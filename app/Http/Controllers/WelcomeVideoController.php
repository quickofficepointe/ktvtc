<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Support\WelcomeVideo;
use Illuminate\Support\Facades\Auth;

/**
 * First sign-in: the user watches the how-to video for their role. "Continue" unlocks when it
 * ends; "Skip for now" shows it again at the next sign-in until it has been watched once.
 * The page stays available afterwards to watch again.
 */
class WelcomeVideoController extends Controller
{
    public function show()
    {
        $user = Auth::user();
        $video = WelcomeVideo::for($user);
        if (!$video) {
            return redirect()->route('home');
        }

        return view('welcome-video', [
            'video'    => $video,
            'watched'  => (bool) $user->welcome_video_watched_at,
            'isStudent' => (int) $user->role === User::ROLE_STUDENT,
        ]);
    }

    public function watched()
    {
        $user = Auth::user();
        if (!$user->welcome_video_watched_at) {
            $user->forceFill(['welcome_video_watched_at' => now()])->save();
        }

        if ((int) $user->role === User::ROLE_STUDENT && \Illuminate\Support\Facades\Route::has('student.quiz.index')) {
            return redirect()->route('student.quiz.index')->with('success', 'Thanks for watching! Now check what you remember with this short quiz.');
        }

        return redirect()->route('home')->with('success', 'Thanks for watching. You can watch it again any time from your menu.');
    }

    public function skip()
    {
        session(['welcome_video_skipped' => true]);

        return redirect()->route('home');
    }
}
