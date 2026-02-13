<?php

namespace App\Http\Controllers;

use App\Models\Download;
use Illuminate\Http\Request;

class DownloadController extends Controller
{
     public function index()
    {
        $downloads = Download::all();
        return view('ktvtc.website.download.index', compact('downloads'));
    }
/**
 * Display downloads for public website
 */
public function downloads()
{
    $downloads = Download::where('is_active', true)
        ->orderBy('created_at', 'desc')
        ->get();

    return view('ktvtc.website.download.downloads', compact('downloads'));
}
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'file_path' => 'required|file|max:10240',
            'is_active' => 'nullable|boolean',
        ]);

        $filePath = $request->file('file_path')->store('downloads', 'public');

        Download::create([
            'title' => $request->title,
            'description' => $request->description,
            'file_path' => $filePath,
            'is_active' => $request->is_active ?? true,
            'created_by' => auth()->id(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return back()->with('success', 'Download added successfully');
    }

    public function update(Request $request, $id)
    {
        $download = Download::findOrFail($id);

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'file_path' => 'nullable|file|max:10240',
            'is_active' => 'nullable|boolean',
        ]);

        $filePath = $request->file('file_path') ? $request->file('file_path')->store('downloads', 'public') : $download->file_path;

        $download->update([
            'title' => $request->title,
            'description' => $request->description,
            'file_path' => $filePath,
            'is_active' => $request->is_active ?? $download->is_active,
         
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return back()->with('success', 'Download updated successfully');
    }

    public function destroy($id)
    {
        $download = Download::findOrFail($id);
        $download->delete();

        return back()->with('success', 'Download deleted successfully');
    }
}
