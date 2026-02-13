<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\Member;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Notification::with(['member', 'createdBy'])
            ->orderBy('created_at', 'desc');

        // Apply filters
        if ($request->has('status') && $request->status === 'unread') {
            $query->where('is_read', false);
        }

        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        // Calculate statistics
        $unreadCount = Notification::where('is_read', false)->count();
        $todayCount = Notification::whereDate('created_at', today())->count();
        $overdueCount = Notification::where('type', 'overdue')->count();

        $notifications = $query->paginate(20);
        $members = Member::active()->get();

        return view('ktvtc.library.notifications.index', compact(
            'notifications',
            'members',
            'unreadCount',
            'todayCount',
            'overdueCount'
        ));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|string|in:general,overdue,reservation,membership,fine,event',
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'member_id' => 'required',
            'data' => 'nullable|json',
            'send_immediately' => 'boolean'
        ]);

        // If member_id is "all", send to all members
        if ($validated['member_id'] === 'all') {
            $members = Member::active()->get();
            foreach ($members as $member) {
                Notification::create([
                    'member_id' => $member->id,
                    'type' => $validated['type'],
                    'title' => $validated['title'],
                    'message' => $validated['message'],
                    'data' => $validated['data'],
                    'sent_at' => $validated['send_immediately'] ? now() : null,
                    'created_by' => Auth::id()
                ]);
            }
            $message = "Notification sent to all members.";
        } else {
            Notification::create([
                'member_id' => $validated['member_id'],
                'type' => $validated['type'],
                'title' => $validated['title'],
                'message' => $validated['message'],
                'data' => $validated['data'],
                'sent_at' => $validated['send_immediately'] ? now() : null,
                'created_by' => Auth::id()
            ]);
            $message = "Notification created successfully.";
        }

        return redirect()->route('notifications.index')
            ->with('success', $message);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Notification $notification)
    {
        $validated = $request->validate([
            'type' => 'required|string|in:general,overdue,reservation,membership,fine,event',
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'is_read' => 'boolean'
        ]);

        $notification->update($validated);

        return redirect()->route('notifications.index')
            ->with('success', 'Notification updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Notification $notification)
    {
        $notification->delete();

        return redirect()->route('notifications.index')
            ->with('success', 'Notification deleted successfully.');
    }

    /**
     * Mark notification as read
     */
    public function markRead(Notification $notification)
    {
        $notification->update([
            'is_read' => true,
            'read_at' => now()
        ]);

        return redirect()->back()->with('success', 'Notification marked as read.');
    }

    /**
     * Mark all notifications as read
     */
    public function markAllRead()
    {
        Notification::where('is_read', false)->update([
            'is_read' => true,
            'read_at' => now()
        ]);

        return redirect()->back()->with('success', 'All notifications marked as read.');
    }
}
