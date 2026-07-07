@extends('ktvtc.finance.layouts.app')

@section('title', 'Student Report')
@section('subtitle', 'All students with card details')

@section('breadcrumb')
<li>
    <span class="mx-2">/</span>
    <a href="{{ route('finance.card-reports.index') }}" class="text-gray-600 hover:text-primary">Card Reports</a>
</li>
<li>
    <span class="mx-2">/</span>
    <span class="text-gray-400">Students</span>
</li>
@endsection

@section('header-actions')
<div class="flex flex-wrap gap-2">
    <button onclick="window.print()" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded-lg font-semibold flex items-center text-sm transition">
        <i class="fas fa-print mr-2"></i> Print
    </button>
    <a href="{{ route('finance.card-reports.export') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-3 py-2 rounded-lg font-semibold flex items-center text-sm transition">
        <i class="fas fa-file-export mr-2"></i> Export
    </a>
</div>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Stats -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
        <div class="finance-card p-3">
            <p class="text-xs text-gray-500">Total Students</p>
            <p class="text-xl font-bold text-gray-800">{{ number_format($studentData->count()) }}</p>
        </div>
        <div class="finance-card p-3 border-l-4 border-green-500">
            <p class="text-xs text-gray-500">With Cards</p>
            <p class="text-xl font-bold text-green-600">{{ number_format($studentData->where('card', '!=', null)->count()) }}</p>
        </div>
        <div class="finance-card p-3 border-l-4 border-yellow-500">
            <p class="text-xs text-gray-500">With Balance</p>
            <p class="text-xl font-bold text-yellow-600">{{ number_format($studentData->where('balance', '>', 0)->count()) }}</p>
        </div>
        <div class="finance-card p-3 border-l-4 border-blue-500">
            <p class="text-xs text-gray-500">Total Funded</p>
            <p class="text-xl font-bold text-blue-600">KES {{ number_format($studentData->sum('total_funded'), 2) }}</p>
        </div>
    </div>

    <!-- Table -->
    <div class="finance-card p-3 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50">
                        <th class="px-3 py-2 text-left font-semibold text-gray-600">#</th>
                        <th class="px-3 py-2 text-left font-semibold text-gray-600">Student</th>
                        <th class="px-3 py-2 text-left font-semibold text-gray-600">Admission</th>
                        <th class="px-3 py-2 text-left font-semibold text-gray-600">Class</th>
                        <th class="px-3 py-2 text-left font-semibold text-gray-600">Card</th>
                        <th class="px-3 py-2 text-right font-semibold text-gray-600">Balance</th>
                        <th class="px-3 py-2 text-right font-semibold text-gray-600">Total Spent</th>
                        <th class="px-3 py-2 text-right font-semibold text-gray-600">Total Funded</th>
                        <th class="px-3 py-2 text-left font-semibold text-gray-600">Last Used</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($studentData ?? [] as $data)
                        <tr class="hover:bg-gray-50">
                            <td class="px-3 py-2 text-center">{{ $loop->iteration }}</td>
                            <td class="px-3 py-2">
                                <span class="font-medium">{{ $data['student']->full_name ?? 'N/A' }}</span>
                            </td>
                            <td class="px-3 py-2 text-sm">{{ $data['student']->admission_number ?? 'N/A' }}</td>
                            <td class="px-3 py-2 text-sm">{{ $data['student']->class ?? 'N/A' }}</td>
                            <td class="px-3 py-2 font-mono text-xs">
                                @if($data['card'])
                                    {{ $data['card']->card_number }}
                                @else
                                    <span class="text-gray-400">No Card</span>
                                @endif
                            </td>
                            <td class="px-3 py-2 text-right font-bold text-sm
                                @if($data['balance'] > 500) text-green-600
                                @elseif($data['balance'] > 100) text-yellow-600
                                @elseif($data['balance'] > 0) text-orange-600
                                @else text-red-600 @endif">
                                KES {{ number_format($data['balance'] ?? 0, 2) }}
                            </td>
                            <td class="px-3 py-2 text-right">KES {{ number_format($data['total_spent'] ?? 0, 2) }}</td>
                            <td class="px-3 py-2 text-right">KES {{ number_format($data['total_funded'] ?? 0, 2) }}</td>
                            <td class="px-3 py-2 text-sm">{{ $data['last_used'] ? $data['last_used']->format('d M Y') : 'Never' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-3 py-8 text-center text-gray-500">No students found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
