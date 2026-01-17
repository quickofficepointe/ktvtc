<?php

namespace App\Http\Controllers;

use App\Models\Partner;
use Illuminate\Http\Request;

class PartnerController extends Controller
{
    public function index()
    {
        $partners = Partner::all();
        return view('ktvtc.website.partner.index', compact('partners'));
    }
/**
 * Display partners for public website
 */
public function publicIndex()
{
    $partners = Partner::where('is_active', true)
        ->orderBy('name', 'asc')
        ->get();

    return view('ktvtc.website.partner.partner', compact('partners'));
}

/**
 * Display single partner for public website
 */
public function publicShow($id)
{
    $partner = Partner::where('is_active', true)
        ->findOrFail($id);

    return view('ktvtc.website.partner.show', compact('partner'));
}
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'logo_path' => 'nullable|image|max:2048',
            'website' => 'nullable|url|max:255',
            'is_active' => 'nullable|boolean',
        ]);

        $logoPath = $request->file('logo_path') ? $request->file('logo_path')->store('partners', 'public') : null;

        Partner::create([
            'name' => $request->name,
            'logo_path' => $logoPath,
            'website' => $request->website,
            'is_active' => $request->is_active ?? true,
            'created_by' => auth()->id(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return back()->with('success', 'Partner added successfully');
    }

    public function update(Request $request, $id)
    {
        $partner = Partner::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'logo_path' => 'nullable|image|max:2048',
            'website' => 'nullable|url|max:255',
            'is_active' => 'nullable|boolean',
        ]);

        $logoPath = $request->file('logo_path') ? $request->file('logo_path')->store('partners', 'public') : $partner->logo_path;

        $partner->update([
            'name' => $request->name,
            'logo_path' => $logoPath,
            'website' => $request->website,
            'is_active' => $request->is_active ?? $partner->is_active,
            'updated_by' => auth()->id(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return back()->with('success', 'Partner updated successfully');
    }

    public function destroy($id)
    {
        $partner = Partner::findOrFail($id);
        $partner->delete();

        return back()->with('success', 'Partner deleted successfully');
    }
}
