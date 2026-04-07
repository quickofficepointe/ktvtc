<?php

namespace App\Http\Controllers;

use App\Models\Certification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class CertificationController extends Controller
{
    /**
     * Display a listing of certifications (Admin)
     */
    public function index()
    {
        $certifications = Certification::orderBy('display_order')
            ->orderBy('name')
            ->get();

        $accreditations = Certification::where('certification_type', 'accreditation')
            ->orderBy('display_order')
            ->get();

        $examBodies = Certification::where('certification_type', 'examination_body')
            ->orderBy('display_order')
            ->get();

        $professionalBodies = Certification::where('certification_type', 'professional_body')
            ->orderBy('display_order')
            ->get();

        $registrations = Certification::where('certification_type', 'registration')
            ->orderBy('display_order')
            ->get();

        return view('ktvtc.website.certifications.index', compact(
            'certifications',
            'accreditations',
            'examBodies',
            'professionalBodies',
            'registrations'
        ));
    }

    /**
     * Store a newly created certification
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'issuing_body' => 'required|string|max:255',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
            'certificate_number' => 'nullable|string|max:100',
            'issue_date' => 'nullable|date',
            'expiry_date' => 'nullable|date|after:issue_date',
            'description' => 'nullable|string',
            'certification_type' => 'required|in:accreditation,examination_body,professional_body,registration',
            'website' => 'nullable|url',
            'display_order' => 'nullable|integer',
            'is_active' => 'boolean'
        ]);

        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $data = $request->all();

        // Handle logo upload
        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('certifications', 'public');
            $data['logo_path'] = $logoPath;
        }

        $data['created_by'] = auth()->id();
        $data['is_active'] = $request->has('is_active');
        $data['display_order'] = $request->display_order ?? 0;

        $certification = Certification::create($data);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Certification added successfully',
                'certification' => $certification
            ]);
        }

        return redirect()->route('website.certifications.index')
            ->with('success', 'Certification added successfully');
    }

    /**
     * Display the specified certification (for AJAX edit modal)
     */
    public function show($id)
    {
        $certification = Certification::findOrFail($id);

        // Add logo_url attribute for JSON response
        $certification->logo_url = $certification->logo_url;

        return response()->json($certification);
    }

    /**
     * Update the specified certification
     */
    public function update(Request $request, $id)
    {
        $certification = Certification::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'issuing_body' => 'required|string|max:255',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
            'certificate_number' => 'nullable|string|max:100',
            'issue_date' => 'nullable|date',
            'expiry_date' => 'nullable|date|after:issue_date',
            'description' => 'nullable|string',
            'certification_type' => 'required|in:accreditation,examination_body,professional_body,registration',
            'website' => 'nullable|url',
            'display_order' => 'nullable|integer',
            'is_active' => 'boolean'
        ]);

        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $data = $request->all();

        // Handle logo upload
        if ($request->hasFile('logo')) {
            // Delete old logo
            if ($certification->logo_path && Storage::disk('public')->exists($certification->logo_path)) {
                Storage::disk('public')->delete($certification->logo_path);
            }
            $logoPath = $request->file('logo')->store('certifications', 'public');
            $data['logo_path'] = $logoPath;
        }

        $data['updated_by'] = auth()->id();
        $data['is_active'] = $request->has('is_active');

        $certification->update($data);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Certification updated successfully',
                'certification' => $certification->fresh()
            ]);
        }

        return redirect()->route('website.certifications.index')
            ->with('success', 'Certification updated successfully');
    }

    /**
     * Remove the specified certification
     */
    public function destroy($id)
    {
        $certification = Certification::findOrFail($id);

        // Delete logo if exists
        if ($certification->logo_path && Storage::disk('public')->exists($certification->logo_path)) {
            Storage::disk('public')->delete($certification->logo_path);
        }

        $certification->delete();

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Certification deleted successfully'
            ]);
        }

        return redirect()->route('website.certifications.index')
            ->with('success', 'Certification deleted successfully');
    }

    /**
     * Toggle certification status
     */
    public function toggleStatus($id)
    {
        $certification = Certification::findOrFail($id);
        $certification->is_active = !$certification->is_active;
        $certification->save();

        return response()->json([
            'success' => true,
            'message' => 'Status updated successfully',
            'is_active' => $certification->is_active
        ]);
    }

    /**
     * Public display of certifications for website
     */
    public function publicIndex()
    {
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

        return view('ktvtc.website.certifications.public', compact(
            'accreditations',
            'examBodies',
            'professionalBodies',
            'registrations'
        ));
    }
}
