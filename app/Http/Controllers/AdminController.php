<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class AdminController extends Controller
{
    public function dashboard()
    {
        $totalUsers = User::count();
        $users = User::paginate(10); // Add pagination
        return view('ktvtc.admin.dashboard', compact('totalUsers', 'users'));
    }

    public function users()
    {
        // Use paginate() instead of get() to get Paginator instance
        $users = User::latest()->paginate(25); // 25 users per page

        $totalUsers = User::count();
        $activeUsers = User::where('is_approved', true)->count();
        $pendingUsers = User::where('is_approved', false)->count();
        $adminUsers = User::where('role', 2)->count();

        return view('ktvtc.admin.users.index', compact(
            'users', 'totalUsers', 'activeUsers', 'pendingUsers', 'adminUsers'
        ));
    }

    public function approve(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $user->role = $request->role;
        $user->is_approved = true;
        $user->save();

        // Send approval email using raw
        Mail::raw(
            "Hello {$user->name},\n\nYour account has been approved successfully.
            You can now access your dashboard based on your assigned role.\n\nThank you,\nKTVTC Team",
            function ($message) use ($user) {
                $message->to($user->email)
                        ->subject('Account Approval - KTVTC');
            }
        );

        return redirect()->route('admin.dashboard')
            ->with('success', 'User approved successfully and email sent.');
    }
}
