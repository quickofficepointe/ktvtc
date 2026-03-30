<?php

namespace App\Http\Controllers;

use App\Models\MStudent;
use App\Models\MobileSchool;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MStudentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $students = MStudent::with(['mobileSchool', 'enrollments.course'])
            ->orderBy('is_active', 'desc')
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();

        $mobileSchools = MobileSchool::where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('ktvtc.mschool.students.index', compact('students', 'mobileSchools'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'nullable|email|max:150|unique:m_students,email',
            'phone' => 'nullable|string|max:20',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|in:male,female,other',
            'address' => 'nullable|string',
            'mobile_school_id' => 'nullable|exists:mobile_schools,id',
            'student_code' => 'nullable|string|unique:m_students,student_code',
            'enrollment_date' => 'nullable|date',
            'is_active' => 'boolean',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240',
            'guardian_name' => 'nullable|string|max:150',
            'guardian_phone' => 'nullable|string|max:20',
            'guardian_email' => 'nullable|email|max:150',
            'guardian_address' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            // Generate student code if not provided
            if (empty($validated['student_code'])) {
                $validated['student_code'] = $this->generateStudentCode();
            }

            // Handle profile image upload
            if ($request->hasFile('profile_image')) {
                $imagePath = $request->file('profile_image')->store('students/profile-images', 'public');
                $validated['profile_image'] = $imagePath;
            }

            // Add tracking information
            $validated['created_by'] = auth()->id();
            $validated['ip_address'] = $request->ip();
            $validated['user_agent'] = $request->userAgent();

            $student = MStudent::create($validated);

            DB::commit();

            return redirect()->route('students.index')
                ->with('success', 'Student created successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to create student: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $student = MStudent::findOrFail($id);

        $validated = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'nullable|email|max:150|unique:m_students,email,' . $student->student_id . ',student_id',
            'phone' => 'nullable|string|max:20',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|in:male,female,other',
            'address' => 'nullable|string',
            'mobile_school_id' => 'nullable|exists:mobile_schools,id',
            'student_code' => 'nullable|string|unique:m_students,student_code,' . $student->student_id . ',student_id',
            'enrollment_date' => 'nullable|date',
            'is_active' => 'boolean',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240',
            'guardian_name' => 'nullable|string|max:150',
            'guardian_phone' => 'nullable|string|max:20',
            'guardian_email' => 'nullable|email|max:150',
            'guardian_address' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            // Handle profile image upload
            if ($request->hasFile('profile_image')) {
                // Delete old image if exists
                if ($student->profile_image) {
                    Storage::disk('public')->delete($student->profile_image);
                }

                $imagePath = $request->file('profile_image')->store('students/profile-images', 'public');
                $validated['profile_image'] = $imagePath;
            }

            // Update tracking information
            $validated['updated_by'] = auth()->id();
            $validated['ip_address'] = $request->ip();
            $validated['user_agent'] = $request->userAgent();

            $student->update($validated);

            DB::commit();

            return redirect()->route('students.index')
                ->with('success', 'Student updated successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to update student: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $student = MStudent::findOrFail($id);

        try {
            DB::beginTransaction();

            // Check if student has enrollments
            if ($student->enrollments()->exists()) {
                return redirect()->route('students.index')
                    ->with('error', 'Cannot delete student. It has associated enrollments. Please delete the enrollments first.');
            }

            // Delete profile image if exists
            if ($student->profile_image) {
                Storage::disk('public')->delete($student->profile_image);
            }

            $student->delete();

            DB::commit();

            return redirect()->route('students.index')
                ->with('success', 'Student deleted successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('students.index')
                ->with('error', 'Failed to delete student: ' . $e->getMessage());
        }
    }

    /**
     * Generate unique student code
     */
    private function generateStudentCode()
    {
        $prefix = 'STU';
        $year = date('Y');

        do {
            $random = Str::upper(Str::random(6));
            $code = $prefix . $year . $random;
        } while (MStudent::where('student_code', $code)->exists());

        return $code;
    }
}
