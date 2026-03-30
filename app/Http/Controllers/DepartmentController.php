<?php

namespace App\Http\Controllers;

use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DepartmentController extends Controller
{
    public function show($slug)
    {
        $department = Department::with(['courses' => function($query) {
            $query->where('is_active', true)
                ->orderBy('featured', 'desc')
                ->orderBy('sort_order', 'asc')
                ->orderBy('name', 'asc');
        }])
        ->where('slug', $slug)
        ->where('is_active', true)
        ->firstOrFail();

        $otherDepartments = Department::where('is_active', true)
            ->where('id', '!=', $department->id)
            ->has('courses')
            ->get();

        return view('ktvtc.website.department.show', compact('department', 'otherDepartments'));
    }

    public function index()
    {
        $departments = Department::latest()->get();
        return view('ktvtc.website.department.index', compact('departments'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:departments,name',
            'description' => 'nullable|string',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'is_active' => 'nullable|boolean',
        ]);

        if ($request->hasFile('cover_image')) {
            $validated['cover_image'] = $request->file('cover_image')
                ->store('departments/cover-images', 'public');
        }

        $validated['slug'] = $this->generateUniqueSlug($request->name);
        $validated['is_active'] = $request->boolean('is_active');
        $validated['created_by'] = Auth::id();
        $validated['ip_address'] = $request->ip();
        $validated['user_agent'] = $request->userAgent();

        Department::create($validated);

        return redirect()->back()->with('success', 'Department added successfully.');
    }

    public function update(Request $request, Department $department)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:departments,name,' . $department->id,
            'description' => 'nullable|string',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'is_active' => 'nullable|boolean',
        ]);

        if ($request->hasFile('cover_image')) {
            if ($department->cover_image && Storage::disk('public')->exists($department->cover_image)) {
                Storage::disk('public')->delete($department->cover_image);
            }
            $validated['cover_image'] = $request->file('cover_image')
                ->store('departments/cover-images', 'public');
        } else {
            $validated['cover_image'] = $department->cover_image;
        }

        if ($request->name !== $department->name) {
            $validated['slug'] = $this->generateUniqueSlug($request->name, $department->id);
        } else {
            $validated['slug'] = $department->slug;
        }

        $validated['is_active'] = $request->boolean('is_active');
        $validated['updated_by'] = Auth::id();

        $department->update($validated);

        return redirect()->back()->with('success', 'Department updated successfully.');
    }

    public function destroy(Department $department)
    {
        if ($department->courses()->exists()) {
            return redirect()->back()->with('error', 'Cannot delete department. It has associated courses.');
        }

        if ($department->cover_image && Storage::disk('public')->exists($department->cover_image)) {
            Storage::disk('public')->delete($department->cover_image);
        }

        $department->delete();
        return redirect()->back()->with('success', 'Department deleted successfully.');
    }

    private function generateUniqueSlug($name, $ignoreId = null)
    {
        $slug = Str::slug($name);
        $originalSlug = $slug;
        $counter = 1;

        while (Department::where('slug', $slug)
            ->when($ignoreId, fn($q) => $q->where('id', '!=', $ignoreId))
            ->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }
}
