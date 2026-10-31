<?php

namespace App\Http\Controllers;

use App\Models\MAttendance;
use App\Models\MAttendanceRecord;
use App\Models\MCourse;
use App\Models\MEnrollment;
use App\Models\MStudent;
use App\Models\MSubject;
use App\Models\MobileSchool;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MAttendanceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $attendances = MAttendance::with(['course', 'subject', 'mobileSchool', 'records'])
            ->orderBy('attendance_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        $courses = MCourse::active()->get();
        $subjects = MSubject::active()->get();
        $mobileSchools = MobileSchool::active()->get();

        return view('ktvtc.mschool.attendances.index', compact(
            'attendances',
            'courses',
            'subjects',
            'mobileSchools'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Handled in index method
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'session_name' => 'nullable|string|max:255',
            'attendance_date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'subject_id' => 'nullable|exists:m_subjects,subject_id',
            'course_id' => 'required|exists:m_courses,course_id',
            'mobile_school_id' => 'nullable|exists:mobile_schools,id',
            'venue' => 'nullable|string|max:255',
            'room' => 'nullable|string|max:100',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'recording_method' => 'required|in:manual,qr_code,biometric,mobile_app',
            'qr_code_data' => 'nullable|string',
            'is_geofenced' => 'boolean',
            'is_active' => 'boolean',
            'is_locked' => 'boolean',
            'allow_late_marking' => 'boolean',
            'late_threshold_minutes' => 'nullable|integer|min:1',
            'total_expected' => 'nullable|integer|min:0',
            'topic_covered' => 'nullable|string',
            'remarks' => 'nullable|string',
        ]);

        // What the session is for: the subject if one was picked, otherwise the course
        $validated['attendable_type'] = !empty($validated['subject_id']) ? 'subject' : 'course';
        $validated['attendable_id'] = $validated['subject_id'] ?? $validated['course_id'];
        $validated['created_by'] = auth()->id();

        try {
            DB::beginTransaction();

            $attendance = MAttendance::create($validated);

            // Generate QR code if recording method is QR code
            if ($attendance->recording_method === 'qr_code') {
                $this->generateQrCode($attendance);
            }

            DB::commit();

            return redirect()->route('mschool.attendances.index')
                ->with('success', 'Attendance session created successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to create attendance session: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(MAttendance $attendance)
    {
        $attendance->load(['course', 'subject', 'mobileSchool', 'records.student']);
        $roster = $this->roster($attendance);
        $records = $attendance->records->keyBy('student_id');

        return view('ktvtc.mschool.attendances.show', compact('attendance', 'roster', 'records'));
    }

    /**
     * Mark the register: one status per student on the session's roster.
     */
    public function mark(Request $request, MAttendance $attendance)
    {
        if ($attendance->is_locked) {
            return back()->with('error', 'This session is locked. Unlock it to change the register.');
        }

        $roster = $this->roster($attendance)->keyBy('student_id');
        $validated = $request->validate([
            'status' => 'required|array',
            'status.*' => 'required|in:present,absent,late,leave,excused',
            'reason' => 'nullable|array',
            'reason.*' => 'nullable|string|max:500',
        ]);

        DB::transaction(function () use ($validated, $roster, $attendance, $request) {
            foreach ($validated['status'] as $studentId => $status) {
                if (!$roster->has((int) $studentId)) {
                    continue; // only students on this session's roster
                }
                $reason = $validated['reason'][$studentId] ?? null;
                MAttendanceRecord::updateOrCreate(
                    ['attendance_id' => $attendance->attendance_id, 'student_id' => (int) $studentId],
                    [
                        'status' => $status,
                        'is_late' => $status === 'late',
                        'check_in_time' => in_array($status, ['present', 'late']) ? now()->format('H:i:s') : null,
                        'absence_reason' => in_array($status, ['absent', 'leave', 'excused']) ? $reason : null,
                        'late_reason' => $status === 'late' ? $reason : null,
                        'verification_method' => 'manual',
                        'recorded_by' => auth()->id(),
                        'ip_address' => $request->ip(),
                    ]
                );
            }
            $attendance->total_expected = $roster->count();
            $attendance->calculateStatistics();
        });

        return redirect()->route('mschool.attendances.show', $attendance->attendance_id)
            ->with('success', 'Register saved: ' . $attendance->fresh()->total_present . ' present, '
                . $attendance->fresh()->total_absent . ' absent, ' . $attendance->fresh()->total_late . ' late.');
    }

    /** Students expected at a session: active enrollments in its course (and mobile school, if set). */
    private function roster(MAttendance $attendance)
    {
        $enrolled = MEnrollment::whereIn('status', ['active', 'pending'])
            ->when($attendance->course_id, fn ($q) => $q->where('course_id', $attendance->course_id))
            ->when($attendance->mobile_school_id, fn ($q) => $q->where('mobile_school_id', $attendance->mobile_school_id))
            ->pluck('student_id');

        if (!$attendance->course_id && !$attendance->mobile_school_id) {
            return collect();
        }

        return MStudent::whereIn('student_id', $enrolled)->orderBy('first_name')->orderBy('last_name')->get();
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(MAttendance $attendance)
    {
        $courses = MCourse::active()->get();
        $subjects = MSubject::active()->get();
        $mobileSchools = MobileSchool::active()->get();

        return view('ktvtc.mschool.attendances.edit', compact(
            'attendance',
            'courses',
            'subjects',
            'mobileSchools'
        ));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, MAttendance $attendance)
    {
        // Prevent updating locked sessions
        if ($attendance->is_locked) {
            return redirect()->back()
                ->with('error', 'Cannot update a locked attendance session.');
        }

        $validated = $request->validate([
            'session_name' => 'nullable|string|max:255',
            'attendance_date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'subject_id' => 'nullable|exists:m_subjects,subject_id',
            'course_id' => 'nullable|exists:m_courses,course_id',
            'mobile_school_id' => 'nullable|exists:mobile_schools,id',
            'venue' => 'nullable|string|max:255',
            'room' => 'nullable|string|max:100',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'recording_method' => 'required|in:manual,qr_code,biometric,mobile_app',
            'is_geofenced' => 'boolean',
            'is_active' => 'boolean',
            'allow_late_marking' => 'boolean',
            'late_threshold_minutes' => 'nullable|integer|min:1',
            'total_expected' => 'nullable|integer|min:0',
            'topic_covered' => 'nullable|string',
            'remarks' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            $attendance->update($validated);

            // Regenerate QR code if recording method changed to QR code
            if ($attendance->recording_method === 'qr_code' && !$attendance->qr_code_data) {
                $this->generateQrCode($attendance);
            }

            DB::commit();

            return redirect()->route('mschool.attendances.index')
                ->with('success', 'Attendance session updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to update attendance session: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(MAttendance $attendance)
    {
        try {
            DB::beginTransaction();

            // Delete related records first
            $attendance->records()->delete();
            $attendance->delete();

            DB::commit();

            return redirect()->route('mschool.attendances.index')
                ->with('success', 'Attendance session deleted successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to delete attendance session: ' . $e->getMessage());
        }
    }

    /**
     * Lock attendance session
     */
    public function lock(MAttendance $attendance)
    {
        try {
            $attendance->update(['is_locked' => true]);

            return redirect()->back()
                ->with('success', 'Attendance session locked successfully.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to lock attendance session: ' . $e->getMessage());
        }
    }

    /**
     * Unlock attendance session
     */
    public function unlock(MAttendance $attendance)
    {
        try {
            $attendance->update(['is_locked' => false]);

            return redirect()->back()
                ->with('success', 'Attendance session unlocked successfully.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to unlock attendance session: ' . $e->getMessage());
        }
    }

    /**
     * Generate QR code for attendance session
     */
    public function generateQrCode(MAttendance $attendance)
    {
        try {
            $qrData = [
                'attendance_id' => $attendance->attendance_id,
                'session_name' => $attendance->session_name,
                'timestamp' => now()->timestamp,
                'expires_at' => now()->addHours(2)->timestamp // QR code expires in 2 hours
            ];

            $encryptedData = encrypt($qrData);
            $attendance->update(['qr_code_data' => $encryptedData]);

            return redirect()->back()
                ->with('success', 'QR code generated successfully.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to generate QR code: ' . $e->getMessage());
        }
    }

    /**
     * Update attendance statistics
     */
    public function updateStatistics(MAttendance $attendance)
    {
        try {
            $records = $attendance->records;

            $attendance->update([
                'total_present' => $records->where('status', 'present')->count(),
                'total_absent' => $records->where('status', 'absent')->count(),
                'total_late' => $records->where('status', 'late')->count(),
                'total_leave' => $records->where('status', 'leave')->count(),
            ]);

            return redirect()->back()
                ->with('success', 'Attendance statistics updated successfully.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to update statistics: ' . $e->getMessage());
        }
    }

    /**
     * Show attendance records for a session
     */
    public function records(MAttendance $attendance)
    {
        // The register lives on the session page
        return redirect()->route('mschool.attendances.show', $attendance->attendance_id);
    }

    /**
     * Generate QR code data
     */

}
