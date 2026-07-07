<?php
// app/Http/Controllers/Finance/FeeStructureController.php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class FeeStructureController extends Controller
{
    /**
     * Display fee structure for all courses
     */
    public function index(Request $request)
    {
        $query = Course::with(['department', 'feeModifiedBy', 'feeModificationApprovedBy']);

        // Filter by approval status
        if ($request->filled('filter')) {
            switch ($request->filter) {
                case 'pending':
                    $query->whereNotNull('fee_modified_by')
                        ->whereNull('fee_modification_approved_by');
                    break;
                case 'approved':
                    $query->whereNotNull('fee_modification_approved_by');
                    break;
                case 'unmodified':
                    $query->whereNull('fee_modified_by');
                    break;
                case 'recent':
                    $query->whereNotNull('fee_modified_at')
                        ->where('fee_modified_at', '>=', now()->subDays(30));
                    break;
            }
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhereHas('department', function($dq) use ($search) {
                      $dq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $courses = $query->orderBy('name')->paginate(20)->withQueryString();

        $stats = [
            'total' => Course::count(),
            'pending_approval' => Course::whereNotNull('fee_modified_by')
                ->whereNull('fee_modification_approved_by')->count(),
            'approved' => Course::whereNotNull('fee_modification_approved_by')->count(),
            'unmodified' => Course::whereNull('fee_modified_by')->count(),
            'recent_changes' => Course::whereNotNull('fee_modified_at')
                ->where('fee_modified_at', '>=', now()->subDays(30))->count(),
        ];

        return view('ktvtc.finance.fee-structure.index', compact('courses', 'stats'));
    }

    /**
     * Show fee structure for a specific course
     */
    public function show(Course $course)
    {
        $feeStructure = $course->getFeeStructureWithVersion();
        $versionHistory = $course->getFeeVersionHistory();
        $formattedBreakdown = $course->formatted_fee_breakdown;
        $totalFee = $course->total_fee;

        // Check if there are active enrollments that might be affected
        $activeEnrollments = $course->enrollments()
            ->where('status', 'active')
            ->count();

        $totalEnrollments = $course->enrollments()->count();

        return view('ktvtc.finance.fee-structure.show', compact(
            'course',
            'feeStructure',
            'versionHistory',
            'formattedBreakdown',
            'totalFee',
            'activeEnrollments',
            'totalEnrollments'
        ));
    }

    /**
     * Show form to edit fee structure
     */
    public function edit(Course $course)
    {
        // Check if user has permission
        if (!Course::canUserModifyFees(auth()->user())) {
            abort(403, 'You do not have permission to modify fee structures.');
        }

        $currentFees = $course->fees_breakdown ?? [];
        $versionHistory = $course->getFeeVersionHistory();
        $totalFee = $course->total_fee;

        // Get existing fee items as array for the form
        $feeItems = [];
        if (!empty($currentFees)) {
            foreach ($currentFees as $key => $value) {
                if (is_array($value)) {
                    $feeItems[] = [
                        'name' => $key,
                        'amount' => $value['amount'] ?? 0,
                        'description' => $value['description'] ?? null,
                    ];
                } else {
                    $feeItems[] = [
                        'name' => $key,
                        'amount' => (float) $value,
                        'description' => null,
                    ];
                }
            }
        }

        return view('ktvtc.finance.fee-structure.edit', compact(
            'course',
            'currentFees',
            'versionHistory',
            'totalFee',
            'feeItems'
        ));
    }

    /**
     * Update fee structure
     */
    public function update(Request $request, Course $course)
    {
        if (!Course::canUserModifyFees(auth()->user())) {
            abort(403, 'You do not have permission to modify fee structures.');
        }

        $validator = Validator::make($request->all(), [
            'fees' => 'required|array|min:1',
            'fees.*.name' => 'required|string|max:255',
            'fees.*.amount' => 'required|numeric|min:0',
            'fees.*.description' => 'nullable|string|max:500',
            'modification_reason' => 'required|string|min:5|max:500',
        ], [
            'fees.required' => 'Please add at least one fee item.',
            'fees.*.name.required' => 'Each fee item must have a name.',
            'fees.*.amount.required' => 'Each fee item must have an amount.',
            'fees.*.amount.min' => 'Fee amount cannot be negative.',
            'modification_reason.required' => 'Please provide a reason for the modification.',
            'modification_reason.min' => 'Reason must be at least 5 characters.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        DB::beginTransaction();

        try {
            $user = auth()->user();

            // Format fee breakdown
            $feeBreakdown = [];
            foreach ($request->fees as $item) {
                $feeBreakdown[$item['name']] = [
                    'amount' => (float) $item['amount'],
                    'description' => $item['description'] ?? null,
                ];
            }

            // Update fee structure
            $course->updateFeeStructure(
                $feeBreakdown,
                $request->modification_reason,
                $user
            );

            DB::commit();

            $message = $course->isFeeStructureApproved()
                ? 'Fee structure updated and approved successfully.'
                : 'Fee structure updated successfully. Waiting for approval.';

            Log::info('Fee structure updated by Finance', [
                'course_id' => $course->id,
                'course_name' => $course->name,
                'updated_by' => $user->id,
                'version' => $course->fee_version,
                'auto_approved' => $course->isFeeStructureApproved(),
            ]);

            return redirect()->route('finance.fee-structure.show', $course)
                ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Fee structure update failed', [
                'course_id' => $course->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->back()
                ->with('error', 'Failed to update fee structure: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Approve fee structure changes (Finance only)
     */
    public function approve(Request $request, Course $course)
    {
        $user = auth()->user();

        // Only Finance or Super Admin can approve
        if (!in_array($user->role, [0, 7])) {
            abort(403, 'Only Finance or Super Admin can approve fee structure changes.');
        }

        if (!$course->hasPendingFeeChanges()) {
            return redirect()->back()
                ->with('warning', 'No pending fee changes to approve.');
        }

        $request->validate([
            'approval_note' => 'nullable|string|max:500',
        ]);

        DB::beginTransaction();

        try {
            $course->approveFeeStructure($user);

            DB::commit();

            Log::info('Fee structure approved', [
                'course_id' => $course->id,
                'course_name' => $course->name,
                'approved_by' => $user->id,
                'note' => $request->approval_note,
                'version' => $course->fee_version,
            ]);

            return redirect()->route('finance.fee-structure.show', $course)
                ->with('success', 'Fee structure approved successfully.');

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Fee structure approval failed', [
                'course_id' => $course->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()
                ->with('error', 'Failed to approve fee structure: ' . $e->getMessage());
        }
    }

    /**
     * Reject fee structure changes
     */
    public function reject(Request $request, Course $course)
    {
        $user = auth()->user();

        if (!in_array($user->role, [0, 7])) {
            abort(403, 'Only Finance or Super Admin can reject fee structure changes.');
        }

        $request->validate([
            'rejection_reason' => 'required|string|min:5|max:500',
        ]);

        DB::beginTransaction();

        try {
            $course->rejectFeeStructure($user, $request->rejection_reason);

            DB::commit();

            Log::warning('Fee structure rejected', [
                'course_id' => $course->id,
                'course_name' => $course->name,
                'rejected_by' => $user->id,
                'reason' => $request->rejection_reason,
            ]);

            return redirect()->route('finance.fee-structure.show', $course)
                ->with('warning', 'Fee structure changes rejected. Previous structure restored.');

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Fee structure rejection failed', [
                'course_id' => $course->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()
                ->with('error', 'Failed to reject fee structure: ' . $e->getMessage());
        }
    }

    /**
     * Rollback to previous fee structure version
     */
    public function rollback(Request $request, Course $course)
    {
        $user = auth()->user();

        if (!in_array($user->role, [0, 7])) {
            abort(403, 'Only Finance or Super Admin can rollback fee structures.');
        }

        $request->validate([
            'reason' => 'required|string|min:5|max:500',
        ]);

        DB::beginTransaction();

        try {
            $course->rollbackFeeStructure($user, $request->reason);

            DB::commit();

            Log::warning('Fee structure rolled back', [
                'course_id' => $course->id,
                'course_name' => $course->name,
                'rolled_back_by' => $user->id,
                'reason' => $request->reason,
                'version' => $course->fee_version,
            ]);

            return redirect()->route('finance.fee-structure.show', $course)
                ->with('success', 'Fee structure rolled back successfully.');

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Fee structure rollback failed', [
                'course_id' => $course->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()
                ->with('error', 'Failed to rollback fee structure: ' . $e->getMessage());
        }
    }

    /**
     * Get fee structure history
     */
    public function history(Course $course)
    {
        $history = [
            'current' => [
                'version' => $course->fee_version ?? 'v1.0',
                'fees' => $course->fees_breakdown,
                'modified_by' => $course->feeModifiedBy?->name,
                'modified_at' => $course->fee_modified_at?->toDateTimeString(),
                'approved_by' => $course->feeModificationApprovedBy?->name,
                'approved_at' => $course->fee_modification_approved_at?->toDateTimeString(),
                'reason' => $course->fee_modification_reason,
                'total' => $course->total_fee,
            ],
            'previous' => $course->previous_fee_structure ? [
                'fees' => $course->previous_fee_structure,
                'total' => $this->calculateTotalFromBreakdown($course->previous_fee_structure),
            ] : null,
        ];

        return view('ktvtc.finance.fee-structure.history', compact('course', 'history'));
    }

    /**
     * Calculate total from fee breakdown
     */
    private function calculateTotalFromBreakdown($breakdown): float
    {
        if (empty($breakdown)) {
            return 0;
        }

        $total = 0;
        foreach ($breakdown as $key => $value) {
            if (is_array($value)) {
                $total += (float) ($value['amount'] ?? 0);
            } else {
                $total += (float) $value;
            }
        }
        return $total;
    }

    /**
     * Get fee structure statistics (API)
     */
    public function stats()
    {
        $stats = [
            'total_courses' => Course::count(),
            'active_courses' => Course::active()->count(),
            'pending_approval' => Course::whereNotNull('fee_modified_by')
                ->whereNull('fee_modification_approved_by')->count(),
            'approved_changes' => Course::whereNotNull('fee_modification_approved_by')->count(),
            'total_fee_modifications' => Course::whereNotNull('fee_modified_by')->count(),
            'recent_changes' => Course::whereNotNull('fee_modified_at')
                ->where('fee_modified_at', '>=', now()->subDays(30))
                ->count(),
            'avg_fee' => Course::avg(DB::raw('JSON_EXTRACT(fees_breakdown, "$.*.amount")')) ?? 0,
        ];

        return response()->json($stats);
    }

    /**
     * Export fee structure report
     */
    public function export(Request $request)
    {
        $courses = Course::with(['department', 'feeModifiedBy', 'feeModificationApprovedBy'])
            ->orderBy('name')
            ->get();

        $filename = 'fee_structure_report_' . now()->format('Y-m-d_His') . '.csv';
        $handle = fopen('php://temp', 'w');

        // Headers
        fputcsv($handle, [
            'Course Name',
            'Course Code',
            'Department',
            'Total Fees',
            'Fee Version',
            'Status',
            'Last Modified By',
            'Last Modified At',
            'Approved By',
            'Approved At',
            'Fee Items',
        ]);

        // Data
        foreach ($courses as $course) {
            $feeItems = [];
            if ($course->fees_breakdown) {
                foreach ($course->fees_breakdown as $key => $value) {
                    $amount = is_array($value) ? ($value['amount'] ?? 0) : $value;
                    $feeItems[] = $key . ': ' . number_format($amount, 2);
                }
            }

            $status = 'Current';
            if ($course->hasPendingFeeChanges()) {
                $status = 'Pending Approval';
            } elseif ($course->isFeeStructureApproved()) {
                $status = 'Approved';
            }

            fputcsv($handle, [
                $course->name,
                $course->code ?? 'N/A',
                $course->department->name ?? 'N/A',
                number_format($course->total_fee, 2),
                $course->fee_version ?? 'v1.0',
                $status,
                $course->feeModifiedBy?->name ?? 'N/A',
                $course->fee_modified_at?->format('Y-m-d H:i:s') ?? 'N/A',
                $course->feeModificationApprovedBy?->name ?? 'N/A',
                $course->fee_modification_approved_at?->format('Y-m-d H:i:s') ?? 'N/A',
                implode('; ', $feeItems),
            ]);
        }

        rewind($handle);
        $content = stream_get_contents($handle);
        fclose($handle);

        return response($content)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }
}
