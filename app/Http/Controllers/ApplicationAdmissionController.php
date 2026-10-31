<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\Campus;
use App\Models\Course;
use App\Models\Student;
use App\Services\Students\DuplicateStudentFinder;
use App\Services\Students\EnrollmentCreator;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Admin: turn an accepted online application into a student + enrollment, so the
 * website and admin stay in step and nobody retypes (or duplicates) the applicant.
 */
class ApplicationAdmissionController extends Controller
{
    public function form(Application $application, DuplicateStudentFinder $finder)
    {
        if ($application->student_id && $application->enrollment_id) {
            return redirect()->route('admin.enrollments.show', $application->enrollment_id)
                ->with('info', 'This application was already admitted.');
        }

        $matches = $finder->matchesFor($application->only(['id_number', 'first_name', 'last_name', 'phone', 'email']));
        $courses = Course::orderBy('name')->get(['id', 'name', 'code']);
        $campuses = Campus::orderBy('name')->get(['id', 'name']);
        $intake = $this->intakeDate($application->intake_period);

        return view('ktvtc.admin.applications.admit', compact('application', 'matches', 'courses', 'campuses', 'intake'));
    }

    public function admit(Request $request, Application $application, EnrollmentCreator $creator)
    {
        if ($application->student_id && $application->enrollment_id) {
            return redirect()->route('admin.enrollments.show', $application->enrollment_id)->with('info', 'This application was already admitted.');
        }
        if ($application->status !== 'accepted') {
            return back()->with('error', 'Mark the application as Accepted before admitting.');
        }

        $data = $request->validate([
            'student_choice'  => 'required|string', // "new" or an existing student id
            'course_id'       => 'required|exists:courses,id',
            'campus_id'       => 'nullable|exists:campuses,id',
            'enrollment_date' => 'required|date',
            'total_fees'      => 'required|numeric|min:0',
            'study_mode'      => 'required|in:full_time,part_time,evening,weekend,online',
            'sponsorship_type'=> 'required|in:self,sponsored,government,scholarship,company',
        ]);

        try {
            $enrollment = DB::transaction(function () use ($data, $application, $creator) {
                $student = $data['student_choice'] === 'new'
                    ? $this->createStudent($application, $data['campus_id'] ?? null)
                    : Student::whereNull('merged_into_id')->findOrFail((int) $data['student_choice']);

                $date = Carbon::parse($data['enrollment_date']);
                $enrollment = $creator->create($student, Course::findOrFail($data['course_id']), [
                    'campus_id'        => $data['campus_id'] ?? null,
                    'total_fees'       => $data['total_fees'],
                    'intake_year'      => $date->year,
                    'intake_month'     => $date->format('F'),
                    'enrollment_date'  => $date->toDateString(),
                    'start_date'       => $date->toDateString(),
                    'study_mode'       => $data['study_mode'],
                    'sponsorship_type' => $data['sponsorship_type'],
                    'student_type'     => $application->application_type === 'continuing' ? 'continuing' : 'new',
                    'status'           => 'active',
                    'remarks'          => "Admitted from online application {$application->application_number}",
                ], Auth::id());

                $application->forceFill([
                    'student_id'    => $student->id,
                    'enrollment_id' => $enrollment->id,
                    'admitted_at'   => now(),
                    'admitted_by'   => Auth::id(),
                ])->save();

                return $enrollment;
            });
        } catch (\DomainException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        $next = $enrollment->total_fees > 0 ? '' : ' Fees were left at 0, so register Term 1 on the Terms page to bill it.';
        return redirect()->route('admin.enrollments.show', $enrollment)
            ->with('success', "Admitted {$enrollment->student_name} as {$enrollment->student_number}.{$next}");
    }

    private function createStudent(Application $a, ?int $campusId): Student
    {
        return Student::create([
            'first_name'        => $a->first_name,
            'last_name'         => $a->last_name,
            'student_number'    => Student::getNextStudentNumber(),
            'email'             => $a->email,
            'phone'             => $a->phone,
            'gender'            => $a->gender,
            'date_of_birth'     => $a->date_of_birth,
            'campus_id'         => $campusId ?? $a->campus_id,
            'status'            => 'active',
            'registration_date' => now(),
            'student_category'  => 'regular',
            'application_id'    => $a->id,
            'id_type'           => $a->id_type,
            'id_number'         => $a->id_number,
            'address'           => $a->address,
            'city'              => $a->city,
            'county'            => $a->county,
            'postal_code'       => $a->postal_code,
            'country'           => $a->country ?? 'Kenya',
            'education_level'   => $a->education_level,
            'school_name'       => $a->school_name,
            'graduation_year'   => $a->graduation_year,
            'mean_grade'        => $a->mean_grade,
            'passport_photo_path' => $a->passport_photo,
            'id_document_path'  => $a->id_document,
        ]);
    }

    /** "March 2028" -> 1 March 2028; falls back to today. */
    private function intakeDate(?string $period): Carbon
    {
        try {
            return $period ? Carbon::createFromFormat('!F Y', trim($period)) : today();
        } catch (\Throwable) {
            return today();
        }
    }
}
