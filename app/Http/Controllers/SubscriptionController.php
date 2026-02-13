<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Subscription;
use Illuminate\Support\Facades\Auth;

class SubscriptionController extends Controller
{
    public function index()
    {
        $subscriptions = Subscription::latest()->get();
        return view('ktvtc.admin.subscribers.index', compact('subscriptions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'nullable|string|max:255',
            'email' => 'required|email|unique:subscriptions,email',
            'status' => 'nullable|in:active,unsubscribed',
        ]);

        Subscription::create([
            'name' => $request->name,
            'email' => $request->email,
            'status' => $request->status ?? 'active',
            'created_by' => Auth::id(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->back()->with('success', 'Subscriber added successfully.');
    }

    public function update(Request $request, $id)
    {
        $subscription = Subscription::findOrFail($id);

        $request->validate([
            'name' => 'nullable|string|max:255',
            'email' => 'required|email|unique:subscriptions,email,' . $id,
            'status' => 'nullable|in:active,unsubscribed',
        ]);

        $subscription->update([
            'name' => $request->name,
            'email' => $request->email,
            'status' => $request->status ?? 'active',
            'updated_by' => Auth::id(),
        ]);

        return redirect()->back()->with('success', 'Subscriber updated successfully.');
    }

    public function destroy($id)
    {
        Subscription::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Subscriber deleted successfully.');
    }
}
