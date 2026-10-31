<?php

namespace App\Services\Students;

use App\Models\Member;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Merges duplicate student records into the one staff chose to keep.
 * Everything that belongs to the duplicate moves to the kept record; the
 * duplicate is archived (soft-deleted, merged_into_id set), never erased.
 */
class StudentMergeService
{
    /** Tables whose student_id points at students.id. */
    private const TABLES = [
        'enrollments', 'fee_payments', 'term_registrations', 'unit_results', 'trainer_evaluations',
        'kcb_transactions', 'exam_registrations', 'attachments', 'attachment_applications',
        'invoices', 'payment_plans', 'applications',
    ];

    /**
     * @return array<string, int> rows moved per table
     */
    public function merge(Student $keep, Student $duplicate, int $userId): array
    {
        if ($keep->id === $duplicate->id) {
            throw new \DomainException('Choose two different records.');
        }
        if ($duplicate->merged_into_id) {
            throw new \DomainException("{$duplicate->student_number} was already merged.");
        }

        return DB::transaction(function () use ($keep, $duplicate, $userId) {
            $moved = [];
            foreach (self::TABLES as $table) {
                if (\Illuminate\Support\Facades\Schema::hasColumn($table, 'student_id')) {
                    $moved[$table] = DB::table($table)->where('student_id', $duplicate->id)->update(['student_id' => $keep->id]);
                }
            }

            // Logins: users.student_id is unique. Move the duplicate's login only if the kept record has none.
            $dupUser = User::where('student_id', $duplicate->id)->first();
            if ($dupUser) {
                if (User::where('student_id', $keep->id)->exists()) {
                    $dupUser->forceFill(['student_id' => null, 'is_active' => false])->save();
                    $moved['login'] = 0;
                } else {
                    $dupUser->forceFill(['student_id' => $keep->id])->save();
                    $moved['login'] = 1;
                }
            }

            // Library: members.student_id is unique. Fold the duplicate's membership (loans,
            // waiting list, fine payments and unpaid fines) into the kept one, or move it over.
            $dupMember = Member::where('student_id', $duplicate->id)->lockForUpdate()->first();
            if ($dupMember) {
                $keepMember = Member::where('student_id', $keep->id)->lockForUpdate()->first();
                if ($keepMember) {
                    foreach (['transactions', 'reservations', 'payments'] as $table) {
                        DB::table($table)->where('member_id', $dupMember->id)->update(['member_id' => $keepMember->id]);
                    }
                    $keepMember->increment('outstanding_fines', (float) $dupMember->outstanding_fines);
                    $dupMember->delete();
                } else {
                    $dupMember->update(['student_id' => $keep->id]);
                }
                $moved['library_member'] = 1;
            }

            // Fill gaps on the kept record from the duplicate
            foreach (['id_number', 'email', 'phone', 'date_of_birth', 'next_of_kin_name', 'next_of_kin_phone', 'emergency_contact_phone', 'passport_photo_path', 'id_document_path'] as $field) {
                if (blank($keep->{$field}) && filled($duplicate->{$field})) {
                    $keep->{$field} = $duplicate->{$field};
                }
            }
            // So payments quoting the duplicate's number are suggested for the kept student
            if (blank($keep->legacy_student_code) && $duplicate->student_number !== $keep->student_number) {
                $keep->legacy_student_code = $duplicate->student_number;
            }
            $keep->save();

            $duplicate->forceFill([
                'merged_into_id' => $keep->id,
                'import_notes'   => trim(($duplicate->import_notes ? $duplicate->import_notes . "\n" : '') . 'Merged into #' . $keep->id . ' (' . $keep->student_number . ') by user ' . $userId . ' on ' . now()->toDateTimeString()),
            ])->save();
            $duplicate->delete();

            Log::info('Students merged', ['kept' => $keep->id, 'merged' => $duplicate->id, 'moved' => $moved, 'by' => $userId]);

            return $moved;
        });
    }
}
