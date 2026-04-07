<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AboutPage;
use App\Models\Certification;
use App\Models\Course;
use App\Models\Department;

use App\Models\AboutImage;
use App\Models\Banner;
use App\Models\Blog;
use App\Models\Event;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AboutPageController extends Controller
{

public function welcome()
{
    $banners = Banner::where('is_active', true)
                    ->orderBy('order')
                    ->get();

    // Get featured courses
    $featuredCourses = Course::with(['department', 'intakes'])
        ->where('is_active', true)
        ->where('featured', true)
        ->orderBy('sort_order', 'asc')
        ->orderBy('name', 'asc')
        ->limit(6)
        ->get();

    if ($featuredCourses->count() < 6) {
        $additionalCourses = Course::with(['department', 'intakes'])
            ->where('is_active', true)
            ->where('featured', false)
            ->orderBy('sort_order', 'asc')
            ->orderBy('name', 'asc')
            ->limit(6 - $featuredCourses->count())
            ->get();
        $featuredCourses = $featuredCourses->merge($additionalCourses);
    }

    // Get latest blogs
    $latestBlogs = Blog::with('category')
        ->where('is_active', true)
        ->where('is_published', true)
        ->orderBy('published_at', 'desc')
        ->limit(3)
        ->get();

    // Get latest events
    $latestEvents = Event::where('is_published', true)
        ->where('is_active', true)
        ->where('event_start_date', '>=', now())
        ->orderBy('event_start_date', 'asc')
        ->limit(3)
        ->get();

    $departments = Department::where('is_active', true)
        ->has('courses')
        ->withCount(['courses' => function($query) {
            $query->where('is_active', true);
        }])
        ->get();

    // ✅ FIXED: Get certifications for the website (using the imported class)
    $certifications = Certification::where('is_active', true)
        ->orderBy('display_order')
        ->orderBy('name')
        ->get();

    $accreditations = Certification::where('certification_type', 'accreditation')
        ->where('is_active', true)
        ->orderBy('display_order')
        ->get();

    $examBodies = Certification::where('certification_type', 'examination_body')
        ->where('is_active', true)
        ->orderBy('display_order')
        ->get();

    $professionalBodies = Certification::where('certification_type', 'professional_body')
        ->where('is_active', true)
        ->orderBy('display_order')
        ->get();

    $registrations = Certification::where('certification_type', 'registration')
        ->where('is_active', true)
        ->orderBy('display_order')
        ->get();

    return view('welcome', compact(
        'banners',
        'featuredCourses',
        'departments',
        'latestEvents',
        'latestBlogs',
        'certifications',
        'accreditations',
        'examBodies',
        'professionalBodies',
        'registrations'
    ));
}
    /**
 * Display about page for public website
 */
public function publicIndex()
{
    $aboutPage = AboutPage::first();
    $aboutImages = AboutImage::where('is_active', true)
        ->orderBy('order')
        ->get();

    return view('ktvtc.website.aboutpage.aboutpage', compact('aboutPage', 'aboutImages'));
}
    public function index()
    {
        $aboutPage = AboutPage::first();
        $aboutImages = AboutImage::orderBy('order')->get();

        return view('ktvtc.website.aboutpage.index', compact('aboutPage', 'aboutImages'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'our_story' => 'nullable|string',
            'mission' => 'nullable|string|max:1000',
            'vision' => 'nullable|string|max:1000',
            'core_values' => 'nullable|string',
            'banner_image' => 'nullable|string',
            'video_url' => 'nullable|url',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
        ]);

        // Check if about page already exists
        $aboutPage = AboutPage::first();

        if ($aboutPage) {
            // Update existing
            $aboutPage->update([
                'our_story' => $request->our_story,
                'mission' => $request->mission,
                'vision' => $request->vision,
                'core_values' => $request->core_values,
                'banner_image' => $request->banner_image,
                'video_url' => $request->video_url,
                'meta_title' => $request->meta_title,
                'meta_description' => $request->meta_description,
                'updated_by' => Auth::id(),
            ]);

            $message = 'About page updated successfully.';
        } else {
            // Create new
            AboutPage::create([
                'our_story' => $request->our_story,
                'mission' => $request->mission,
                'vision' => $request->vision,
                'core_values' => $request->core_values,
                'banner_image' => $request->banner_image,
                'video_url' => $request->video_url,
                'meta_title' => $request->meta_title,
                'meta_description' => $request->meta_description,
                'created_by' => Auth::id(),
                'updated_by' => Auth::id(),
            ]);

            $message = 'About page created successfully.';
        }

        return redirect()->back()->with('success', $message);
    }

    public function storeImage(Request $request)
    {
        $request->validate([
            'about_page_id' => 'required|exists:about_pages,id',
            'image_path' => 'required|string',
            'caption' => 'nullable|string|max:255',
            'order' => 'nullable|integer',
        ]);

        AboutImage::create([
            'about_page_id' => $request->about_page_id,
            'image_path' => $request->image_path,
            'caption' => $request->caption,
            'order' => $request->order ?? 0,
            'created_by' => Auth::id(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->back()->with('success', 'Image added successfully.');
    }

    public function updateImage(Request $request, $id)
    {
        $aboutImage = AboutImage::findOrFail($id);

        $request->validate([
            'image_path' => 'required|string',
            'caption' => 'nullable|string|max:255',
            'order' => 'nullable|integer',
        ]);

        $aboutImage->update([
            'image_path' => $request->image_path,
            'caption' => $request->caption,
            'order' => $request->order ?? 0,
            'updated_by' => Auth::id(),
        ]);

        return redirect()->back()->with('success', 'Image updated successfully.');
    }

    public function destroyImage($id)
    {
        AboutImage::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Image deleted successfully.');
    }
}
