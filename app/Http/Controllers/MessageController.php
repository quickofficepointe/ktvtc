<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Message;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{
    // Display messages
    public function index()
    {
        $messages = Message::latest()->get();
        $pendingCount = Message::where('status', 'pending')->count();
        $viewedCount = Message::where('status', 'viewed')->count();
        $repliedCount = Message::where('status', 'replied')->count();
        $resolvedCount = Message::where('status', 'resolved')->count();
        $archivedCount = Message::where('status', 'archived')->count();

        return view('ktvtc.admin.contact.index', compact('messages', 'pendingCount', 'viewedCount', 'repliedCount', 'resolvedCount', 'archivedCount'));
    }

    // Store new message from website
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email'=> 'required|email|max:255',
            'phone'=> 'nullable|string|max:20',
            'message' => 'required|string',
        ]);

        Message::create([
            'name' => $request->name,
            'email'=> $request->email,
            'phone'=> $request->phone,
            'message'=> $request->message,
            'status' => 'pending',
        ]);

        return redirect()->back()->with('success', 'Message sent successfully.');
    }

    // Show single message (automatically marks as viewed if pending)
    public function show($id)
    {
        $message = Message::findOrFail($id);

        // If message is pending, mark as viewed when admin opens it
        if ($message->status === 'pending') {
            $message->update([
                'status' => 'viewed',
                'first_seen_by' => Auth::id(),
            ]);
        }

        return view('ktvtc.admin.contact.show', compact('message'));
    }

    // Update message status
    public function update(Request $request, $id)
    {
        $message = Message::findOrFail($id);

        $request->validate([
            'status' => 'required|in:pending,viewed,replied,resolved,archived',
        ]);

        $updateData = ['status' => $request->status];

        // If marking as viewed and not previously viewed, record who viewed it
        if ($request->status === 'viewed' && !$message->first_seen_by) {
            $updateData['first_seen_by'] = Auth::id();
        }

        // If marking as replied or resolved and not previously viewed
        if (in_array($request->status, ['replied', 'resolved']) && !$message->first_seen_by) {
            $updateData['first_seen_by'] = Auth::id();
        }

        $message->update($updateData);

        $statusMessages = [
            'pending' => 'Message marked as pending.',
            'viewed' => 'Message marked as viewed.',
            'replied' => 'Message marked as replied.',
            'resolved' => 'Message marked as resolved.',
            'archived' => 'Message archived.',
        ];

        return redirect()->back()->with('success', $statusMessages[$request->status]);
    }

    // Quick action: Mark as viewed
    public function markAsViewed($id)
    {
        $message = Message::findOrFail($id);

        $message->update([
            'status' => 'viewed',
            'first_seen_by' => $message->first_seen_by ?? Auth::id(),
        ]);

        return redirect()->back()->with('success', 'Message marked as viewed.');
    }

    // Quick action: Mark as replied
    public function markAsReplied($id)
    {
        $message = Message::findOrFail($id);

        $message->update([
            'status' => 'replied',
            'first_seen_by' => $message->first_seen_by ?? Auth::id(),
        ]);

        return redirect()->back()->with('success', 'Message marked as replied.');
    }

    // Quick action: Mark as resolved
    public function markAsResolved($id)
    {
        $message = Message::findOrFail($id);

        $message->update([
            'status' => 'resolved',
            'first_seen_by' => $message->first_seen_by ?? Auth::id(),
        ]);

        return redirect()->back()->with('success', 'Message marked as resolved.');
    }

    // Quick action: Archive
    public function archive($id)
    {
        $message = Message::findOrFail($id);
        $message->update(['status' => 'archived']);

        return redirect()->back()->with('success', 'Message archived.');
    }

    // Delete a message
    public function destroy($id)
    {
        $message = Message::findOrFail($id);
        $message->delete();

        return redirect()->back()->with('success', 'Message deleted successfully.');
    }
}
