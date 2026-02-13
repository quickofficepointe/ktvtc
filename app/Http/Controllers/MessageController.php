<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Message;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{
    // Display messages in datatable
    public function index()
{
    $messages = Message::latest()->get();
    $unreadCount = Message::where('status', false)->count();

    return view('ktvtc.admin.contact.index', compact('messages', 'unreadCount'));
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
            'ip_address'=> $request->ip(),
            'user_agent'=> $request->userAgent(),
        ]);

        return redirect()->back()->with('success', 'Message sent successfully.');
    }

    // Mark as seen or update action
    public function update(Request $request, $id)
    {
        $message = Message::findOrFail($id);

        $message->update([
            'is_seen' => $request->has('is_seen') ? $request->is_seen : $message->is_seen,
            'seen_by' => $request->has('is_seen') && $request->is_seen ? Auth::id() : $message->seen_by,
            'action'  => $request->action ?? $message->action,
        ]);

        return redirect()->back()->with('success', 'Message updated successfully.');
    }

    // Delete a message
    public function destroy($id)
    {
        $message = Message::findOrFail($id);
        $message->delete();

        return redirect()->back()->with('success', 'Message deleted successfully.');
    }
}
