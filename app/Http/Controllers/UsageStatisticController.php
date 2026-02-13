<?php

namespace App\Http\Controllers;

use App\Models\UsageStatistic;
use App\Models\Branch;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class UsageStatisticController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $startDate = $request->input('start_date', now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->format('Y-m-d'));

        $query = UsageStatistic::with('branch')
            ->whereBetween('stat_date', [$startDate, $endDate])
            ->orderBy('stat_date', 'desc');

        $statistics = $query->paginate(20);
        $branches = Branch::where('is_active', true)->get();

        // Overall statistics
        $overallStats = [
            'total_borrows' => UsageStatistic::whereBetween('stat_date', [$startDate, $endDate])
                ->sum('total_borrows'),
            'new_members' => UsageStatistic::whereBetween('stat_date', [$startDate, $endDate])
                ->sum('new_members'),
            'total_fines' => UsageStatistic::whereBetween('stat_date', [$startDate, $endDate])
                ->sum('total_fines'),
            'collected_fines' => UsageStatistic::whereBetween('stat_date', [$startDate, $endDate])
                ->sum('collected_fines'),
        ];

        // Branch comparison statistics - using your actual table columns
        $branchStats = UsageStatistic::with('branch')
            ->whereBetween('stat_date', [$startDate, $endDate])
            ->select(
                'branch_id',
                DB::raw('SUM(total_borrows) as total_borrows'),
                DB::raw('SUM(total_returns) as total_returns'),
                DB::raw('SUM(total_reservations) as total_reservations'),
                DB::raw('SUM(new_members) as new_members'),
                DB::raw('SUM(active_members) as active_members'),
                DB::raw('SUM(total_fines) as total_fines'),
                DB::raw('SUM(collected_fines) as collected_fines')
            )
            ->groupBy('branch_id')
            ->get()
            ->map(function ($stat) {
                // Calculate fine collection rate
                $stat->fine_collection_rate = $stat->total_fines > 0
                    ? ($stat->collected_fines / $stat->total_fines) * 100
                    : 0;

                // Calculate utilization rate dynamically
                $totalItems = DB::table('items')->where('branch_id', $stat->branch_id)->count();
                $stat->utilization_rate = $totalItems > 0
                    ? ($stat->total_borrows / $totalItems) * 100
                    : 0;

                return $stat;
            });

        return view('ktvtc.library.statistics.index', compact(
            'statistics',
            'branches',
            'overallStats',
            'branchStats',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'stat_date' => 'required|date',
            'total_borrows' => 'required|integer|min:0',
            'total_returns' => 'required|integer|min:0',
            'total_reservations' => 'required|integer|min:0',
            'new_members' => 'required|integer|min:0',
            'active_members' => 'required|integer|min:0',
            'total_fines' => 'required|numeric|min:0',
            'collected_fines' => 'required|numeric|min:0'
        ]);

        // Check if statistic already exists for this date and branch
        $existing = UsageStatistic::where('stat_date', $validated['stat_date'])
            ->where('branch_id', $validated['branch_id'])
            ->exists();

        if ($existing) {
            return redirect()->back()->with('error', 'Usage statistic already exists for this date and branch.');
        }

        $statistic = UsageStatistic::create($validated);

        return redirect()->route('usage-statistics.index')
            ->with('success', 'Usage statistic added successfully.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, UsageStatistic $usageStatistic)
    {
        $validated = $request->validate([
            'stat_date' => 'required|date',
            'total_borrows' => 'required|integer|min:0',
            'total_returns' => 'required|integer|min:0',
            'total_reservations' => 'required|integer|min:0',
            'new_members' => 'required|integer|min:0',
            'active_members' => 'required|integer|min:0',
            'total_fines' => 'required|numeric|min:0',
            'collected_fines' => 'required|numeric|min:0'
        ]);

        // Check if updating would create a duplicate
        if ($usageStatistic->stat_date != $validated['stat_date'] || $usageStatistic->branch_id != $request->branch_id) {
            $existing = UsageStatistic::where('stat_date', $validated['stat_date'])
                ->where('branch_id', $request->branch_id)
                ->where('id', '!=', $usageStatistic->id)
                ->exists();

            if ($existing) {
                return redirect()->back()->with('error', 'Another usage statistic already exists for this date and branch.');
            }
        }

        $usageStatistic->update($validated);

        return redirect()->route('usage-statistics.index')
            ->with('success', 'Usage statistic updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(UsageStatistic $usageStatistic)
    {
        $usageStatistic->delete();

        return redirect()->route('usage-statistics.index')
            ->with('success', 'Usage statistic deleted successfully.');
    }

    /**
     * Show daily details
     */
    public function details(UsageStatistic $usageStatistic)
    {
        // Calculate additional metrics
        $totalItems = DB::table('items')->where('branch_id', $usageStatistic->branch_id)->count();
        $utilizationRate = $totalItems > 0
            ? ($usageStatistic->total_borrows / $totalItems) * 100
            : 0;

        $fineCollectionRate = $usageStatistic->total_fines > 0
            ? ($usageStatistic->collected_fines / $usageStatistic->total_fines) * 100
            : 0;

        $tpm = $usageStatistic->active_members > 0
            ? $usageStatistic->total_borrows / $usageStatistic->active_members
            : 0;

        $reservationRate = $usageStatistic->total_borrows > 0
            ? ($usageStatistic->total_reservations / $usageStatistic->total_borrows) * 100
            : 0;

        $avgFine = $usageStatistic->total_borrows > 0
            ? $usageStatistic->total_fines / $usageStatistic->total_borrows
            : 0;

        return response()->json([
            'stat_date' => $usageStatistic->stat_date->format('M d, Y'),
            'branch_name' => $usageStatistic->branch->name,
            'total_borrows' => $usageStatistic->total_borrows,
            'total_returns' => $usageStatistic->total_returns,
            'total_reservations' => $usageStatistic->total_reservations,
            'new_members' => $usageStatistic->new_members,
            'active_members' => $usageStatistic->active_members,
            'total_fines' => number_format($usageStatistic->total_fines, 2),
            'collected_fines' => number_format($usageStatistic->collected_fines, 2),
            'utilization_rate' => number_format($utilizationRate, 2),
            'fine_collection_rate' => number_format($fineCollectionRate, 2),
            'tpm' => number_format($tpm, 2),
            'reservation_rate' => number_format($reservationRate, 2),
            'avg_fine' => number_format($avgFine, 2),
            'updated_at' => $usageStatistic->updated_at->format('M d, Y h:i A')
        ]);
    }

    /**
     * Export monthly report
     */
    public function exportMonthly(Request $request)
    {
        $startDate = $request->input('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->endOfMonth()->format('Y-m-d'));

        // Generate and return report
        // This is a placeholder for actual export functionality
        return redirect()->route('usage-statistics.index')
            ->with('success', 'Monthly report generated for ' . $startDate . ' to ' . $endDate);
    }

    /**
     * Export annual report
     */
    public function exportAnnual(Request $request)
    {
        $startDate = $request->input('start_date', now()->startOfYear()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->endOfYear()->format('Y-m-d'));

        // Generate and return report
        // This is a placeholder for actual export functionality
        return redirect()->route('usage-statistics.index')
            ->with('success', 'Annual report generated for ' . $startDate . ' to ' . $endDate);
    }

    /**
     * Export daily report
     */
    public function exportDaily(UsageStatistic $usageStatistic)
    {
        // Generate and return daily report
        // This is a placeholder for actual export functionality
        return redirect()->route('usage-statistics.index')
            ->with('success', 'Daily report exported for ' . $usageStatistic->stat_date->format('M d, Y'));
    }
}
