<?php
// app/Policies/AttachmentPolicy.php

namespace App\Policies;

use App\Models\Attachment;
use App\Models\User;

class AttachmentPolicy
{
    /**
     * Super Admin and ILO can do anything.
     * Everyone else falls through to specific gates.
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return null;
    }

    // ============================================================
    // STUDENT GATES
    // ============================================================

    public function viewAsStudent(User $user, Attachment $attachment): bool
    {
        return $user->isStudent()
            && $user->student_id
            && (int) $attachment->student_id === (int) $user->student_id;
    }

    public function updateAsStudent(User $user, Attachment $attachment): bool
    {
        return $this->viewAsStudent($user, $attachment)
            && $attachment->canBeEditedByStudent();
    }

    public function deleteAsStudent(User $user, Attachment $attachment): bool
    {
        return $this->viewAsStudent($user, $attachment)
            && $attachment->status === 'draft';
    }

    // ============================================================
    // ILO GATES
    // ============================================================

    public function viewAsIlo(User $user, Attachment $attachment): bool
    {
        return $user->isIlo();
    }

    public function approveAsIlo(User $user, Attachment $attachment): bool
    {
        return $user->isIlo() && $attachment->canBeActivated();
    }

    public function requestRevisionAsIlo(User $user, Attachment $attachment): bool
    {
        return $user->isIlo()
            && in_array($attachment->status, ['submitted', 'trainer_approved'], true);
    }

    public function rejectAsIlo(User $user, Attachment $attachment): bool
    {
        return $user->isIlo()
            && in_array($attachment->status, ['submitted', 'trainer_approved'], true);
    }

    public function completeAsIlo(User $user, Attachment $attachment): bool
    {
        return $user->isIlo() && $attachment->canBeCompleted();
    }

    public function gradeAsIlo(User $user, Attachment $attachment): bool
    {
        return $user->isIlo() && $attachment->canBeGraded();
    }

    public function logVisitAsIlo(User $user, Attachment $attachment): bool
    {
        return $user->isIlo() && $attachment->status === 'active';
    }

    public function promoteCompanyAsIlo(User $user, Attachment $attachment): bool
    {
        return $user->isIlo() && is_null($attachment->company_id);
    }

    // ============================================================
    // TRAINER GATES
    // ============================================================

    /**
     * Trainer can view an attachment only if the student is enrolled
     * in one of the trainer's active courses.
     */
    public function viewAsTrainer(User $user, Attachment $attachment): bool
    {
        if (!$user->isTrainer()) {
            return false;
        }

        $courseIds = $user->trainerCourseIds();

        if (empty($courseIds)) {
            return false;
        }

        return $attachment->student
            ->enrollments()
            ->whereIn('course_id', $courseIds)
            ->exists();
    }

    /**
     * Same scope check as view, but also requires the attachment to
     * be in a state the trainer can act on ('submitted').
     */
    public function approveAsTrainer(User $user, Attachment $attachment): bool
    {
        return $this->viewAsTrainer($user, $attachment)
            && $attachment->canBeApprovedByTrainer();
    }

    public function requestRevisionAsTrainer(User $user, Attachment $attachment): bool
    {
        return $this->viewAsTrainer($user, $attachment)
            && $attachment->status === 'submitted';
    }

    public function markEligibilityAsTrainer(User $user, Attachment $attachment): bool
    {
        return $this->viewAsTrainer($user, $attachment);
    }

    public function logVisitAsTrainer(User $user, Attachment $attachment): bool
    {
        return $this->viewAsTrainer($user, $attachment)
            && $attachment->status === 'active';
    }
}