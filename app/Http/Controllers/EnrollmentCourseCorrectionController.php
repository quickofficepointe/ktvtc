<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Enrollment;
use App\Services\Students\CourseCorrection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/** Admin: put an enrollment that was placed on the wrong course by mistake on the right one. */
class EnrollmentCourseCorrectionController extends Controller
{
    public function store(Request $request, Enrollment $enrollment, CourseCorrection $correction)
    {
        $data = $request->validate([
            'course_id' => 'required|exists:courses,id',
            'reason' => 'required|string|min:10|max:300',
            'total_fees' => 'nullable|numeric|min:0|max:2000000',
        ], ['reason.min' => 'Write a short reason (at least 10 characters), e.g. "Imported on the wrong course".']);

        try {
            $result = $correction->correct($enrollment, Course::findOrFail($data['course_id']), trim($data['reason']),
                isset($data['total_fees']) && $data['total_fees'] !== '' ? (float) $data['total_fees'] : null, Auth::user());
        } catch (\DomainException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('admin.enrollments.show', $enrollment)->with('success', 'Course corrected. ' . $result['message']);
    }
}
