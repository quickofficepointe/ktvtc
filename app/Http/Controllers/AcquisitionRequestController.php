<?php

namespace App\Http\Controllers;

use App\Models\AcquisitionRequest;
use App\Models\Branch;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AcquisitionRequestController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = AcquisitionRequest::with(['requester', 'branch']);

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $requests = $query->orderBy('created_at', 'desc')->paginate(20);
        $branches = Branch::where('is_active', true)->get();

        // Calculate stats
        $pendingCount = AcquisitionRequest::where('status', 'pending')->count();
        $approvedCount = AcquisitionRequest::where('status', 'approved')->count();
        $estimatedCost = AcquisitionRequest::where('status', 'approved')
            ->sum(\DB::raw('estimated_price * quantity'));

        return view('ktvtc.library.acquisition.index', compact(
            'requests',
            'branches',
            'pendingCount',
            'approvedCount',
            'estimatedCost'
        ));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'author' => 'nullable|string|max:255',
            'isbn' => 'nullable|string|max:20',
            'publisher' => 'nullable|string|max:255',
            'publication_year' => 'nullable|integer|min:1000|max:' . (date('Y') + 1),
            'branch_id' => 'required|exists:branches,id',
            'quantity' => 'required|integer|min:1|max:100',
            'estimated_price' => 'nullable|numeric|min:0',
            'reason' => 'required|string|max:1000',
        ]);

        $validated['requester_id'] = Auth::id();
        $validated['status'] = 'pending';

        AcquisitionRequest::create($validated);

        return redirect()->route('acquisition-requests.index')
            ->with('success', 'Acquisition request submitted successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(AcquisitionRequest $acquisitionRequest)
    {
        $acquisitionRequest->load(['requester', 'branch']);
        return view('acquisition-requests.show', compact('acquisitionRequest'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, AcquisitionRequest $acquisitionRequest)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'author' => 'nullable|string|max:255',
            'isbn' => 'nullable|string|max:20',
            'publisher' => 'nullable|string|max:255',
            'publication_year' => 'nullable|integer',
            'branch_id' => 'required|exists:branches,id',
            'quantity' => 'required|integer|min:1',
            'estimated_price' => 'nullable|numeric|min:0',
            'reason' => 'required|string|max:1000',
            'status' => 'required|in:pending,approved,rejected,ordered,received',
            'admin_notes' => 'nullable|string|max:1000',
        ]);

        $acquisitionRequest->update($validated);

        return redirect()->route('acquisition-requests.index')
            ->with('success', 'Request updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AcquisitionRequest $acquisitionRequest)
    {
        $acquisitionRequest->delete();

        return redirect()->route('acquisition-requests.index')
            ->with('success', 'Request deleted successfully.');
    }

    /**
     * Approve an acquisition request.
     */
    public function approve(AcquisitionRequest $acquisitionRequest)
    {
        $acquisitionRequest->update([
            'status' => 'approved',
            'admin_notes' => request('admin_notes', ''),
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        return redirect()->route('acquisition-requests.index')
            ->with('success', 'Request approved successfully.');
    }

    /**
     * Reject an acquisition request.
     */
    public function reject(AcquisitionRequest $acquisitionRequest)
    {
        $acquisitionRequest->update([
            'status' => 'rejected',
            'admin_notes' => request('admin_notes', ''),
            'rejected_by' => Auth::id(),
            'rejected_at' => now(),
        ]);

        return redirect()->route('acquisition-requests.index')
            ->with('success', 'Request rejected.');
    }

    /**
     * Mark request as ordered.
     */
    public function markOrdered(AcquisitionRequest $acquisitionRequest)
    {
        $acquisitionRequest->update([
            'status' => 'ordered',
            'ordered_at' => now(),
        ]);

        return redirect()->route('acquisition-requests.index')
            ->with('success', 'Request marked as ordered.');
    }

    /**
     * Mark request as received.
     */
    public function markReceived(AcquisitionRequest $acquisitionRequest)
    {
        $acquisitionRequest->update([
            'status' => 'received',
            'received_at' => now(),
        ]);

        // Here you could automatically create book records from the request
        // when it's marked as received

        return redirect()->route('acquisition-requests.index')
            ->with('success', 'Request marked as received.');
    }
}
