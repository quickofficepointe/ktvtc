<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Faq;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class FaqController extends Controller
{
    public function index()
    {
        $faqs = Faq::latest()->get();
        return view('ktvtc.website.faq.index', compact('faqs'));
    }
/**
 * Display FAQs for public website
 */
public function publicIndex()
{
    $faqs = Faq::where('is_active', true)
        ->orderBy('position', 'asc')
        ->orderBy('created_at', 'desc')
        ->get();

    return view('ktvtc.website.faq.faq', compact('faqs'));
}

/**
 * Display single FAQ by slug
 */
public function publicShow($slug)
{
    $faq = Faq::where('slug', $slug)
        ->where('is_active', true)
        ->firstOrFail();

    return view('ktvtc.website.faq.singlefaq', compact('faq'));
}
    public function store(Request $request)
    {
        $request->validate([
            'question' => 'required|string|max:255',
            'answer'   => 'required|string',
        ]);

        Faq::create([
            'question'  => $request->question,
            'slug'      => Str::slug($request->question),
            'answer'    => $request->answer,
            'is_active' => $request->has('is_active'),
            'position'  => $request->position ?? 0,
            'created_by'=> Auth::id(),
            'ip_address'=> $request->ip(),
            'user_agent'=> $request->userAgent(),
        ]);

        return redirect()->back()->with('success', 'FAQ added successfully.');
    }

    public function update(Request $request, $id)
    {
        $faq = Faq::findOrFail($id);

        $request->validate([
            'question' => 'required|string|max:255',
            'answer'   => 'required|string',
        ]);

        $faq->update([
            'question'  => $request->question,
            'slug'      => Str::slug($request->question),
            'answer'    => $request->answer,
            'is_active' => $request->has('is_active'),
            'position'  => $request->position ?? $faq->position,
            'updated_by'=> Auth::id(),
        ]);

        return redirect()->back()->with('success', 'FAQ updated successfully.');
    }

    public function destroy($id)
    {
        Faq::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'FAQ deleted successfully.');
    }
}
