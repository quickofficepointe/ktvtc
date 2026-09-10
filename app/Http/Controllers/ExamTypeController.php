<?php

namespace App\Http\Controllers;

use App\Models\ExamType;
use App\Models\ExamBody;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class ExamTypeController extends Controller
{
    /**
     * Display a listing of exam types.
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        $query = ExamType::with(['examBody', 'creator'])
            ->withCount(['examRegistrations', 'feeTemplates']);

        // Apply filters
        if ($request->filled('exam_body_id')) {
            $query->where('exam_body_id', $request->exam_body_id);
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active === 'yes');
        }

        if ($request->filled('level')) {
            $query->where('level', $request->level);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('examBody', function($sq) use ($search) {
                      $sq->where('name', 'like', "%{$search}%")
                         ->orWhere('code', 'like', "%{$search}%");
                  });
            });
        }

        // Statistics
        $totalTypes = (clone $query)->count();
        $activeTypes = (clone $query)->where('is_active', true)->count();

        $levelBreakdown = [
            '1' => (clone $query)->where('level', 1)->count(),
            '2' => (clone $query)->where('level', 2)->count(),
            '3' => (clone $query)->where('level', 3)->count(),
            '4' => (clone $query)->where('level', 4)->count(),
            '5' => (clone $query)->where('level', 5)->count(),
            '6' => (clone $query)->where('level', 6)->count(),
        ];

        $examTypes = $query->orderBy('exam_body_id')
            ->orderBy('level')
            ->orderBy('name')
            ->paginate(15);

        // Get exam bodies for filter
        $examBodies = ExamBody::orderBy('name')->get();

        $levels = [1, 2, 3, 4, 5, 6];

        return view('ktvtc.admin.exams.examtype', compact(
            'examTypes',
            'examBodies',
            'levels',
            'totalTypes',
            'activeTypes',
            'levelBreakdown'
        ));
    }

    /**
     * Show the form for creating a new exam type.
     */
    public function create()
    {
        $examBodies = ExamBody::where('is_active', true)
            ->orderBy('name')
            ->get();

        $levels = [1, 2, 3, 4, 5, 6];

        return view('ktvtc.admin.exam-types.create', compact('examBodies', 'levels'));
    }

    /**
     * Store a newly created exam type.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'exam_body_id' => 'required|exists:exam_bodies,id',
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:exam_types,code',
            'level' => 'nullable|integer|min:1|max:6',
            'duration_months' => 'nullable|integer|min:1|max:60',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        DB::beginTransaction();

        try {
            // Check for duplicate within same exam body
            $exists = ExamType::where('exam_body_id', $request->exam_body_id)
                ->where('name', $request->name)
                ->exists();

            if ($exists) {
                return redirect()->back()
                    ->with('error', 'An exam type with this name already exists for this exam body.')
                    ->withInput();
            }

            $data = $request->all();
            $data['created_by'] = auth()->id();

            $examType = ExamType::create($data);

            DB::commit();

            return redirect()->route('admin.tvet.exam-types.index')
                ->with('success', 'Exam type created successfully.');

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->with('error', 'Failed to create exam type: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified exam type.
     */
    public function show(ExamType $examType)
    {
        $examType->load(['examBody', 'creator', 'updater']);

        // Get statistics
        $feeTemplatesCount = $examType->feeTemplates()->count();
        $activeTemplatesCount = $examType->feeTemplates()->where('is_active', true)->count();

        $examRegistrationsCount = $examType->examRegistrations()->count();
        $pendingRegistrations = $examType->examRegistrations()->where('status', 'pending')->count();
        $certifiedCount = $examType->examRegistrations()->where('status', 'certified')->count();

        // Get recent registrations
        $recentRegistrations = $examType->examRegistrations()
            ->with(['enrollment.student'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Get recent fee templates
        $recentTemplates = $examType->feeTemplates()
            ->with(['course'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return view('ktvtc.admin.exam-types.show', compact(
            'examType',
            'feeTemplatesCount',
            'activeTemplatesCount',
            'examRegistrationsCount',
            'pendingRegistrations',
            'certifiedCount',
            'recentRegistrations',
            'recentTemplates'
        ));
    }

    /**
     * Show the form for editing the specified exam type.
     */
    public function edit(ExamType $examType)
    {
        $examBodies = ExamBody::where('is_active', true)
            ->orderBy('name')
            ->get();

        $levels = [1, 2, 3, 4, 5, 6];

        return view('ktvtc.admin.exam-types.edit', compact('examType', 'examBodies', 'levels'));
    }

    /**
     * Update the specified exam type.
     */
    public function update(Request $request, ExamType $examType)
    {
        $validator = Validator::make($request->all(), [
            'exam_body_id' => 'required|exists:exam_bodies,id',
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:exam_types,code,' . $examType->id,
            'level' => 'nullable|integer|min:1|max:6',
            'duration_months' => 'nullable|integer|min:1|max:60',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        DB::beginTransaction();

        try {
            // Check for duplicate within same exam body
            $exists = ExamType::where('exam_body_id', $request->exam_body_id)
                ->where('name', $request->name)
                ->where('id', '!=', $examType->id)
                ->exists();

            if ($exists) {
                return redirect()->back()
                    ->with('error', 'An exam type with this name already exists for this exam body.')
                    ->withInput();
            }

            $data = $request->all();
            $data['updated_by'] = auth()->id();

            $examType->update($data);

            DB::commit();

            return redirect()->route('admin.tvet.exam-types.index')
                ->with('success', 'Exam type updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->with('error', 'Failed to update exam type: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified exam type.
     */
    public function destroy(ExamType $examType)
    {
        // Check if exam type has fee templates
        if ($examType->feeTemplates()->count() > 0) {
            return redirect()->back()
                ->with('error', 'Cannot delete exam type that has fee templates.');
        }

        // Check if exam type has exam registrations
        if ($examType->examRegistrations()->count() > 0) {
            return redirect()->back()
                ->with('error', 'Cannot delete exam type that has exam registrations.');
        }

        $examType->delete();

        return redirect()->route('admin.tvet.exam-types.index')
            ->with('success', 'Exam type deleted successfully.');
    }

    /**
     * Toggle exam type status.
     */
    public function toggleStatus(ExamType $examType)
    {
        $examType->update([
            'is_active' => !$examType->is_active
        ]);

        $status = $examType->is_active ? 'activated' : 'deactivated';

        return redirect()->back()
            ->with('success', "Exam type {$status} successfully.");
    }

    /**
     * Get exam types by exam body (API).
     */
    public function getByExamBody(Request $request)
    {
        $request->validate([
            'exam_body_id' => 'required|exists:exam_bodies,id',
            'include_inactive' => 'nullable|boolean',
        ]);

        $query = ExamType::where('exam_body_id', $request->exam_body_id);

        if (!$request->boolean('include_inactive')) {
            $query->where('is_active', true);
        }

        $types = $query->orderBy('level')
            ->orderBy('name')
            ->get(['id', 'name', 'code', 'level', 'duration_months']);

        return response()->json([
            'success' => true,
            'exam_types' => $types
        ]);
    }

    /**
     * Get exam types for select dropdown (API).
     */
    public function getForSelect(Request $request)
    {
        $query = ExamType::with('examBody')
            ->where('is_active', true);

        if ($request->filled('exam_body_id')) {
            $query->where('exam_body_id', $request->exam_body_id);
        }

        $types = $query->orderBy('exam_body_id')
            ->orderBy('level')
            ->orderBy('name')
            ->get()
            ->map(function($type) {
                return [
                    'id' => $type->id,
                    'name' => $type->full_name,
                    'code' => $type->code,
                    'exam_body' => $type->examBody->name,
                    'level' => $type->level,
                    'duration' => $type->duration_months,
                ];
            });

        return response()->json($types);
    }

    /**
     * Get exam type statistics (API).
     */
    public function getStatistics()
    {
        $totalTypes = ExamType::count();
        $activeTypes = ExamType::where('is_active', true)->count();

        $typesByBody = ExamBody::withCount('examTypes')
            ->orderBy('exam_types_count', 'desc')
            ->limit(5)
            ->get()
            ->map(function($body) {
                return [
                    'exam_body' => $body->name,
                    'count' => $body->exam_types_count,
                ];
            });

        $levelDistribution = ExamType::select('level', DB::raw('COUNT(*) as count'))
            ->whereNotNull('level')
            ->groupBy('level')
            ->orderBy('level')
            ->get();

        $recentTypes = ExamType::with('examBody')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function($type) {
                return [
                    'id' => $type->id,
                    'name' => $type->name,
                    'code' => $type->code,
                    'exam_body' => $type->examBody->name,
                    'created_at' => $type->created_at->format('Y-m-d'),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => [
                'totals' => [
                    'total_types' => $totalTypes,
                    'active_types' => $activeTypes,
                    'inactive_types' => $totalTypes - $activeTypes,
                ],
                'types_by_body' => $typesByBody,
                'level_distribution' => $levelDistribution,
                'recent_types' => $recentTypes,
            ]
        ]);
    }

    /**
     * Export exam types.
     */
    public function export(Request $request)
    {
        $request->validate([
            'format' => 'required|in:csv,excel',
        ]);

        $types = ExamType::with('examBody')
            ->orderBy('exam_body_id')
            ->orderBy('level')
            ->orderBy('name')
            ->get();

        $filename = 'exam-types-' . date('Y-m-d-H-i-s') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($types) {
            $file = fopen('php://output', 'w');

            // Add BOM for UTF-8
            fwrite($file, "\xEF\xBB\xBF");

            // Headers
            fputcsv($file, [
                'Exam Body',
                'Name',
                'Code',
                'Level',
                'Duration (Months)',
                'Description',
                'Status',
                'Created At',
            ]);

            // Data
            foreach ($types as $type) {
                fputcsv($file, [
                    $type->examBody->name ?? 'N/A',
                    $type->name,
                    $type->code,
                    $type->level ?? 'N/A',
                    $type->duration_months ?? 'N/A',
                    $type->description,
                    $type->is_active ? 'Active' : 'Inactive',
                    $type->created_at->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Bulk update exam types.
     */
    public function bulkUpdate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'exam_type_ids' => 'required|array',
            'exam_type_ids.*' => 'exists:exam_types,id',
            'action' => 'required|in:activate,deactivate,set_level',
            'level' => 'required_if:action,set_level|nullable|integer|min:1|max:6',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            DB::beginTransaction();

            $count = 0;
            $types = ExamType::whereIn('id', $request->exam_type_ids)->get();

            foreach ($types as $type) {
                switch ($request->action) {
                    case 'activate':
                        $type->update(['is_active' => true]);
                        break;
                    case 'deactivate':
                        $type->update(['is_active' => false]);
                        break;
                    case 'set_level':
                        $type->update(['level' => $request->level]);
                        break;
                }
                $count++;
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "{$count} exam types updated successfully.",
                'count' => $count,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to update exam types: ' . $e->getMessage(),
            ], 500);
        }
    }
}
