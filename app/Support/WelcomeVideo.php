<?php

namespace App\Support;

use App\Models\User;

/**
 * The how-to video each role watches on its first sign-in (files in public/videos).
 * Roles without a video here are not asked to watch one.
 */
class WelcomeVideo
{
    private const VIDEOS = [
        User::ROLE_STUDENT  => ['file' => 'videos/student-portal.mp4', 'title' => 'Student portal: how to', 'minutes' => 3],
        User::ROLE_TRAINERS => ['file' => 'videos/trainer-portal.mp4', 'title' => 'Trainer portal: how to', 'minutes' => 2],
        11                  => ['file' => 'videos/academics-office.mp4', 'title' => 'Academics office: how to', 'minutes' => 4],
        User::ROLE_FINANCE  => ['file' => 'videos/finance-office.mp4', 'title' => 'Finance office: how to', 'minutes' => 11],
    ];

    public static function for(User $user): ?array
    {
        $video = self::VIDEOS[(int) $user->role] ?? null;

        return $video && is_file(public_path($video['file'])) ? $video : null;
    }

    /** Must this user be shown their video before their dashboard? */
    public static function due(User $user): bool
    {
        return !$user->welcome_video_watched_at
            && !session('welcome_video_skipped')
            && self::for($user) !== null;
    }
}
