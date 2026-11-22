<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

/**
 * "My profile" for every signed-in user: phone, bio, optional photo and password.
 * Shown inside the user's own portal so staff keep their own menu.
 */
class ProfileController extends Controller
{
    private const LAYOUTS = [
        User::ROLE_STUDENT  => 'ktvtc.students.layout.studentlayout',
        User::ROLE_TRAINERS => 'ktvtc.trainers.layout.trainerlayout',
        11                  => 'ktvtc.academics.layout',
    ];

    public function edit()
    {
        $user = Auth::user();
        $layout = self::LAYOUTS[(int) $user->role] ?? 'ktvtc.students.layout.studentlayout';

        return view('ktvtc.students.profile.edit', compact('user', 'layout'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'phone_number'     => 'nullable|string|max:20',
            'bio'              => 'nullable|string|max:500',
            'profile_picture'  => 'nullable|image|mimes:jpg,jpeg,png,webp|max:3072', // 3MB limit
            'current_password' => 'nullable|required_with:password|current_password',
            'password'         => 'nullable|string|min:8|confirmed',
        ], [
            'current_password.current_password' => 'Your current password is not correct.',
            'current_password.required_with'    => 'Type your current password to set a new one.',
        ]);

        $changes = collect($validated)->only(['phone_number', 'bio'])->all();
        if ($request->hasFile('profile_picture')) {
            $changes['profile_picture'] = $request->file('profile_picture')->store('profile_pictures', 'public');
        }
        if (!empty($validated['password'])) {
            $user->forceFill(['password' => Hash::make($validated['password'])]);
        }
        $user->fill($changes)->save();

        $message = !empty($validated['password']) ? 'Profile saved and password changed.' : 'Profile saved.';
        if (!$user->is_approved) {
            return redirect()->route('profile.edit')->with('warning', $message . ' Your account is waiting for admin approval.');
        }

        return redirect()->route('home')->with('success', $message);
    }
}
