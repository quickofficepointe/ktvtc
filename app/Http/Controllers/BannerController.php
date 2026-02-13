<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class BannerController extends Controller
{
    public function index()
    {
        $banners = Banner::all();
        return view('ktvtc.website.banners.index', compact('banners'));
    }

    public function store(Request $request)
    {
        // Debug: Check if files are being received
        Log::info('Files received:', [
            'desktop_image' => $request->hasFile('desktop_image'),
            'mobile_image' => $request->hasFile('mobile_image')
        ]);

        $request->validate([
            'title' => 'nullable|string|max:255',
            'desktop_image' => 'required|image|mimes:jpeg,png,jpg,gif|max:4096',
            'mobile_image' => 'required|image|mimes:jpeg,png,jpg,gif|max:4096',
            'button_text' => 'nullable|string|max:100',
            'button_link' => 'nullable|url|max:255',
            'is_active' => 'nullable|boolean',
            'order' => 'nullable|integer',
        ]);

        try {
            // Store images
            $desktopPath = $request->file('desktop_image')->store('banners', 'public');
            $mobilePath = $request->file('mobile_image')->store('banners', 'public');

            // Create banner
            Banner::create([
                'title' => $request->title,
                'desktop_image' => $desktopPath,
                'mobile_image' => $mobilePath,
                'button_text' => $request->button_text,
                'button_link' => $request->button_link,
                'is_active' => $request->boolean('is_active', true),
                'order' => $request->order ?? 0,
                'created_by' => auth()->id(),
                'ip_address' => $request->ip(),           // Add this
                'user_agent' => $request->userAgent(),    // Add this
            ]);

            return back()->with('success', 'Banner added successfully');

        } catch (\Exception $e) {
            Log::error('Banner creation error: ' . $e->getMessage());
            return back()->with('error', 'Error creating banner: ' . $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        $banner = Banner::findOrFail($id);

        $request->validate([
            'title' => 'nullable|string|max:255',
            'desktop_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:4096',
            'mobile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:4096',
            'button_text' => 'nullable|string|max:100',
            'button_link' => 'nullable|url|max:255',
            'is_active' => 'nullable|boolean',
            'order' => 'nullable|integer',
        ]);

        try {
            $data = [
                'title' => $request->title,
                'button_text' => $request->button_text,
                'button_link' => $request->button_link,
                'is_active' => $request->boolean('is_active', $banner->is_active),
                'order' => $request->order ?? $banner->order,
                'updated_by' => auth()->id(),
                'ip_address' => $request->ip(),           // Add this
                'user_agent' => $request->userAgent(),    // Add this
            ];

            // Update desktop image if provided
            if ($request->hasFile('desktop_image')) {
                // Delete old image
                if ($banner->desktop_image) {
                    Storage::disk('public')->delete($banner->desktop_image);
                }
                $data['desktop_image'] = $request->file('desktop_image')->store('banners', 'public');
            }

            // Update mobile image if provided
            if ($request->hasFile('mobile_image')) {
                // Delete old image
                if ($banner->mobile_image) {
                    Storage::disk('public')->delete($banner->mobile_image);
                }
                $data['mobile_image'] = $request->file('mobile_image')->store('banners', 'public');
            }

            $banner->update($data);

            return back()->with('success', 'Banner updated successfully');

        } catch (\Exception $e) {
            Log::error('Banner update error: ' . $e->getMessage());
            return back()->with('error', 'Error updating banner: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $banner = Banner::findOrFail($id);

            // Delete images from storage
            if ($banner->desktop_image) {
                Storage::disk('public')->delete($banner->desktop_image);
            }
            if ($banner->mobile_image) {
                Storage::disk('public')->delete($banner->mobile_image);
            }

            $banner->delete();

            return back()->with('success', 'Banner deleted successfully');
        } catch (\Exception $e) {
            Log::error('Banner deletion error: ' . $e->getMessage());
            return back()->with('error', 'Error deleting banner: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $banner = Banner::findOrFail($id);
        return response()->json($banner);
    }
}
