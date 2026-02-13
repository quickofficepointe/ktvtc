@extends('ktvtc.library.layout.librarylayout')

@section('content')
<div class="container mx-auto px-4 py-8">

    {{-- Success Message --}}
    @if(session('success'))
        <div class="mb-6 p-4 rounded-lg bg-green-50 border border-green-200">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <span class="text-green-800 font-medium">{{ session('success') }}</span>
            </div>
        </div>
    @endif

    {{-- Header Section --}}
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 font-serif">Usage Statistics</h1>
            <p class="text-gray-600 mt-2">Library usage analytics and performance metrics</p>
        </div>
        <div class="flex space-x-3">
            <button onclick="generateMonthlyReport()"
                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-3 rounded-lg font-medium transition-colors flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Monthly Report
            </button>
            <button onclick="generateAnnualReport()"
                class="bg-green-600 hover:bg-green-700 text-white px-4 py-3 rounded-lg font-medium transition-colors flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
                Annual Report
            </button>
        </div>
    </div>

    {{-- Date Range Selector --}}
    <div class="bg-amber-50 rounded-xl shadow-sm border border-amber-200 p-6 mb-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h3 class="text-lg font-semibold text-gray-900 font-serif mb-2">Select Date Range</h3>
                <p class="text-sm text-gray-600">View statistics for specific time periods</p>
            </div>
            <div class="flex items-center space-x-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">From</label>
                    <input type="date" id="startDate" value="{{ $startDate }}"
                           class="border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-amber-500 focus:border-transparent">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">To</label>
                    <input type="date" id="endDate" value="{{ $endDate }}"
                           class="border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-amber-500 focus:border-transparent">
                </div>
                <div class="flex items-end">
                    <button onclick="updateDateRange()"
                        class="bg-amber-600 hover:bg-amber-700 text-white px-4 py-2 rounded-lg transition-colors">
                        Apply
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Overall Stats --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-xl shadow-sm border border-amber-200 p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-lg bg-amber-100 text-amber-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total Borrows</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $overallStats['total_borrows'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-blue-200 p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-lg bg-blue-100 text-blue-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">New Members</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $overallStats['new_members'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-green-200 p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-lg bg-green-100 text-green-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total Fines</p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($overallStats['total_fines'], 2) }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-purple-200 p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-lg bg-purple-100 text-purple-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Collected Fines</p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($overallStats['collected_fines'], 2) }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Branch Comparison --}}
    <div class="bg-white rounded-xl shadow-sm border border-amber-200 p-6 mb-6">
        <h2 class="text-lg font-semibold text-gray-900 font-serif mb-4">Branch Performance</h2>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-amber-50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-amber-800 uppercase tracking-wider">Branch</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-amber-800 uppercase tracking-wider">Borrows</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-amber-800 uppercase tracking-wider">Returns</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-amber-800 uppercase tracking-wider">Reservations</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-amber-800 uppercase tracking-wider">New Members</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-amber-800 uppercase tracking-wider">Active Members</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-amber-800 uppercase tracking-wider">Fines Generated</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-amber-800 uppercase tracking-wider">Fines Collected</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-amber-800 uppercase tracking-wider">Utilization Rate</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-amber-800 uppercase tracking-wider">Collection Rate</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-amber-100">
                    @forelse($branchStats as $branch)
                        <tr class="hover:bg-amber-50 transition-colors duration-150">
                            <td class="px-6 py-4">
                                <div class="flex items-center space-x-3">
                                    <div class="flex-shrink-0">
                                        <div class="w-10 h-10 bg-amber-100 rounded-full flex items-center justify-center border border-amber-200">
                                            <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                            </svg>
                                        </div>
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold text-gray-900">{{ $branch->branch->name }}</p>
                                        <p class="text-xs text-gray-500">{{ $branch->stat_date->format('M d, Y') }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-center">
                                    <span class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-blue-100 text-blue-800 font-bold">
                                        {{ $branch->total_borrows }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-center">
                                    <span class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-green-100 text-green-800 font-bold">
                                        {{ $branch->total_returns }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-center">
                                    <span class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-purple-100 text-purple-800 font-bold">
                                        {{ $branch->total_reservations }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-center">
                                    <span class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-indigo-100 text-indigo-800 font-bold">
                                        {{ $branch->new_members }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-center">
                                    <span class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-amber-100 text-amber-800 font-bold">
                                        {{ $branch->active_members }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-center">
                                    <span class="inline-flex items-center justify-center w-14 h-10 rounded-full bg-red-100 text-red-800 font-bold">
                                        ${{ number_format($branch->total_fines, 2) }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-center">
                                    <span class="inline-flex items-center justify-center w-14 h-10 rounded-full bg-green-100 text-green-800 font-bold">
                                        ${{ number_format($branch->collected_fines, 2) }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-center">
                                    <span class="inline-flex items-center justify-center w-16 h-10 rounded-full bg-blue-100 text-blue-800 font-bold text-sm">
                                        {{ number_format($branch->utilization_rate, 1) }}%
                                    </span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-center">
                                    <span class="inline-flex items-center justify-center w-16 h-10 rounded-full {{ $branch->fine_collection_rate >= 80 ? 'bg-green-100 text-green-800' : ($branch->fine_collection_rate >= 50 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }} font-bold text-sm">
                                        {{ number_format($branch->fine_collection_rate, 1) }}%
                                    </span>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="px-6 py-8 text-center text-gray-500">
                                No branch statistics available for the selected period.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Daily Statistics Table --}}
    <div class="bg-white rounded-xl shadow-sm border border-amber-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-amber-200 bg-amber-50">
            <h2 class="text-lg font-semibold text-gray-900 font-serif">Daily Usage Statistics</h2>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-amber-50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-amber-800 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-amber-800 uppercase tracking-wider">Branch</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-amber-800 uppercase tracking-wider">Borrows</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-amber-800 uppercase tracking-wider">Returns</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-amber-800 uppercase tracking-wider">Reservations</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-amber-800 uppercase tracking-wider">New Members</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-amber-800 uppercase tracking-wider">Active Members</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-amber-800 uppercase tracking-wider">Fines Generated</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-amber-800 uppercase tracking-wider">Fines Collected</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-amber-800 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-amber-100">
                    @forelse($statistics as $stat)
                        <tr class="hover:bg-amber-50 transition-colors duration-150">
                            <td class="px-6 py-4">
                                <div class="text-sm font-semibold text-gray-900">
                                    {{ $stat->stat_date->format('M d, Y') }}
                                </div>
                                <div class="text-xs text-gray-500">
                                    {{ $stat->stat_date->format('l') }}
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center space-x-3">
                                    <div class="flex-shrink-0">
                                        <div class="w-8 h-8 bg-amber-100 rounded-full flex items-center justify-center">
                                            <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                            </svg>
                                        </div>
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold text-gray-900">{{ $stat->branch->name }}</p>
                                        <p class="text-xs text-gray-500">{{ $stat->branch->code }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-center">
                                    <span class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-blue-100 text-blue-800 font-bold">
                                        {{ $stat->total_borrows }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-center">
                                    <span class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-green-100 text-green-800 font-bold">
                                        {{ $stat->total_returns }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-center">
                                    <span class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-purple-100 text-purple-800 font-bold">
                                        {{ $stat->total_reservations }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-center">
                                    <span class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-indigo-100 text-indigo-800 font-bold">
                                        {{ $stat->new_members }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-center">
                                    <span class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-amber-100 text-amber-800 font-bold">
                                        {{ $stat->active_members }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-center">
                                    <span class="inline-flex items-center justify-center w-14 h-10 rounded-full bg-red-100 text-red-800 font-bold">
                                        ${{ number_format($stat->total_fines, 2) }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-center">
                                    <span class="inline-flex items-center justify-center w-14 h-10 rounded-full bg-green-100 text-green-800 font-bold">
                                        ${{ number_format($stat->collected_fines, 2) }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center space-x-2">
                                    <button onclick="viewDailyDetails({{ $stat->id }})"
                                        class="inline-flex items-center px-3 py-2 border border-blue-300 shadow-sm text-sm leading-4 font-medium rounded-md text-blue-700 bg-white hover:bg-blue-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                        View
                                    </button>
                                    <button onclick="exportDailyReport({{ $stat->id }})"
                                        class="inline-flex items-center px-3 py-2 border border-green-300 shadow-sm text-sm leading-4 font-medium rounded-md text-green-700 bg-white hover:bg-green-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                        Export
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center justify-center text-gray-500">
                                    <svg class="w-16 h-16 mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    <h3 class="text-lg font-medium text-gray-900 mb-2">No usage statistics found</h3>
                                    <p class="text-gray-600 mb-4">Select a different date range or check back later.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($statistics->hasPages())
        <div class="px-6 py-4 border-t border-amber-200 bg-amber-50">
            {{ $statistics->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Daily Details Modal -->
<div id="detailsModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 py-6">
        <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity" onclick="closeDetailsModal()"></div>

        <div class="relative bg-white rounded-2xl shadow-2xl max-w-4xl w-full max-h-[90vh] overflow-hidden transform transition-all">
            <!-- Modal Header -->
            <div class="bg-gradient-to-r from-amber-600 to-amber-700 px-6 py-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="p-2 bg-white bg-opacity-20 rounded-lg">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-white font-serif">Daily Statistics Details</h3>
                            <p id="modalDate" class="text-amber-100 text-sm">Loading...</p>
                        </div>
                    </div>
                    <button onclick="closeDetailsModal()"
                            class="p-2 hover:bg-white hover:bg-opacity-20 rounded-lg transition-colors">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Modal Body -->
            <div class="p-6 overflow-y-auto max-h-[calc(90vh-80px)]">
                <div id="modalContent" class="space-y-6">
                    <!-- Content will be loaded via AJAX -->
                    <div class="flex justify-center py-12">
                        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-amber-600"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Update Date Range
    function updateDateRange() {
        const startDate = document.getElementById('startDate').value;
        const endDate = document.getElementById('endDate').value;

        if (!startDate || !endDate) {
            alert('Please select both start and end dates.');
            return;
        }

        if (new Date(startDate) > new Date(endDate)) {
            alert('Start date must be before end date.');
            return;
        }

        window.location.href = `?start_date=${startDate}&end_date=${endDate}`;
    }

    // Generate Monthly Report
    function generateMonthlyReport() {
        const today = new Date();
        const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
        const lastDay = new Date(today.getFullYear(), today.getMonth() + 1, 0);

        const startDate = firstDay.toISOString().split('T')[0];
        const endDate = lastDay.toISOString().split('T')[0];

        window.location.href = `/usage-statistics/export/monthly?start_date=${startDate}&end_date=${endDate}`;
    }

    // Generate Annual Report
    function generateAnnualReport() {
        const today = new Date();
        const firstDay = new Date(today.getFullYear(), 0, 1);
        const lastDay = new Date(today.getFullYear(), 11, 31);

        const startDate = firstDay.toISOString().split('T')[0];
        const endDate = lastDay.toISOString().split('T')[0];

        window.location.href = `/usage-statistics/export/annual?start_date=${startDate}&end_date=${endDate}`;
    }

    // View Daily Details
    function viewDailyDetails(statId) {
        fetch(`/usage-statistics/${statId}/details`)
            .then(response => response.json())
            .then(data => {
                document.getElementById('modalDate').textContent = `${data.stat_date} - ${data.branch_name}`;
                document.getElementById('modalContent').innerHTML = `
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="bg-blue-50 rounded-xl p-6">
                            <h4 class="text-lg font-semibold text-blue-900 mb-4">Transaction Summary</h4>
                            <div class="space-y-3">
                                <div class="flex justify-between items-center">
                                    <span class="text-gray-600">Total Borrows:</span>
                                    <span class="font-bold text-blue-700">${data.total_borrows}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-gray-600">Total Returns:</span>
                                    <span class="font-bold text-green-700">${data.total_returns}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-gray-600">Total Reservations:</span>
                                    <span class="font-bold text-purple-700">${data.total_reservations}</span>
                                </div>
                            </div>
                        </div>

                        <div class="bg-green-50 rounded-xl p-6">
                            <h4 class="text-lg font-semibold text-green-900 mb-4">Member Activity</h4>
                            <div class="space-y-3">
                                <div class="flex justify-between items-center">
                                    <span class="text-gray-600">New Members:</span>
                                    <span class="font-bold text-indigo-700">${data.new_members}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-gray-600">Active Members:</span>
                                    <span class="font-bold text-amber-700">${data.active_members}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-gray-600">Utilization Rate:</span>
                                    <span class="font-bold text-blue-700">${data.utilization_rate}%</span>
                                </div>
                            </div>
                        </div>

                        <div class="bg-red-50 rounded-xl p-6">
                            <h4 class="text-lg font-semibold text-red-900 mb-4">Financial Summary</h4>
                            <div class="space-y-3">
                                <div class="flex justify-between items-center">
                                    <span class="text-gray-600">Total Fines Generated:</span>
                                    <span class="font-bold text-red-700">$${data.total_fines}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-gray-600">Fines Collected:</span>
                                    <span class="font-bold text-green-700">$${data.collected_fines}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-gray-600">Collection Rate:</span>
                                    <span class="font-bold ${data.fine_collection_rate >= 80 ? 'text-green-700' : (data.fine_collection_rate >= 50 ? 'text-yellow-700' : 'text-red-700')}">${data.fine_collection_rate}%</span>
                                </div>
                            </div>
                        </div>

                        <div class="bg-amber-50 rounded-xl p-6">
                            <h4 class="text-lg font-semibold text-amber-900 mb-4">Performance Metrics</h4>
                            <div class="space-y-3">
                                <div class="flex justify-between items-center">
                                    <span class="text-gray-600">Transactions per Active Member:</span>
                                    <span class="font-bold text-blue-700">${data.tpm}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-gray-600">Reservation Rate:</span>
                                    <span class="font-bold text-purple-700">${data.reservation_rate}%</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-gray-600">Average Fine Amount:</span>
                                    <span class="font-bold text-red-700">$${data.avg_fine}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 pt-6 border-t border-gray-200">
                        <div class="flex justify-between items-center">
                            <div>
                                <h4 class="text-lg font-semibold text-gray-900">Additional Information</h4>
                                <p class="text-sm text-gray-600 mt-1">Last updated: ${data.updated_at}</p>
                            </div>
                            <button onclick="exportDailyReport(${statId})"
                                class="bg-amber-600 hover:bg-amber-700 text-white px-4 py-2 rounded-lg font-medium transition-colors flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                Export Report
                            </button>
                        </div>
                    </div>
                `;
                openDetailsModal();
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('modalContent').innerHTML = `
                    <div class="text-center py-12">
                        <svg class="w-16 h-16 text-red-500 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Error Loading Details</h3>
                        <p class="text-gray-600">Unable to load statistics details. Please try again.</p>
                    </div>
                `;
                openDetailsModal();
            });
    }

    // Export Daily Report
    function exportDailyReport(statId) {
        window.location.href = `/usage-statistics/${statId}/export`;
    }

    // Modal Functions
    function openDetailsModal() {
        document.getElementById('detailsModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeDetailsModal() {
        document.getElementById('detailsModal').classList.add('hidden');
        document.body.style.overflow = 'auto';
    }

    // Set default dates to last 30 days if not set
    document.addEventListener('DOMContentLoaded', function() {
        const today = new Date();
        const thirtyDaysAgo = new Date();
        thirtyDaysAgo.setDate(today.getDate() - 30);

        const startDateInput = document.getElementById('startDate');
        const endDateInput = document.getElementById('endDate');

        if (!startDateInput.value) {
            startDateInput.value = thirtyDaysAgo.toISOString().split('T')[0];
        }

        if (!endDateInput.value) {
            endDateInput.value = today.toISOString().split('T')[0];
        }

        // Close modals on Escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeDetailsModal();
            }
        });
    });
</script>
@endsection
