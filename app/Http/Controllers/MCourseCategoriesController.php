<?php

namespace App\Http\Controllers;

use App\Models\MCourseCategories;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class MCourseCategoriesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categories = MCourseCategories::orderBy('sort_order')->orderBy('category_name')->get();

        return view('ktvtc.mschool.courses.coursescategory.index', compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'slug' => 'nullable|string|max:255|unique:m_course_categories,slug',
            'is_active' => 'boolean',
            'sort_order' => 'integer|min:0'
        ]);

        try {
            DB::beginTransaction();

            // Generate slug if not provided
            if (empty($validated['slug'])) {
                $validated['slug'] = Str::slug($validated['category_name']);

                // Ensure slug is unique
                $counter = 1;
                $originalSlug = $validated['slug'];
                while (MCourseCategories::where('slug', $validated['slug'])->exists()) {
                    $validated['slug'] = $originalSlug . '-' . $counter;
                    $counter++;
                }
            }

            $category = MCourseCategories::create($validated);

            DB::commit();

            return redirect()->route('course-categories.index')
                ->with('success', 'Course category created successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to create course category: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $category = MCourseCategories::findOrFail($id);

        $validated = $request->validate([
            'category_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'slug' => 'nullable|string|max:255|unique:m_course_categories,slug,' . $category->category_id . ',category_id',
            'is_active' => 'boolean',
            'sort_order' => 'integer|min:0'
        ]);

        try {
            DB::beginTransaction();

            // Generate slug if not provided
            if (empty($validated['slug'])) {
                $validated['slug'] = Str::slug($validated['category_name']);

                // Ensure slug is unique (excluding current category)
                $counter = 1;
                $originalSlug = $validated['slug'];
                while (MCourseCategories::where('slug', $validated['slug'])
                        ->where('category_id', '!=', $category->category_id)
                        ->exists()) {
                    $validated['slug'] = $originalSlug . '-' . $counter;
                    $counter++;
                }
            }

            $category->update($validated);

            DB::commit();

            return redirect()->route('course-categories.index')
                ->with('success', 'Course category updated successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to update course category: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $category = MCourseCategories::findOrFail($id);

        try {
            DB::beginTransaction();

            // Check if category has courses
            if ($category->courses()->exists()) {
                return redirect()->route('course-categories.index')
                    ->with('error', 'Cannot delete category. It has associated courses. Please reassign or delete the courses first.');
            }

            $category->delete();

            DB::commit();

            return redirect()->route('course-categories.index')
                ->with('success', 'Course category deleted successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('course-categories.index')
                ->with('error', 'Failed to delete course category: ' . $e->getMessage());
        }
    }
}
