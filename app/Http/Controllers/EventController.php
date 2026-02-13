<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class EventController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function publicindex()
{
    $events = Event::where('is_published', true)
                  ->where('is_active', true)
                  ->where('event_start_date', '>=', now())
                  ->with(['creator', 'updater'])
                  ->orderBy('event_start_date')
                  ->paginate(10); // or whatever number you prefer

    $featuredEvents = Event::where('is_published', true)
                          ->where('is_active', true)
                          ->where('is_featured', true)
                          ->where('event_start_date', '>=', now())
                          ->orderBy('event_start_date')
                          ->take(5)
                          ->get();

    return view('ktvtc.website.events.events', compact('events', 'featuredEvents'));
}

public function byType($type)
{
    $events = Event::where('is_published', true)
                  ->where('is_active', true)
                  ->where('event_type', $type)
                  ->where('event_start_date', '>=', now())
                  ->orderBy('event_start_date')
                  ->paginate(10);

    $featuredEvents = Event::where('is_published', true)
                          ->where('is_active', true)
                          ->where('is_featured', true)
                          ->where('event_start_date', '>=', now())
                          ->orderBy('event_start_date')
                          ->take(5)
                          ->get();

    return view('ktvtc.website.events.events', compact('events', 'featuredEvents'));
}

  public function show($slug)
    {
        $event = Event::where('slug', $slug)
                     ->where('is_published', true)
                     ->where('is_active', true)
                     ->with(['creator', 'updater'])
                     ->firstOrFail();

        // Increment view count
        $event->increment('view_count');

        // Get related events
        $relatedEvents = Event::where('is_published', true)
                            ->where('is_active', true)
                            ->where('id', '!=', $event->id)
                            ->where(function($query) use ($event) {
                                $query->where('event_type', $event->event_type)
                                      ->orWhere('department', $event->department);
                            })
                            ->where('event_start_date', '>=', now())
                            ->orderBy('event_start_date')
                            ->take(3)
                            ->get();

        return view('ktvtc.website.events.show', compact('event', 'relatedEvents'));
    }

    public function index()
    {
        $events = Event::with(['creator', 'updater'])
                      ->latest()
                      ->get();
        return view('ktvtc.website.events.index', compact('events'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'short_description' => 'nullable|string',
            'description' => 'nullable|string',
            'location' => 'required|string|max:255',
            'event_start_date' => 'required|date',
            'event_end_date' => 'required|date|after:event_start_date',
            'registration_start_date' => 'nullable|date',
            'registration_end_date' => 'nullable|date|after:registration_start_date',
            'event_type' => 'required|string|in:bootcamp,workshop,trip,mentorship,seminar,social,conference,other',
            'department' => 'nullable|string|max:255',
            'target_audience' => 'nullable|string|in:all_students,freshmen,sophomores,juniors,seniors,graduate,faculty,staff',
            'is_paid' => 'nullable|boolean',
            'price' => 'nullable|numeric|min:0',
            'early_bird_price' => 'nullable|numeric|min:0',
            'early_bird_end_date' => 'nullable|date',
            'max_attendees' => 'nullable|integer|min:1',
            'registered_attendees' => 'nullable|integer|min:0',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240',
            'banner_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240',
            'is_active' => 'nullable|boolean',
            'is_published' => 'nullable|boolean',
            'is_featured' => 'nullable|boolean',
            'sort_order' => 'nullable|integer',
            'organizer_name' => 'nullable|string|max:255',
            'organizer_email' => 'nullable|email|max:255',
            'organizer_phone' => 'nullable|string|max:255',
            'organizer_website' => 'nullable|url|max:255',
        ]);

        // Handle file uploads
        $coverImagePath = null;
        if ($request->hasFile('cover_image')) {
            $coverImagePath = $request->file('cover_image')->store('events/cover-images', 'public');
        }

        $bannerImagePath = null;
        if ($request->hasFile('banner_image')) {
            $bannerImagePath = $request->file('banner_image')->store('events/banner-images', 'public');
        }

        // Generate unique slug
        $slug = Str::slug($request->title);
        $originalSlug = $slug;
        $counter = 1;

        while (Event::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        // Set published_at if event is being published
        $publishedAt = null;
        if ($request->boolean('is_published')) {
            $publishedAt = now();
        }

        Event::create([
            'title' => $request->title,
            'slug' => $slug,
            'short_description' => $request->short_description,
            'description' => $request->description,
            'location' => $request->location,
            'event_start_date' => $request->event_start_date,
            'event_end_date' => $request->event_end_date,
            'registration_start_date' => $request->registration_start_date,
            'registration_end_date' => $request->registration_end_date,
            'event_type' => $request->event_type,
            'department' => $request->department,
            'target_audience' => $request->target_audience,
            'is_paid' => $request->boolean('is_paid'),
            'price' => $request->is_paid ? $request->price : null,
            'early_bird_price' => $request->is_paid ? $request->early_bird_price : null,
            'early_bird_end_date' => $request->is_paid ? $request->early_bird_end_date : null,
            'max_attendees' => $request->max_attendees,
            'registered_attendees' => $request->registered_attendees ?? 0,
            'cover_image' => $coverImagePath,
            'banner_image' => $bannerImagePath,
            'is_active' => $request->boolean('is_active', true),
            'is_published' => $request->boolean('is_published'),
            'is_featured' => $request->boolean('is_featured'),
            'published_at' => $publishedAt,
            'sort_order' => $request->sort_order ?? 0,
            'organizer_name' => $request->organizer_name,
            'organizer_email' => $request->organizer_email,
            'organizer_phone' => $request->organizer_phone,
            'organizer_website' => $request->organizer_website,
            'created_by' => Auth::id(),
            'updated_by' => Auth::id(),
        ]);

        return redirect()->back()->with('success', 'Event created successfully.');
    }

    /**
     * Display the specified resource.
     */


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Event $event)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'short_description' => 'nullable|string',
            'description' => 'nullable|string',
            'location' => 'required|string|max:255',
            'event_start_date' => 'required|date',
            'event_end_date' => 'required|date|after:event_start_date',
            'registration_start_date' => 'nullable|date',
            'registration_end_date' => 'nullable|date|after:registration_start_date',
            'event_type' => 'required|string|in:bootcamp,workshop,trip,mentorship,seminar,social,conference,other',
            'department' => 'nullable|string|max:255',
            'target_audience' => 'nullable|string|in:all_students,freshmen,sophomores,juniors,seniors,graduate,faculty,staff',
            'is_paid' => 'nullable|boolean',
            'price' => 'nullable|numeric|min:0',
            'early_bird_price' => 'nullable|numeric|min:0',
            'early_bird_end_date' => 'nullable|date',
            'max_attendees' => 'nullable|integer|min:1',
            'registered_attendees' => 'nullable|integer|min:0',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240',
            'banner_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240',
            'is_active' => 'nullable|boolean',
            'is_published' => 'nullable|boolean',
            'is_featured' => 'nullable|boolean',
            'sort_order' => 'nullable|integer',
            'organizer_name' => 'nullable|string|max:255',
            'organizer_email' => 'nullable|email|max:255',
            'organizer_phone' => 'nullable|string|max:255',
            'organizer_website' => 'nullable|url|max:255',
        ]);

        // Handle file uploads
        $coverImagePath = $event->cover_image;
        if ($request->hasFile('cover_image')) {
            // Delete old cover image if exists
            if ($event->cover_image && Storage::disk('public')->exists($event->cover_image)) {
                Storage::disk('public')->delete($event->cover_image);
            }
            $coverImagePath = $request->file('cover_image')->store('events/cover-images', 'public');
        }

        $bannerImagePath = $event->banner_image;
        if ($request->hasFile('banner_image')) {
            // Delete old banner image if exists
            if ($event->banner_image && Storage::disk('public')->exists($event->banner_image)) {
                Storage::disk('public')->delete($event->banner_image);
            }
            $bannerImagePath = $request->file('banner_image')->store('events/banner-images', 'public');
        }

        // Generate new slug only if title changed
        $slug = $event->slug;
        if ($request->title !== $event->title) {
            $slug = Str::slug($request->title);
            $originalSlug = $slug;
            $counter = 1;

            while (Event::where('slug', $slug)->where('id', '!=', $event->id)->exists()) {
                $slug = $originalSlug . '-' . $counter;
                $counter++;
            }
        }

        // Handle published_at
        $publishedAt = $event->published_at;
        if ($request->boolean('is_published') && !$event->is_published) {
            $publishedAt = now();
        } elseif (!$request->boolean('is_published') && $event->is_published) {
            $publishedAt = null;
        }

        $event->update([
            'title' => $request->title,
            'slug' => $slug,
            'short_description' => $request->short_description,
            'description' => $request->description,
            'location' => $request->location,
            'event_start_date' => $request->event_start_date,
            'event_end_date' => $request->event_end_date,
            'registration_start_date' => $request->registration_start_date,
            'registration_end_date' => $request->registration_end_date,
            'event_type' => $request->event_type,
            'department' => $request->department,
            'target_audience' => $request->target_audience,
            'is_paid' => $request->boolean('is_paid'),
            'price' => $request->is_paid ? $request->price : null,
            'early_bird_price' => $request->is_paid ? $request->early_bird_price : null,
            'early_bird_end_date' => $request->is_paid ? $request->early_bird_end_date : null,
            'max_attendees' => $request->max_attendees,
            'registered_attendees' => $request->registered_attendees ?? $event->registered_attendees,
            'cover_image' => $coverImagePath,
            'banner_image' => $bannerImagePath,
            'is_active' => $request->boolean('is_active'),
            'is_published' => $request->boolean('is_published'),
            'is_featured' => $request->boolean('is_featured'),
            'published_at' => $publishedAt,
            'sort_order' => $request->sort_order ?? $event->sort_order,
            'organizer_name' => $request->organizer_name,
            'organizer_email' => $request->organizer_email,
            'organizer_phone' => $request->organizer_phone,
            'organizer_website' => $request->organizer_website,
            'updated_by' => Auth::id(),
        ]);

        return redirect()->back()->with('success', 'Event updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Event $event)
    {
        // Delete associated images
        if ($event->cover_image && Storage::disk('public')->exists($event->cover_image)) {
            Storage::disk('public')->delete($event->cover_image);
        }

        if ($event->banner_image && Storage::disk('public')->exists($event->banner_image)) {
            Storage::disk('public')->delete($event->banner_image);
        }

        $event->delete();

        return redirect()->back()->with('success', 'Event deleted successfully.');
    }

    /**
     * Get events for API (for frontend display)
     */
    public function apiIndex()
    {
        $events = Event::where('is_published', true)
                      ->where('is_active', true)
                      ->where('event_start_date', '>=', now())
                      ->orderBy('event_start_date')
                      ->get();

        return response()->json($events);
    }

    /**
     * Get featured events
     */
    public function featured()
    {
        $events = Event::where('is_published', true)
                      ->where('is_active', true)
                      ->where('is_featured', true)
                      ->where('event_start_date', '>=', now())
                      ->orderBy('event_start_date')
                      ->take(6)
                      ->get();

        return response()->json($events);
    }

    /**
     * Get events by department
     */
    public function byDepartment($department)
    {
        $events = Event::where('is_published', true)
                      ->where('is_active', true)
                      ->where('department', $department)
                      ->where('event_start_date', '>=', now())
                      ->orderBy('event_start_date')
                      ->get();

        return response()->json($events);
    }

    /**
     * Increment view count
     */
    public function incrementViews(Event $event)
    {
        $event->increment('view_count');

        return response()->json(['success' => true]);
    }
}
