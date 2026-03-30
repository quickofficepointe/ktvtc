<?php

namespace App\Http\Controllers;

use App\Models\FineRule;
use Illuminate\Http\Request;

class FineRuleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $rules = FineRule::orderBy('name')->get();

        return view('fine-rules.index', compact('rules'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:fine_rules,name',
            'description' => 'nullable|string|max:1000',
            'calculation_type' => 'required|in:fixed,daily,percentage',
            'fine_amount' => 'required|numeric|min:0',
            'grace_period_days' => 'nullable|integer|min:0',
            'max_fine_days' => 'nullable|integer|min:0',
            'max_fine_amount' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
        ]);

        FineRule::create($validated);

        return redirect()->route('fine-rules.index')
            ->with('success', 'Fine rule created successfully.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, FineRule $fineRule)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:fine_rules,name,' . $fineRule->id,
            'description' => 'nullable|string|max:1000',
            'calculation_type' => 'required|in:fixed,daily,percentage',
            'fine_amount' => 'required|numeric|min:0',
            'grace_period_days' => 'nullable|integer|min:0',
            'max_fine_days' => 'nullable|integer|min:0',
            'max_fine_amount' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
        ]);

        $fineRule->update($validated);

        return redirect()->route('fine-rules.index')
            ->with('success', 'Fine rule updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(FineRule $fineRule)
    {
        if ($fineRule->transactions()->exists()) {
            return redirect()->route('fine-rules.index')
                ->with('error', 'Cannot delete fine rule that is being used in transactions.');
        }

        $fineRule->delete();

        return redirect()->route('fine-rules.index')
            ->with('success', 'Fine rule deleted successfully.');
    }

    /**
     * Activate a fine rule.
     */
    public function activate(FineRule $fineRule)
    {
        $fineRule->update(['is_active' => true]);

        return redirect()->route('fine-rules.index')
            ->with('success', 'Fine rule activated.');
    }

    /**
     * Deactivate a fine rule.
     */
    public function deactivate(FineRule $fineRule)
    {
        $fineRule->update(['is_active' => false]);

        return redirect()->route('fine-rules.index')
            ->with('success', 'Fine rule deactivated.');
    }
}
