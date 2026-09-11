@extends('ktvtc.finance.layouts.app')

@section('title', 'Create Fee Structure: ' . $course->name)
@section('subtitle', 'Set the fee for a specific term')

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
        <a href="{{ route('finance.fee-structure.course-terms', $course) }}" class="ml-1 text-sm font-medium text-gray-500 hover:text-primary md:ml-2">
            {{ $course->name }}
        </a>
    </div>
</li>
<li aria-current="page">
    <div class="flex items-center">
        <i class="fas fa-angle-right text-gray-400"></i>
        <span class="ml-1 text-sm font-medium text-gray-800 md:ml-2">Create</span>
    </div>
</li>
@endsection

@section('header-actions')
<a href="{{ route('finance.fee-structure.course-terms', $course) }}"
   class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg font-medium transition-colors">
    <i class="fas fa-arrow-left mr-1"></i> Back
</a>
@endsection

@section('content')
<div class="max-w-4xl mx-auto">
    <form action="{{ route('finance.fee-structure.store') }}" method="POST">
        @csrf
        <input type="hidden" name="course_id" value="{{ $course->id }}">

        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-primary/5 to-transparent">
                <h2 class="text-lg font-semibold text-gray-800">
                    New Fee Structure for {{ $course->name }}
                </h2>
                <p class="text-sm text-gray-500 mt-1">Enter the fee details for the selected term</p>
            </div>

            <div class="p-6 space-y-6">

                {{-- Term selection --}}
                <div>
                    <h4 class="text-sm font-bold text-gray-700 uppercase tracking-wide mb-3">
                        <i class="fas fa-calendar mr-1 text-primary"></i> Academic Term
                    </h4>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">
                                Term <span class="text-red-500">*</span>
                            </label>
                            <select name="term_id" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                                <option value="">Select a term</option>
                                @foreach($terms as $term)
                                    <option value="{{ $term->id }}" {{ (old('term_id', $termId) == $term->id) ? 'selected' : '' }}>
                                        {{ $term->name }} ({{ $term->academic_year }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Campus</label>
                            <select name="campus_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                                <option value="">All Campuses</option>
                                @foreach($campuses as $campus)
                                    <option value="{{ $campus->id }}" {{ old('campus_id') == $campus->id ? 'selected' : '' }}>
                                        {{ $campus->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                {{-- Fee items --}}
                <div>
                    <h4 class="text-sm font-bold text-gray-700 uppercase tracking-wide mb-3">
                        <i class="fas fa-list-ul mr-1 text-primary"></i> Fee Breakdown
                    </h4>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @php
                            $feeFields = [
                                'tuition_per_month' => ['Tuition per Month', true],
                                'registration_fee' => ['Registration Fee', false],
                                'caution_money' => ['Caution Money', false],
                                'student_id_fee' => ['Student ID Fee', false],
                                'library_fee' => ['Library Fee', false],
                                'medical_fee' => ['Medical Fee', false],
                                'sports_fee' => ['Sports Fee', false],
                                'activity_fee' => ['Activity Fee', false],
                                'workshop_levy' => ['Workshop Levy', false],
                                'practical_materials' => ['Practical Materials', false],
                                'tool_kit_deposit' => ['Tool Kit Deposit', false],
                                'protective_clothing' => ['Protective Clothing', false],
                                'industrial_attachment_fee' => ['Industrial Attachment Fee', false],
                                'cdacc_registration_fee' => ['CDACC Registration Fee', false],
                                'cdacc_examination_fee' => ['CDACC Examination Fee', false],
                                'cdacc_certification_fee' => ['CDACC Certification Fee', false],
                                'tvet_authority_levy' => ['TVET Authority Levy', false],
                                'trade_test_fee' => ['Trade Test Fee', false],
                            ];
                        @endphp

                        @foreach($feeFields as $field => $config)
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">
                                    {{ $config[0] }} @if($config[1]) <span class="text-red-500">*</span> @endif
                                </label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-500 text-sm">KES</span>
                                    <input type="number" name="{{ $field }}" step="0.01" min="0"
                                           value="{{ old($field, 0) }}"
                                           {{ $config[1] ? 'required' : '' }}
                                           class="w-full pl-12 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary text-sm">
                                </div>
                            </div>
                        @endforeach

                        <div class="md:col-span-2">
                            <label class="block text-sm font-semibold text-gray-700 mb-1">
                                Total Course Months <span class="text-red-500">*</span>
                            </label>
                            <input type="number" name="total_course_months" required min="1"
                                   value="{{ old('total_course_months', 9) }}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                            <p class="text-xs text-gray-500 mt-1">Number of months the course runs for</p>
                        </div>
                    </div>
                </div>

                {{-- Government sponsorship --}}
                <div>
                    <h4 class="text-sm font-bold text-gray-700 uppercase tracking-wide mb-3">
                        <i class="fas fa-university mr-1 text-primary"></i> Government Sponsorship
                    </h4>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <label class="flex items-center p-3 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50">
                            <input type="checkbox" name="has_government_sponsorship" value="1"
                                   {{ old('has_government_sponsorship') ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-primary focus:ring-primary">
                            <span class="ml-2 text-sm font-medium text-gray-700">This course has government sponsorship</span>
                        </label>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Subsidy Amount (KES)</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-500 text-sm">KES</span>
                                <input type="number" name="government_subsidy_amount" step="0.01" min="0"
                                       value="{{ old('government_subsidy_amount', 0) }}"
                                       class="w-full pl-12 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary text-sm">
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-end gap-3">
                <a href="{{ route('finance.fee-structure.course-terms', $course) }}"
                   class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-100 transition">
                    Cancel
                </a>
                <button type="submit"
                        class="px-6 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg transition flex items-center">
                    <i class="fas fa-save mr-2"></i> Create Fee Structure
                </button>
            </div>
        </div>
    </form>
</div>
@endsection