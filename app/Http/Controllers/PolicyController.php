<?php

namespace App\Http\Controllers;

use App\Models\Policy;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PolicyController extends Controller
{
    public function index()
    {
        $policies = Policy::all();
        return view('ktvtc.website.policy.index', compact('policies'));
    }
/**
 * Display policies for public website
 */
public function publicIndex()
{
    $policies = Policy::where('is_active', true)
        ->orderBy('title', 'asc')
        ->get();

    return view('ktvtc.website.policy.policy', compact('policies'));
}

/**
 * Display single policy by slug
 */
public function publicShow($slug)
{
    $policy = Policy::where('slug', $slug)
        ->where('is_active', true)
        ->firstOrFail();

    return view('ktvtc.website.policy.show', compact('policy'));
}
    public function store(Request $request)
    {
        $request->validate([
            'title'     => 'required|string|max:255',
            'content'   => 'required|string',
            'is_active' => 'nullable|boolean',
        ]);

        Policy::create([
            'title'      => $request->title,
            'slug'       => Str::slug($request->title), // ✅ Auto slug
            'content'    => $request->content,
            'is_active'  => $request->is_active ?? true,
            'created_by' => auth()->id(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return back()->with('success', 'Policy added successfully');
    }

    public function update(Request $request, $id)
    {
        $policy = Policy::findOrFail($id);

        $request->validate([
            'title'     => 'required|string|max:255',
            'content'   => 'required|string',
            'is_active' => 'nullable|boolean',
        ]);

        $policy->update([
            'title'      => $request->title,
            'slug'       => Str::slug($request->title), // ✅ Regenerates on update
            'content'    => $request->content,
            'is_active'  => $request->is_active ?? $policy->is_active,
            'updated_by' => auth()->id(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return back()->with('success', 'Policy updated successfully');
    }

    public function destroy($id)
    {
        $policy = Policy::findOrFail($id);
        $policy->delete();

        return back()->with('success', 'Policy deleted successfully');
    }
}

