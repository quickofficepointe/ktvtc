@extends('ktvtc.library.layout.librarylayout')

@section('content')
<div class="container mx-auto px-4 py-8">
    {{-- Header Section --}}
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 font-serif">Book Popularity Report</h1>
            <p class="text-gray-600 mt-2">Comprehensive analysis of book popularity metrics</p>
        </div>
        <div class="flex space-x-3">
            <a href="{{ route('book-popularities.index') }}"
                class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-3 rounded-lg font-medium transition-colors flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Back to List
            </a>
            <button onclick="window.print()"
                class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-medium transition-all duration-200 transform hover:scale-105 shadow-lg hover:shadow-xl flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                Print Report
            </button>
        </div>
    </div>

    {{-- Report Filters --}}
    <div class="bg-white rounded-xl shadow-sm border border-amber-200 p-6 mb-8">
        <div class="flex flex-col md:flex-row md:items-center justify-between space-y-4 md:space-y-0">
            <div>
                <h3 class="text-lg font-semibold text-gray-900">Report Filters</h3>
                <p class="text-sm text-gray-600">Select timeframe for analysis</p>
            </div>
            <div class="flex space-x-4">
                <a href="{{ route('book-popularities.report') }}?timeframe=week"
                    class="px-4 py-2 rounded-lg border {{ $stats['timeframe'] == 'week' ? 'bg-amber-100 border-amber-500 text-amber-700' : 'border-gray-300 text-gray-700 hover:bg-gray-50' }} transition-colors">
                    Last 7 Days
                </a>
                <a href="{{ route('book-popularities.report') }}?timeframe=month"
                    class="px-4 py-2 rounded-lg border {{ $stats['timeframe'] == 'month' ? 'bg-amber-100 border-amber-500 text-amber-700' : 'border-gray-300 text-gray-700 hover:bg-gray-50' }} transition-colors">
                    Last 30 Days
                </a>
                <a href="{{ route('book-popularities.report') }}"
                    class="px-4 py-2 rounded-lg border {{ $stats['timeframe'] == 'all' ? 'bg-amber-100 border-amber-500 text-amber-700' : 'border-gray-300 text-gray-700 hover:bg-gray-50' }} transition-colors">
                    All Time
                </a>
            </div>
        </div>
    </div>

    {{-- Summary Stats --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-xl shadow-sm border border-blue-200 p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-lg bg-blue-100 text-blue-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Books Analyzed</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['total_books'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-green-200 p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-lg bg-green-100 text-green-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Avg Borrows</p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['avg_borrow_count'], 1) }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-purple-200 p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-lg bg-purple-100 text-purple-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Avg Views</p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['avg_view_count'], 1) }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-amber-200 p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-lg bg-amber-100 text-amber-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Avg Reservations</p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['avg_reservation_count'], 1) }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Top 10 Most Popular Books --}}
    <div class="bg-white rounded-xl shadow-sm border border-amber-200 overflow-hidden mb-8">
        <div class="px-6 py-4 border-b border-amber-200 bg-amber-50">
            <h2 class="text-lg font-semibold text-gray-900 font-serif">Top 10 Most Popular Books</h2>
            <p class="text-sm text-gray-600 mt-1">Based on overall popularity score</p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-amber-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-amber-800 uppercase tracking-wider">Rank</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-amber-800 uppercase tracking-wider">Book Title</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-amber-800 uppercase tracking-wider">Score</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-amber-800 uppercase tracking-wider">Borrows</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-amber-800 uppercase tracking-wider">Views</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-amber-800 uppercase tracking-wider">Reservations</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-amber-100">
                    @foreach($reportData['top_10_popular'] as $index => $popularity)
                    <tr class="hover:bg-amber-50 transition-colors">
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                <div class="w-8 h-8 flex items-center justify-center rounded-full
                                    @if($index === 0) bg-yellow-100 text-yellow-800
                                    @elseif($index === 1) bg-gray-100 text-gray-800
                                    @elseif($index === 2) bg-amber-100 text-amber-800
                                    @else bg-blue-50 text-blue-800 @endif font-bold">
                                    {{ $index + 1 }}
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                @if($popularity->book->cover_image)
                                <img src="{{ asset('storage/' . $popularity->book->cover_image) }}"
                                     alt="{{ $popularity->book->title }}"
                                     class="w-10 h-14 object-cover rounded mr-3 border border-amber-200">
                                @endif
                                <div>
                                    <div class="font-medium text-gray-900">{{ $popularity->book->title }}</div>
                                    <div class="text-sm text-gray-500">{{ $popularity->book->category->name }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-green-100 text-green-800">
                                {{ number_format($popularity->popularity_score, 2) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="font-semibold text-gray-900">{{ $popularity->borrow_count }}</span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="font-semibold text-gray-900">{{ $popularity->view_count }}</span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="font-semibold text-gray-900">{{ $popularity->reservation_count }}</span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Most Borrowed Books --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
        <div class="bg-white rounded-xl shadow-sm border border-blue-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-blue-200 bg-blue-50">
                <h2 class="text-lg font-semibold text-gray-900 font-serif">Most Borrowed Books</h2>
                <p class="text-sm text-gray-600 mt-1">Top 10 by borrow count</p>
            </div>

            <div class="p-6">
                <ul class="space-y-4">
                    @foreach($reportData['most_borrowed'] as $index => $popularity)
                    <li class="flex items-center justify-between p-3 hover:bg-blue-50 rounded-lg transition-colors">
                        <div class="flex items-center">
                            <span class="w-8 h-8 flex items-center justify-center rounded-full bg-blue-100 text-blue-800 font-bold mr-3">
                                {{ $index + 1 }}
                            </span>
                            <div>
                                <div class="font-medium text-gray-900">{{ $popularity->book->title }}</div>
                                <div class="text-sm text-gray-500">{{ $popularity->book->category->name }}</div>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="font-bold text-blue-700">{{ $popularity->borrow_count }}</div>
                            <div class="text-xs text-gray-500">borrows</div>
                        </div>
                    </li>
                    @endforeach
                </ul>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-purple-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-purple-200 bg-purple-50">
                <h2 class="text-lg font-semibold text-gray-900 font-serif">Most Viewed Books</h2>
                <p class="text-sm text-gray-600 mt-1">Top 10 by view count</p>
            </div>

            <div class="p-6">
                <ul class="space-y-4">
                    @foreach($reportData['most_viewed'] as $index => $popularity)
                    <li class="flex items-center justify-between p-3 hover:bg-purple-50 rounded-lg transition-colors">
                        <div class="flex items-center">
                            <span class="w-8 h-8 flex items-center justify-center rounded-full bg-purple-100 text-purple-800 font-bold mr-3">
                                {{ $index + 1 }}
                            </span>
                            <div>
                                <div class="font-medium text-gray-900">{{ $popularity->book->title }}</div>
                                <div class="text-sm text-gray-500">{{ $popularity->book->category->name }}</div>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="font-bold text-purple-700">{{ $popularity->view_count }}</div>
                            <div class="text-xs text-gray-500">views</div>
                        </div>
                    </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>

    {{-- Most Reserved Books & Lowest Popularity --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <div class="bg-white rounded-xl shadow-sm border border-green-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-green-200 bg-green-50">
                <h2 class="text-lg font-semibold text-gray-900 font-serif">Most Reserved Books</h2>
                <p class="text-sm text-gray-600 mt-1">Top 10 by reservation count</p>
            </div>

            <div class="p-6">
                <ul class="space-y-4">
                    @foreach($reportData['most_reserved'] as $index => $popularity)
                    <li class="flex items-center justify-between p-3 hover:bg-green-50 rounded-lg transition-colors">
                        <div class="flex items-center">
                            <span class="w-8 h-8 flex items-center justify-center rounded-full bg-green-100 text-green-800 font-bold mr-3">
                                {{ $index + 1 }}
                            </span>
                            <div>
                                <div class="font-medium text-gray-900">{{ $popularity->book->title }}</div>
                                <div class="text-sm text-gray-500">{{ $popularity->book->category->name }}</div>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="font-bold text-green-700">{{ $popularity->reservation_count }}</div>
                            <div class="text-xs text-gray-500">reservations</div>
                        </div>
                    </li>
                    @endforeach
                </ul>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-red-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-red-200 bg-red-50">
                <h2 class="text-lg font-semibold text-gray-900 font-serif">Lowest Popularity Books</h2>
                <p class="text-sm text-gray-600 mt-1">Books needing attention</p>
            </div>

            <div class="p-6">
                <ul class="space-y-4">
                    @foreach($reportData['lowest_10_popular'] as $index => $popularity)
                    <li class="flex items-center justify-between p-3 hover:bg-red-50 rounded-lg transition-colors">
                        <div class="flex items-center">
                            <span class="w-8 h-8 flex items-center justify-center rounded-full bg-red-100 text-red-800 font-bold mr-3">
                                {{ $index + 1 }}
                            </span>
                            <div>
                                <div class="font-medium text-gray-900">{{ $popularity->book->title }}</div>
                                <div class="text-sm text-gray-500">{{ $popularity->book->category->name }}</div>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="font-bold text-red-700">{{ number_format($popularity->popularity_score, 2) }}</div>
                            <div class="text-xs text-gray-500">score</div>
                        </div>
                    </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>

    {{-- Report Summary --}}
    <div class="mt-8 p-6 bg-gradient-to-r from-amber-50 to-amber-100 rounded-xl border border-amber-300">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Report Summary</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <h4 class="font-medium text-gray-700 mb-2">Key Insights:</h4>
                <ul class="list-disc pl-5 space-y-2 text-gray-600">
                    <li>Total books analyzed: {{ $stats['total_books'] }}</li>
                    <li>Average borrow count: {{ number_format($stats['avg_borrow_count'], 1) }}</li>
                    <li>Average view count: {{ number_format($stats['avg_view_count'], 1) }}</li>
                    <li>Average reservation count: {{ number_format($stats['avg_reservation_count'], 1) }}</li>
                </ul>
            </div>
            <div>
                <h4 class="font-medium text-gray-700 mb-2">Recommendations:</h4>
                <ul class="list-disc pl-5 space-y-2 text-gray-600">
                    <li>Consider promoting low-popularity books</li>
                    <li>Monitor borrow-to-view ratio for popular books</li>
                    <li>Review reservation patterns for collection planning</li>
                    <li>Use this data for book acquisition decisions</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<style>
    @media print {
        button, a, .no-print {
            display: none !important;
        }

        .container {
            padding: 0 !important;
        }

        body {
            font-size: 12pt;
        }

        table {
            page-break-inside: auto;
        }

        tr {
            page-break-inside: avoid;
            page-break-after: auto;
        }

        thead {
            display: table-header-group;
        }
    }
</style>
@endsection
