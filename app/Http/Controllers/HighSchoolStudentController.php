<?php

namespace App\Http\Controllers;

use App\Models\HighSchoolStudent;
use App\Models\CardAccount;
use App\Models\CardFundingRequest;
use App\Services\CardService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\HighSchoolStudentsImport;
use App\Exports\HighSchoolStudentsExport;

class HighSchoolStudentController extends Controller
{
    protected $cardService;

    public function __construct(CardService $cardService)
    {
        $this->cardService = $cardService;
    }

    /**
     * Dashboard
     */
    public function dashboard()
    {
        $totalStudents = HighSchoolStudent::count();
        $activeStudents = HighSchoolStudent::where('status', 'active')->count();
        $totalCards = CardAccount::count();
        $activeCards = CardAccount::where('is_active', true)->count();
        $totalBalance = CardAccount::sum('balance');
        $studentsWithoutCards = HighSchoolStudent::doesntHave('cardAccount')->count();

        // Recent funding requests
        $recentFunding = CardFundingRequest::with('cardAccount.student')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return view('ktvtc.finance.high-school.dashboard', compact(
            'totalStudents',
            'activeStudents',
            'totalCards',
            'activeCards',
            'totalBalance',
            'studentsWithoutCards',
            'recentFunding'
        ));
    }

    /**
     * Display list of high school students
     */
    public function index(Request $request)
    {
        $query = HighSchoolStudent::query();

        // Filters
        if ($request->filled('class')) {
            $query->where('class', $request->class);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('admission_number', 'like', "%{$search}%")
                  ->orWhere('parent_phone', 'like', "%{$search}%");
            });
        }

        $students = $query->with('cardAccount')->orderBy('full_name')->paginate(20);

        // Get stats
        $totalStudents = HighSchoolStudent::count();
        $activeStudents = HighSchoolStudent::where('status', 'active')->count();
        $studentsWithCards = HighSchoolStudent::has('cardAccount')->count();
        $studentsWithoutCards = $totalStudents - $studentsWithCards;

        // Get distinct classes for filter
        $classes = HighSchoolStudent::select('class')->distinct()->pluck('class');

        return view('ktvtc.finance.high-school.students.index', compact(
            'students',
            'totalStudents',
            'activeStudents',
            'studentsWithCards',
            'studentsWithoutCards',
            'classes'
        ));
    }

    /**
     * Show create student form
     */
    public function create()
    {
        return view('ktvtc.finance.high-school.students.create');
    }

    /**
     * Store a new student
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'admission_number' => 'required|unique:high_school_students',
            'full_name' => 'required|string|max:255',
            'class' => 'required|string|max:20',
            'parent_phone' => 'nullable|string|max:20',
            'parent_name' => 'nullable|string|max:255',
            'profile_picture' => 'nullable|image|max:2048',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        DB::beginTransaction();

        try {
            // Create student
            $student = HighSchoolStudent::create([
                'admission_number' => $request->admission_number,
                'full_name' => $request->full_name,
                'class' => $request->class,
                'parent_phone' => $request->parent_phone,
                'parent_name' => $request->parent_name,
                'status' => 'active'
            ]);

            // Handle profile picture
            if ($request->hasFile('profile_picture')) {
                $path = $request->file('profile_picture')->store('high-school/students', 'public');
                $student->profile_picture = $path;
                $student->save();
            }

            // Auto-create card
            $card = $this->cardService->createCardForStudent($student);

            DB::commit();

            return redirect()->route('finance.hs-students.show', $student)
                ->with('success', 'Student created and card issued successfully! Card number: ' . $card->card_number);

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to create student: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Show student details
     */
    public function show(HighSchoolStudent $student)
    {
        $student->load(['cardAccount', 'contacts']);
        $transactions = $student->transactions()->orderBy('created_at', 'desc')->limit(20)->get();

        return view('ktvtc.finance.high-school.students.show', compact('student', 'transactions'));
    }

    /**
     * Show edit form
     */
    public function edit(HighSchoolStudent $student)
    {
        return view('ktvtc.finance.high-school.students.edit', compact('student'));
    }

    /**
     * Update student
     */
    public function update(Request $request, HighSchoolStudent $student)
    {
        $validator = Validator::make($request->all(), [
            'full_name' => 'required|string|max:255',
            'class' => 'required|string|max:20',
            'parent_phone' => 'nullable|string|max:20',
            'parent_name' => 'nullable|string|max:255',
            'status' => 'required|in:active,inactive,graduated',
            'profile_picture' => 'nullable|image|max:2048',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            $student->update([
                'full_name' => $request->full_name,
                'class' => $request->class,
                'parent_phone' => $request->parent_phone,
                'parent_name' => $request->parent_name,
                'status' => $request->status,
            ]);

            if ($request->hasFile('profile_picture')) {
                $path = $request->file('profile_picture')->store('high-school/students', 'public');
                $student->profile_picture = $path;
                $student->save();
            }

            // Update card snapshot
            if ($student->cardAccount) {
                $student->cardAccount->update([
                    'student_name' => $student->full_name,
                    'student_class' => $student->class,
                    'student_admission_number' => $student->admission_number,
                    'student_photo' => $student->profile_picture,
                ]);
            }

            return redirect()->route('finance.hs-students.show', $student)
                ->with('success', 'Student updated successfully');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to update student: ' . $e->getMessage());
        }
    }

    /**
     * Delete student
     */
    public function destroy(HighSchoolStudent $student)
    {
        try {
            // Delete card first if exists
            if ($student->cardAccount) {
                $student->cardAccount->delete();
            }

            $student->delete();

            return redirect()->route('finance.hs-students.index')
                ->with('success', 'Student deleted successfully');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to delete student: ' . $e->getMessage());
        }
    }

    /**
     * Show import view
     */
    public function importView()
    {
        return view('ktvtc.finance.high-school.students.import');
    }

    /**
     * Process import
     */
    public function importProcess(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv'
        ]);

        try {
            $import = new HighSchoolStudentsImport();
            Excel::import($import, $request->file('file'));

            return redirect()->route('finance.hs-students.index')
                ->with('success', $import->getRowCount() . ' students imported successfully');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    /**
     * Export students
     */
    public function export(Request $request)
    {
        return Excel::download(new HighSchoolStudentsExport, 'high-school-students.xlsx');
    }

    /**
     * Download template
     */
    public function downloadTemplate()
    {
        $headers = [
            'admission_number',
            'full_name',
            'class',
            'parent_phone',
            'parent_name'
        ];

        $callback = function() use ($headers) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $headers);
            fclose($handle);
        };

        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="student_import_template.csv"'
        ]);
    }

    /**
     * Bulk activate students
     */
    public function bulkActivate(Request $request)
    {
        $ids = $request->student_ids;
        if (empty($ids)) {
            return redirect()->back()->with('error', 'No students selected');
        }

        HighSchoolStudent::whereIn('id', $ids)->update(['status' => 'active']);

        return redirect()->back()
            ->with('success', count($ids) . ' students activated');
    }

    /**
     * Bulk deactivate students
     */
    public function bulkDeactivate(Request $request)
    {
        $ids = $request->student_ids;
        if (empty($ids)) {
            return redirect()->back()->with('error', 'No students selected');
        }

        HighSchoolStudent::whereIn('id', $ids)->update(['status' => 'inactive']);

        return redirect()->back()
            ->with('success', count($ids) . ' students deactivated');
    }
}
