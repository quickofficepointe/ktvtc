<?php

namespace App\Http\Controllers;

use App\Models\FeePayment;
use App\Models\Student;
use App\Models\User;
use App\Services\Students\DuplicateStudentFinder;
use App\Services\Students\StudentMergeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Admin: student records that look like the same person, merged only when
 * staff confirm which record to keep.
 */
class StudentDuplicatesController extends Controller
{
    public function index(DuplicateStudentFinder $finder)
    {
        $groups = $finder->groups()->map(function ($group) {
            $group['students'] = $group['students']->map(function (Student $s) {
                $s->payments_total = (float) FeePayment::where('student_id', $s->id)->where('status', 'completed')->sum('amount');
                $s->has_login = User::where('student_id', $s->id)->exists();
                return $s;
            });
            return $group;
        });

        return view('ktvtc.admin.students.duplicates', compact('groups'));
    }

    public function merge(Request $request, StudentMergeService $merger)
    {
        $data = $request->validate([
            'keep_id'     => 'required|integer|exists:students,id',
            'merge_ids'   => 'required|array|min:1',
            'merge_ids.*' => 'integer|exists:students,id',
        ]);

        $keep = Student::findOrFail($data['keep_id']);
        $done = [];
        try {
            foreach (array_diff($data['merge_ids'], [$keep->id]) as $id) {
                $dup = Student::findOrFail($id);
                $merger->merge($keep, $dup, Auth::id());
                $done[] = $dup->student_number;
            }
        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', sprintf('Merged %s into %s (%s). The other record%s archived.',
            implode(', ', $done), $keep->full_name, $keep->student_number, count($done) > 1 ? 's were' : ' was'));
    }
}
