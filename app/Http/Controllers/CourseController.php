<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class CourseController extends Controller
{
    public function publicIndex()
{
    $courses = Course::with(['department', 'intakes'])
        ->where('is_active', true)
        ->orderBy('featured', 'desc')
        ->orderBy('sort_order', 'asc')
        ->orderBy('name', 'asc')
        ->paginate(9); // or whatever number you prefer per page

    $departments = Department::where('is_active', true)
        ->has('courses')
        ->withCount(['courses' => function($query) {
            $query->where('is_active', true);
        }])
        ->get();

    return view('ktvtc.website.courses.public-index', compact('courses', 'departments'));
}

    // Show single course
    public function show($slug)
    {
        $course = Course::with(['department', 'intakes' => function($query) {
            $query->where('is_active', true)->orderBy('year', 'desc')->orderByRaw("FIELD(month, 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December')");
        }])
        ->where('slug', $slug)
        ->where('is_active', true)
        ->firstOrFail();

        $relatedCourses = Course::with('department')
            ->where('department_id', $course->department_id)
            ->where('id', '!=', $course->id)
            ->where('is_active', true)
            ->limit(3)
            ->get();

        return view('ktvtc.website.courses.show', compact('course', 'relatedCourses'));
    }
    public function index()
    {
        $courses = Course::with('department')->latest()->get();
        $departments = Department::where('is_active', true)->get();
        return view('ktvtc.website.courses.index', compact('courses', 'departments'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'department_id' => 'required|exists:departments,id',
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
            'duration' => 'required|string|max:100',
            'total_hours' => 'nullable|string|max:50',
            'schedule' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'requirements' => 'nullable|string',
            'fees_breakdown' => 'nullable|string',
           'delivery_modes' => 'required|array',
        'delivery_modes.*' => 'in:onsite,virtual,hybrid',
            'what_you_will_learn' => 'nullable|string',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg|max:10240',
            'level' => 'required|in:beginner,intermediate,advanced',
            'featured' => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        // Handle image upload
        $coverImagePath = null;
        if ($request->hasFile('cover_image')) {
            $coverImagePath = $request->file('cover_image')->store('courses/cover-images', 'public');
        }

        // Generate unique slug
        $slug = Str::slug($request->name);
        $originalSlug = $slug;
        $counter = 1;

        while (Course::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        // Parse fees breakdown if provided
        $feesBreakdown = $this->parseFeesBreakdown($request->fees_breakdown);

        Course::create([
            'department_id' => $request->department_id,
            'name' => $request->name,
            'slug' => $slug,
            'code' => $request->code,
            'duration' => $request->duration,
            'total_hours' => $request->total_hours,
            'schedule' => $request->schedule,
            'description' => $request->description,
            'requirements' => $request->requirements,
            'fees_breakdown' => $feesBreakdown,
           'delivery_mode' => implode(',', $request->delivery_modes),
            'what_you_will_learn' => $request->what_you_will_learn,
            'cover_image' => $coverImagePath,
            'level' => $request->level,
            'featured' => $request->boolean('featured'),
            'sort_order' => $request->sort_order ?? 0,
            'is_active' => $request->boolean('is_active'),
            'created_by' => Auth::id(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return back()->with('success', 'Course added successfully');
    }
public function update(Request $request)
{
    // Get course ID from request
    $request->validate([
        'course_id' => 'required|exists:courses,id', // Add course_id validation
        'department_id' => 'required|exists:departments,id',
        'name' => 'required|string|max:255',
        'code' => 'nullable|string|max:50',
        'duration' => 'required|string|max:100',
        'total_hours' => 'nullable|string|max:50',
        'schedule' => 'nullable|string|max:255',
        'description' => 'nullable|string',
        'requirements' => 'nullable|string',
        'fees_breakdown' => 'nullable|string',
          'delivery_modes' => 'required|array',
        'delivery_modes.*' => 'in:onsite,virtual,hybrid',
        'what_you_will_learn' => 'nullable|string',
        'cover_image' => 'nullable|image|mimes:jpeg,png,jpg|max:10240',
        'level' => 'required|in:beginner,intermediate,advanced',
        'featured' => 'nullable|boolean',
        'sort_order' => 'nullable|integer|min:0',
        'is_active' => 'nullable|boolean',
    ]);

    $course = Course::findOrFail($request->course_id);

    // Handle image upload
    $coverImagePath = $course->cover_image;
    if ($request->hasFile('cover_image')) {
        // Delete old image if exists
        if ($course->cover_image && Storage::disk('public')->exists($course->cover_image)) {
            Storage::disk('public')->delete($course->cover_image);
        }
        $coverImagePath = $request->file('cover_image')->store('courses/cover-images', 'public');
    }

    // Generate new slug only if name changed
    $slug = $course->slug;
    if ($request->name !== $course->name) {
        $slug = Str::slug($request->name);
        $originalSlug = $slug;
        $counter = 1;

        while (Course::where('slug', $slug)->where('id', '!=', $request->course_id)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }
    }

    // Parse fees breakdown if provided
    $feesBreakdown = $this->parseFeesBreakdown($request->fees_breakdown);

    $course->update([
        'department_id' => $request->department_id,
        'name' => $request->name,
        'slug' => $slug,
        'code' => $request->code,
        'duration' => $request->duration,
        'total_hours' => $request->total_hours,
        'schedule' => $request->schedule,
        'description' => $request->description,
        'requirements' => $request->requirements,
        'fees_breakdown' => $feesBreakdown,
         'delivery_mode' => implode(',', $request->delivery_modes),
        'what_you_will_learn' => $request->what_you_will_learn,
        'cover_image' => $coverImagePath,
        'level' => $request->level,
        'featured' => $request->boolean('featured'),
        'sort_order' => $request->sort_order ?? $course->sort_order,
        'is_active' => $request->boolean('is_active'),
        'updated_by' => Auth::id(),
    ]);

    return back()->with('success', 'Course updated successfully');
}

    public function destroy($id)
    {
        $course = Course::findOrFail($id);

        // Delete cover image if exists
        if ($course->cover_image && Storage::disk('public')->exists($course->cover_image)) {
            Storage::disk('public')->delete($course->cover_image);
        }

        $course->delete();

        return back()->with('success', 'Course deleted successfully');
    }

    /**
     * Parse fees breakdown text into JSON format
     */
    private function parseFeesBreakdown($feesText)
    {
        if (empty($feesText)) {
            return null;
        }

        $feesArray = [];
        $lines = explode("\n", $feesText);

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;

            $parts = explode(':', $line, 2);
            if (count($parts) === 2) {
                $key = trim($parts[0]);
                $value = trim($parts[1]);
                if (!empty($key) && !empty($value)) {
                    $feesArray[$key] = $value;
                }
            }
        }

        return !empty($feesArray) ? $feesArray : null;
    }
}
