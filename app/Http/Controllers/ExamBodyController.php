<?php

namespace App\Http\Controllers;

use App\Models\ExamBody;
use App\Models\ExamType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ExamBodyController extends Controller
{
    /**
     * Display a listing of exam bodies.
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        $query = ExamBody::withCount('examTypes')
            ->when($user->role != 2, function ($q) use ($user) {
                return $q; // Exam bodies are global, no campus restriction
            });

        // Apply filters
        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active === 'yes');
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Statistics
        $totalBodies = (clone $query)->count();
        $activeBodies = (clone $query)->where('is_active', true)->count();

        $examBodies = $query->orderBy('name')->paginate(15);

        return view('ktvtc.admin.exams.index', compact(
            'examBodies',
            'totalBodies',
            'activeBodies'
        ));
    }

    /**
     * Show the form for creating a new exam body.
     */
    public function create()
    {
        return view('ktvtc.admin.exam-bodies.create');
    }

    /**
     * Store a newly created exam body.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:exam_bodies,code',
            'description' => 'nullable|string',
            'website' => 'nullable|url|max:255',
            'contact_person' => 'nullable|string|max:255',
            'contact_email' => 'nullable|email|max:255',
            'contact_phone' => 'nullable|string|max:20',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        DB::beginTransaction();

        try {
            $data = $request->all();
            $data['created_by'] = auth()->id();

            $examBody = ExamBody::create($data);

            DB::commit();

            return redirect()->route('admin.tvet.exam-bodies.index')
                ->with('success', 'Exam body created successfully.');

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->with('error', 'Failed to create exam body: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified exam body.
     */
    public function show(ExamBody $examBody)
    {
        $examBody->load(['examTypes', 'creator', 'updater']);

        // Get statistics
        $totalTypes = $examBody->examTypes()->count();
        $activeTypes = $examBody->examTypes()->where('is_active', true)->count();

        // Get recent exam types
        $recentTypes = $examBody->examTypes()
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Get usage statistics
        $templatesCount = \App\Models\CourseFeeTemplate::whereHas('examType', function($q) use ($examBody) {
            $q->where('exam_body_id', $examBody->id);
        })->count();

        $enrollmentsCount = \App\Models\Enrollment::whereHas('feeTemplate.examType', function($q) use ($examBody) {
            $q->where('exam_body_id', $examBody->id);
        })->count();

        $registrationsCount = \App\Models\ExamRegistration::whereHas('examType', function($q) use ($examBody) {
            $q->where('exam_body_id', $examBody->id);
        })->count();

        return view('ktvtc.admin.exam-bodies.show', compact(
            'examBody',
            'totalTypes',
            'activeTypes',
            'recentTypes',
            'templatesCount',
            'enrollmentsCount',
            'registrationsCount'
        ));
    }

    /**
     * Show the form for editing the specified exam body.
     */
    public function edit(ExamBody $examBody)
    {
        return view('ktvtc.admin.exam-bodies.edit', compact('examBody'));
    }

    /**
     * Update the specified exam body.
     */
    public function update(Request $request, ExamBody $examBody)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:exam_bodies,code,' . $examBody->id,
            'description' => 'nullable|string',
            'website' => 'nullable|url|max:255',
            'contact_person' => 'nullable|string|max:255',
            'contact_email' => 'nullable|email|max:255',
            'contact_phone' => 'nullable|string|max:20',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        DB::beginTransaction();

        try {
            $data = $request->all();
            $data['updated_by'] = auth()->id();

            $examBody->update($data);

            DB::commit();

            return redirect()->route('admin.tvet.exam-bodies.index')
                ->with('success', 'Exam body updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->with('error', 'Failed to update exam body: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified exam body.
     */
    public function destroy(ExamBody $examBody)
    {
        // Check if exam body has exam types
        if ($examBody->examTypes()->count() > 0) {
            return redirect()->back()
                ->with('error', 'Cannot delete exam body that has exam types. Delete the exam types first.');
        }

        // Check if exam body is used in fee templates
        $templatesCount = \App\Models\CourseFeeTemplate::whereHas('examType', function($q) use ($examBody) {
            $q->where('exam_body_id', $examBody->id);
        })->count();

        if ($templatesCount > 0) {
            return redirect()->back()
                ->with('error', 'Cannot delete exam body that is used in fee templates.');
        }

        $examBody->delete();

        return redirect()->route('admin.tvet.exam-bodies.index')
            ->with('success', 'Exam body deleted successfully.');
    }

    /**
     * Toggle exam body status.
     */
    public function toggleStatus(ExamBody $examBody)
    {
        $examBody->update([
            'is_active' => !$examBody->is_active
        ]);

        $status = $examBody->is_active ? 'activated' : 'deactivated';

        return redirect()->back()
            ->with('success', "Exam body {$status} successfully.");
    }

    /**
     * Get exam bodies for select dropdown (API).
     */
    public function getForSelect(Request $request)
    {
        $query = ExamBody::where('is_active', true);

        if ($request->filled('with_counts')) {
            $query->withCount('examTypes');
        }

        $bodies = $query->orderBy('name')
            ->get(['id', 'name', 'code', 'description']);

        return response()->json($bodies);
    }

    /**
     * Get exam types for a specific exam body (API).
     */
    public function getExamTypes(ExamBody $examBody)
    {
        $types = $examBody->examTypes()
            ->where('is_active', true)
            ->orderBy('level')
            ->orderBy('name')
            ->get(['id', 'name', 'code', 'level', 'duration_months']);

        return response()->json([
            'success' => true,
            'exam_body' => [
                'id' => $examBody->id,
                'name' => $examBody->name,
                'code' => $examBody->code,
            ],
            'exam_types' => $types
        ]);
    }

    /**
     * Get exam body statistics (API).
     */
    public function getStatistics()
    {
        $totalBodies = ExamBody::count();
        $activeBodies = ExamBody::where('is_active', true)->count();

        $bodiesWithTypes = ExamBody::has('examTypes')->count();

        $totalTypes = \App\Models\ExamType::count();
        $activeTypes = \App\Models\ExamType::where('is_active', true)->count();

        $mostUsedBody = ExamBody::withCount('examTypes')
            ->orderBy('exam_types_count', 'desc')
            ->first();

        $recentBodies = ExamBody::orderBy('created_at', 'desc')
            ->limit(5)
            ->get(['id', 'name', 'code', 'created_at']);

        return response()->json([
            'success' => true,
            'data' => [
                'totals' => [
                    'total_bodies' => $totalBodies,
                    'active_bodies' => $activeBodies,
                    'bodies_with_types' => $bodiesWithTypes,
                    'total_types' => $totalTypes,
                    'active_types' => $activeTypes,
                ],
                'most_used_body' => $mostUsedBody ? [
                    'id' => $mostUsedBody->id,
                    'name' => $mostUsedBody->name,
                    'code' => $mostUsedBody->code,
                    'types_count' => $mostUsedBody->exam_types_count,
                ] : null,
                'recent_bodies' => $recentBodies,
            ]
        ]);
    }

    /**
     * Export exam bodies.
     */
    public function export(Request $request)
    {
        $request->validate([
            'format' => 'required|in:csv,excel',
        ]);

        $bodies = ExamBody::withCount('examTypes')
            ->orderBy('name')
            ->get();

        $filename = 'exam-bodies-' . date('Y-m-d-H-i-s') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($bodies) {
            $file = fopen('php://output', 'w');

            // Add BOM for UTF-8
            fwrite($file, "\xEF\xBB\xBF");

            // Headers
            fputcsv($file, [
                'Name',
                'Code',
                'Description',
                'Website',
                'Contact Person',
                'Contact Email',
                'Contact Phone',
                'Exam Types Count',
                'Status',
                'Created At',
            ]);

            // Data
            foreach ($bodies as $body) {
                fputcsv($file, [
                    $body->name,
                    $body->code,
                    $body->description,
                    $body->website,
                    $body->contact_person,
                    $body->contact_email,
                    $body->contact_phone,
                    $body->exam_types_count,
                    $body->is_active ? 'Active' : 'Inactive',
                    $body->created_at->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Import exam bodies (for future use).
     */
    public function import(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|mimes:csv,txt|max:2048',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Import logic would go here
        // This is a placeholder for future implementation

        return redirect()->route('admin.tvet.exam-bodies.index')
            ->with('success', 'Exam bodies imported successfully.');
    }
}
