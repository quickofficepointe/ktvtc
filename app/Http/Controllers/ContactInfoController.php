<?php

namespace App\Http\Controllers;

use App\Models\ContactInfo;
use Illuminate\Http\Request;

class ContactInfoController extends Controller
{
     public function index()
    {
        $contacts = ContactInfo::all();
        return view('ktvtc.website.contactinfo.index', compact('contacts'));
    }
 public function contactus()
    {

        return view('ktvtc.website.contactinfo.contactus');
    }
    public function store(Request $request)
    {
        $request->validate([
            'address' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:100',
            'map_link' => 'nullable|string|max:255'
        ]);

        ContactInfo::create([
            'address' => $request->address,
            'phone' => $request->phone,
            'email' => $request->email,
            'map_link' => $request->map_link,
            'created_by' => auth()->id(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return back()->with('success', 'Contact info added successfully');
    }

    public function update(Request $request, $id)
    {
        $contact = ContactInfo::findOrFail($id);
        $request->validate([
            'address' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:100',
            'map_link' => 'nullable|string|max:255'
        ]);

        $contact->update([
            'address' => $request->address,
            'phone' => $request->phone,
            'email' => $request->email,
            'map_link' => $request->map_link,
            'updated_by' => auth()->id(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return back()->with('success', 'Contact info updated successfully');
    }

    public function destroy($id)
    {
        $contact = ContactInfo::findOrFail($id);
        $contact->delete();

        return back()->with('success', 'Contact info deleted successfully');
    }
}
