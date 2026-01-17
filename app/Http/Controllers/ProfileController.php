<?php

namespace App\Http\Controllers;

use App\Models\Profile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Profile $profile)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit()
    {
        $user = Auth::user();
        return view('ktvtc.students.profile.edit', compact('user'));
    }

public function update(Request $request)
{
    $user = Auth::user();

    $validated = $request->validate([
        'phone_number' => 'nullable|string|max:20',
        'bio' => 'nullable|string|max:500',
        'profile_picture' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:3072', // 3MB limit
    ]);

    if ($request->hasFile('profile_picture')) {
        $path = $request->file('profile_picture')->store('profile_pictures', 'public');
        $validated['profile_picture'] = $path;
    }

    $user->update($validated);

    return redirect()
        ->route('student.dashboard') // 👈 redirect to student dashboard
        ->with('success', 'Profile updated successfully. Awaiting admin approval.');
}


    /**
     * Update the specified resource in storage.
     */

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Profile $profile)
    {
        //
    }
}
