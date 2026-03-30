<?php

namespace App\Http\Controllers;

use App\Models\MobileSchool;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class MobileSchoolController extends Controller
{
    public function index()
    {
        $mschools = MobileSchool::latest()->get();
        return view('ktvtc.website.mschool.index', compact('mschools'));
    }

    public function publicIndex()
    {
        $mschools = MobileSchool::where('is_active', true)
            ->orderBy('name', 'asc')
            ->get();

        return view('ktvtc.website.mschool.mschool', compact('mschools'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'address' => 'nullable|string|max:500',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'google_map_link' => 'nullable|url',
            'coordinator_name' => 'nullable|string|max:255',
            'coordinator_email' => 'nullable|email|max:255',
            'coordinator_phone' => 'nullable|string|max:20',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'gallery_images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'is_active' => 'nullable|boolean',
        ]);

        // Handle cover image upload
        $coverImagePath = null;
        $thumbnailPath = null;

        if ($request->hasFile('cover_image')) {
            $coverImagePath = $this->uploadImage($request->file('cover_image'), 'cover');
            $thumbnailPath = $this->createThumbnail($coverImagePath);
        }

        // Handle gallery images upload
        $galleryImages = [];
        if ($request->hasFile('gallery_images')) {
            foreach ($request->file('gallery_images') as $image) {
                $galleryImages[] = $this->uploadImage($image, 'gallery');
            }
        }

        MobileSchool::create([
            'name' => $request->name,
            'description' => $request->description,
            'address' => $request->address,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'google_map_link' => $request->google_map_link,
            'coordinator_name' => $request->coordinator_name,
            'coordinator_email' => $request->coordinator_email,
            'coordinator_phone' => $request->coordinator_phone,
            'cover_image' => $coverImagePath,
            'thumbnail' => $thumbnailPath,
            'gallery_images' => !empty($galleryImages) ? $galleryImages : null,
            'is_active' => $request->is_active ?? true,
            'created_by' => Auth::id(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return back()->with('success', 'Mobile School added successfully');
    }

    public function update(Request $request, $id)
    {
        $mschool = MobileSchool::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'address' => 'nullable|string|max:500',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'google_map_link' => 'nullable|url',
            'coordinator_name' => 'nullable|string|max:255',
            'coordinator_email' => 'nullable|email|max:255',
            'coordinator_phone' => 'nullable|string|max:20',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'remove_cover_image' => 'nullable|boolean',
            'gallery_images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'remove_gallery_images' => 'nullable|array',
            'is_active' => 'nullable|boolean',
        ]);

        // Handle cover image update
        $coverImagePath = $mschool->cover_image;
        $thumbnailPath = $mschool->thumbnail;

        if ($request->has('remove_cover_image') && $request->remove_cover_image) {
            // Remove existing images
            if ($coverImagePath && !filter_var($coverImagePath, FILTER_VALIDATE_URL)) {
                Storage::delete($coverImagePath);
            }
            if ($thumbnailPath && !filter_var($thumbnailPath, FILTER_VALIDATE_URL)) {
                Storage::delete($thumbnailPath);
            }
            $coverImagePath = null;
            $thumbnailPath = null;
        } elseif ($request->hasFile('cover_image')) {
            // Remove old images
            if ($coverImagePath && !filter_var($coverImagePath, FILTER_VALIDATE_URL)) {
                Storage::delete($coverImagePath);
            }
            if ($thumbnailPath && !filter_var($thumbnailPath, FILTER_VALIDATE_URL)) {
                Storage::delete($thumbnailPath);
            }

            // Upload new images
            $coverImagePath = $this->uploadImage($request->file('cover_image'), 'cover');
            $thumbnailPath = $this->createThumbnail($coverImagePath);
        }

        // Handle gallery images update
        $galleryImages = $mschool->gallery_images ?? [];

        // Remove selected gallery images
        if ($request->has('remove_gallery_images')) {
            foreach ($request->remove_gallery_images as $index) {
                if (isset($galleryImages[$index])) {
                    $imagePath = $galleryImages[$index];
                    if (!filter_var($imagePath, FILTER_VALIDATE_URL)) {
                        Storage::delete($imagePath);
                    }
                    unset($galleryImages[$index]);
                }
            }
            $galleryImages = array_values($galleryImages); // Reindex array
        }

        // Add new gallery images
        if ($request->hasFile('gallery_images')) {
            foreach ($request->file('gallery_images') as $image) {
                $galleryImages[] = $this->uploadImage($image, 'gallery');
            }
        }

        $mschool->update([
            'name' => $request->name,
            'description' => $request->description,
            'address' => $request->address,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'google_map_link' => $request->google_map_link,
            'coordinator_name' => $request->coordinator_name,
            'coordinator_email' => $request->coordinator_email,
            'coordinator_phone' => $request->coordinator_phone,
            'cover_image' => $coverImagePath,
            'thumbnail' => $thumbnailPath,
            'gallery_images' => !empty($galleryImages) ? $galleryImages : null,
            'is_active' => $request->is_active ?? $mschool->is_active,
            'updated_by' => Auth::id(),
        ]);

        return back()->with('success', 'Mobile School updated successfully');
    }

    public function destroy($id)
    {
        $mschool = MobileSchool::findOrFail($id);

        // Delete associated images
        if ($mschool->cover_image && !filter_var($mschool->cover_image, FILTER_VALIDATE_URL)) {
            Storage::delete($mschool->cover_image);
        }
        if ($mschool->thumbnail && !filter_var($mschool->thumbnail, FILTER_VALIDATE_URL)) {
            Storage::delete($mschool->thumbnail);
        }
        if ($mschool->gallery_images) {
            foreach ($mschool->gallery_images as $image) {
                if (!filter_var($image, FILTER_VALIDATE_URL)) {
                    Storage::delete($image);
                }
            }
        }

        $mschool->delete();

        return back()->with('success', 'Mobile School deleted successfully');
    }

    /**
     * Upload image to storage.
     */
    private function uploadImage($file, $type = 'cover')
    {
        $path = $file->store("mobile-schools/{$type}s", 'public');

        // Resize image if it's too large
        $manager = new ImageManager(new Driver());
        $image = $manager->read(storage_path('app/public/' . $path));

        if ($type === 'cover') {
            $image->scale(width: 1200);
        } else {
            $image->scale(width: 800);
        }

        $image->save(storage_path('app/public/' . $path));

        return $path;
    }

    /**
     * Create thumbnail from image.
     */
    private function createThumbnail($imagePath)
    {
        if (filter_var($imagePath, FILTER_VALIDATE_URL)) {
            return null;
        }

        $manager = new ImageManager(new Driver());
        $image = $manager->read(storage_path('app/public/' . $imagePath));

        // Create thumbnail path
        $thumbnailPath = str_replace('/covers/', '/thumbnails/', $imagePath);
        $thumbnailPath = preg_replace('/\.[^.]+$/', '_thumb.jpg', $thumbnailPath);

        // Create and save thumbnail
        $image->scale(width: 300, height: 200);
        $image->toJpeg(80)->save(storage_path('app/public/' . $thumbnailPath));

        return $thumbnailPath;
    }
}
