@extends('ktvtc.finance.layouts.app')

@section('title', 'Fee Structure Details')
@section('subtitle', $feeStructure->course->name . ' · ' . ($feeStructure->term->name ?? 'Legacy'))

@section('breadcrumb')
<li>
    <div class="flex items-center">
        <i class="fas fa-angle-right text-gray-400"></i>
        <a href="{{ route('finance.fee-structure.index') }}" class="ml-1 text-sm font-medium text-gray-500 hover:text-primary md:ml-2">
            Fee Structure
        </a>
    </div>
</li>
<li>
    <div class="flex items-center">
        <i class="fas fa-angle-right text-gray-400"></i>
        <a href="{{ route('finance.fee-structure.course-terms', $feeStructure->course_id) }}" class="ml-1 text-sm font-medium text-gray-500 hover:text-primary md:ml-2">
            {{ $feeStructure->course->name }}
        </a>
    </div>
</li>
<li aria-current="page">
    <div class="flex items-center">
        <i class="fas fa-angle-right text-gray-400"></i>
        <span class="ml-1 text-sm font-medium text-gray-800 md:ml-2">Details</span>
    </div>
</li>
@endsection

@section('header-actions')
<div class="flex space-x-2">
    <a href="{{ route('finance.fee-structure.course-terms', $feeStructure->course_id) }}"
       class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg font-medium transition-colors">
        <i class="fas fa-arrow-left mr-1"></i> Back
    </a>
    <a href="{{ route('finance.fee-structure.edit', $feeStructure) }}"
       class="px-4 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg font-medium transition-colors">
        <i class="fas fa-edit mr-1"></i> Edit
    </a>
</div>
@endsection

@section('content')
<div class="max-w-4xl mx-auto">

    {{-- Header card --}}
    <div class="bg-white rounded-xl border border-gray-200 p-6 mb-6">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold text-gray-800">{{ $feeStructure->course->name }}</h2>
                <p class="text-sm text-gray-500 mt-1">
                    <span class="font-mono">{{ $feeStructure->course->code ?? 'N/A' }}</span> ·
                    {{ $feeStructure->term->name ?? 'Legacy Fee (no term)' }}
                    @if($feeStructure->campus)
                        · {{ $feeStructure->campus->name }}
                    @endif
                </p>
            </div>
            <div class="text-right">
                @if($feeStructure->is_approved)
                    <span class="text-xs px-3 py-1 bg-green-100 text-green-700 rounded-full font-medium">
                        <i class="fas fa-check-circle mr-1"></i> Approved
                    </span>
                @else
                    <span class="text-xs px-3 py-1 bg-amber-100 text-amber-700 rounded-full font-medium">
                        <i class="fas fa-clock mr-1"></i> Pending Approval
                    </span>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-6">
            <div class="bg-primary/5 rounded-lg p-4">
                <p class="text-xs text-gray-600">Total Course Fee</p>
                <p class="text-2xl font-bold text-primary mt-1">
                    KES {{ number_format($feeStructure->total_course_fee, 2) }}
                </p>
            </div>
            <div class="bg-gray-50 rounded-lg p-4">
                <p class="text-xs text-gray-600">Monthly Total</p>
                <p class="text-xl font-bold text-gray-800 mt-1">
                    KES {{ number_format($feeStructure->monthly_total, 2) }}
                </p>
            </div>
            <div class="bg-gray-50 rounded-lg p-4">
                <p class="text-xs text-gray-600">Duration</p>
                <p class="text-xl font-bold text-gray-800 mt-1">
                    {{ $feeStructure->total_course_months }} months
                </p>
            </div>
        </div>

        @if($feeStructure->has_government_sponsorship)
            <div class="mt-4 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                <p class="text-sm text-blue-800">
                    <i class="fas fa-university mr-2"></i>
                    Government sponsored · Subsidy: KES {{ number_format($feeStructure->government_subsidy_amount, 2) }}
                </p>
            </div>
        @endif
    </div>

    {{-- Fee breakdown --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden mb-6">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                <i class="fas fa-list-ul text-primary mr-2"></i> Fee Breakdown
            </h3>
        </div>
        <div class="p-6">
            <table class="w-full text-sm">
                <tbody class="divide-y divide-gray-100">
                    @foreach($formattedBreakdown as $label => $amount)
                        @if($amount > 0)
                            <tr>
                                <td class="py-2 text-gray-700">{{ $label }}</td>
                                <td class="py-2 text-right font-medium text-gray-900">
                                    KES {{ number_format($amount, 2) }}
                                </td>
                            </tr>
                        @endif
                    @endforeach
                    <tr class="bg-gray-50 font-bold border-t-2 border-gray-300">
                        <td class="py-3 text-gray-800">Total Course Fee</td>
                        <td class="py-3 text-right text-primary text-lg">
                            KES {{ number_format($feeStructure->total_course_fee, 2) }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    {{-- Enrollment count --}}
    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <h3 class="text-sm font-semibold text-gray-700 mb-2">
            <i class="fas fa-users text-primary mr-1"></i> Enrollments Using This Fee
        </h3>
        <p class="text-2xl font-bold text-gray-800">{{ number_format($activeEnrollments) }}</p>
        <p class="text-xs text-gray-500 mt-1">Active enrollments for this course and term</p>
    </div>

</div>
@endsection