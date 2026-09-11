@extends('ktvtc.finance.layouts.app')

@section('title', 'Fee Structure: ' . $course->name)
@section('subtitle', 'Per-term fees for this course')

@section('breadcrumb')
<li>
    <div class="flex items-center">
        <i class="fas fa-angle-right text-gray-400"></i>
        <a href="{{ route('finance.fee-structure.index') }}" class="ml-1 text-sm font-medium text-gray-500 hover:text-primary md:ml-2">
            Fee Structure
        </a>
    </div>
</li>
<li aria-current="page">
    <div class="flex items-center">
        <i class="fas fa-angle-right text-gray-400"></i>
        <span class="ml-1 text-sm font-medium text-gray-800 md:ml-2">{{ $course->name }}</span>
    </div>
</li>
@endsection

@section('header-actions')
<div class="flex space-x-2">
    <a href="{{ route('finance.fee-structure.index') }}"
       class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg font-medium transition-colors">
        <i class="fas fa-arrow-left mr-1"></i> Back
    </a>
    <a href="{{ route('finance.fee-structure.create', $course) }}"
       class="px-4 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg font-medium transition-colors">
        <i class="fas fa-plus mr-1"></i> Add Term Fee
    </a>
</div>
@endsection

@section('content')
<div class="max-w-6xl mx-auto">

    {{-- Course header --}}
    <div class="bg-white rounded-xl border border-gray-200 p-6 mb-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-xl font-bold text-gray-800">{{ $course->name }}</h2>
                <p class="text-sm text-gray-500 mt-1">
                    Code: {{ $course->code ?? 'N/A' }} · Department: {{ $course->department->name ?? 'N/A' }}
                </p>
            </div>
            <div class="flex gap-4">
                <div class="text-center">
                    <p class="text-xs text-gray-500">Terms Priced</p>
                    <p class="text-xl font-bold text-primary">{{ $feeStructures->count() }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Term grid --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach($allTerms as $term)
            @php
                $fs = $termsWithFees[$term->id] ?? null;
            @endphp
            <div class="bg-white rounded-xl border-2 {{ $fs ? 'border-green-200' : 'border-dashed border-gray-300' }} p-5">
                <div class="flex items-start justify-between mb-3">
                    <div>
                        <h3 class="font-bold text-gray-800">{{ $term->name }}</h3>
                        <p class="text-xs text-gray-500">{{ $term->academic_year }}</p>
                    </div>
                    @if($term->is_current)
                        <span class="text-xs px-2 py-0.5 bg-green-100 text-green-700 rounded-full font-medium">
                            <i class="fas fa-star mr-1"></i> Current
                        </span>
                    @endif
                </div>

                @if($fs)
                    <div class="mb-3 pb-3 border-b border-gray-100">
                        <p class="text-xs text-gray-500">Total Fee</p>
                        <p class="text-2xl font-bold text-primary">KES {{ number_format($fs->total_course_fee, 2) }}</p>
                    </div>

                    <div class="text-xs text-gray-500 mb-3 space-y-0.5">
                        <div class="flex justify-between">
                            <span>Tuition/Month</span>
                            <span class="font-medium text-gray-700">KES {{ number_format($fs->tuition_per_month, 2) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Duration</span>
                            <span class="font-medium text-gray-700">{{ $fs->total_course_months }} months</span>
                        </div>
                    </div>

                    <div class="flex gap-2">
                        <a href="{{ route('finance.fee-structure.edit', $fs) }}"
                           class="flex-1 text-center text-xs bg-amber-50 hover:bg-amber-100 text-amber-700 py-2 rounded-lg font-medium transition">
                            <i class="fas fa-edit mr-1"></i> Edit
                        </a>
                        <a href="{{ route('finance.fee-structure.show', $fs) }}"
                           class="flex-1 text-center text-xs bg-gray-100 hover:bg-gray-200 text-gray-700 py-2 rounded-lg font-medium transition">
                            <i class="fas fa-eye mr-1"></i> View
                        </a>
                    </div>
                @else
                    <div class="text-center py-6">
                        <i class="fas fa-plus-circle text-3xl text-gray-300 mb-2"></i>
                        <p class="text-sm text-gray-500 mb-3">No fee set for this term</p>
                        <a href="{{ route('finance.fee-structure.create', ['course' => $course, 'term_id' => $term->id]) }}"
                           class="inline-block text-xs bg-primary hover:bg-primary-dark text-white px-4 py-2 rounded-lg font-medium transition">
                            <i class="fas fa-plus mr-1"></i> Add Fee
                        </a>
                    </div>
                @endif
            </div>
        @endforeach
    </div>

    @if($allTerms->isEmpty())
        <div class="bg-white rounded-xl border border-gray-200 p-12 text-center">
            <i class="fas fa-calendar-times text-5xl text-gray-300 mb-3"></i>
            <p class="text-lg font-medium text-gray-600">No academic terms defined</p>
            <p class="text-sm text-gray-500 mt-1">Create academic terms first before setting per-term fees.</p>
        </div>
    @endif

</div>
@endsection