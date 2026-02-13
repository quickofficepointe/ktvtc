<?php

namespace App\Http\Controllers;

use App\Models\MCourse;
use App\Models\MCourseCategories;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class MCourseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $courses = MCourse::with('category')
            ->orderBy('is_active', 'desc')
            ->orderBy('course_name')
            ->get();

        $categories = MCourseCategories::where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('category_name')
            ->get();

        return view('ktvtc.mschool.courses.index', compact('courses', 'categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'course_name' => 'required|string|max:255',
            'course_description' => 'nullable|string',
            'course_code' => 'nullable|string|max:50|unique:m_courses,course_code',
            'duration' => 'nullable|integer|min:0',
            'price' => 'nullable|numeric|min:0',
            'category_id' => 'nullable|exists:m_course_categories,category_id',
            'is_active' => 'boolean',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240', // 10MB max
        ]);

        try {
            DB::beginTransaction();

            // Handle image upload
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('courses/images', 'public');
                $validated['image_url'] = $imagePath;
            }

            // Remove the image field from validated data as it's not in fillable
            unset($validated['image']);

            $course = MCourse::create($validated);

            DB::commit();

            return redirect()->route('courses.index')
                ->with('success', 'Course created successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to create course: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $course = MCourse::findOrFail($id);

        $validated = $request->validate([
            'course_name' => 'required|string|max:255',
            'course_description' => 'nullable|string',
            'course_code' => 'nullable|string|max:50|unique:m_courses,course_code,' . $course->course_id . ',course_id',
            'duration' => 'nullable|integer|min:0',
            'price' => 'nullable|numeric|min:0',
            'category_id' => 'nullable|exists:m_course_categories,category_id',
            'is_active' => 'boolean',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240',
        ]);

        try {
            DB::beginTransaction();

            // Handle image upload
            if ($request->hasFile('image')) {
                // Delete old image if exists
                if ($course->image_url) {
                    Storage::disk('public')->delete($course->image_url);
                }

                $imagePath = $request->file('image')->store('courses/images', 'public');
                $validated['image_url'] = $imagePath;
            }

            // Remove the image field from validated data as it's not in fillable
            unset($validated['image']);

            $course->update($validated);

            DB::commit();

            return redirect()->route('courses.index')
                ->with('success', 'Course updated successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to update course: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $course = MCourse::findOrFail($id);

        try {
            DB::beginTransaction();

            // Check if course has enrollments
            if ($course->enrollments()->exists()) {
                return redirect()->route('courses.index')
                    ->with('error', 'Cannot delete course. It has associated enrollments. Please reassign or delete the enrollments first.');
            }

            // Check if course has subjects
            if ($course->subjects()->exists()) {
                return redirect()->route('courses.index')
                    ->with('error', 'Cannot delete course. It has associated subjects. Please remove the subjects first.');
            }

            // Delete image if exists
            if ($course->image_url) {
                Storage::disk('public')->delete($course->image_url);
            }

            $course->delete();

            DB::commit();

            return redirect()->route('courses.index')
                ->with('success', 'Course deleted successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('courses.index')
                ->with('error', 'Failed to delete course: ' . $e->getMessage());
        }
    }
}
