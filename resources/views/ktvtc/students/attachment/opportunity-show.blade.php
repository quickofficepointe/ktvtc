@extends('ktvtc.students.layout.studentlayout')

@section('title', 'Opportunity Details')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <a href="{{ route('student.attachment.opportunities') }}"
       class="inline-flex items-center text-sm text-gray-600 hover:text-primary">
        <i class="fas fa-arrow-left mr-2"></i> Back to Opportunities
    </a>

    {{-- Header --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="min-w-0">
                <h1 class="text-2xl font-bold text-gray-800">
                    {{ $opportunity->company->name ?? 'Company' }}
                </h1>
                <p class="text-gray-500 mt-1">
                    {{ $opportunity->course->name ?? '' }}
                    @if($opportunity->course?->code)
                        <span class="font-mono text-xs">({{ $opportunity->course->code }})</span>
                    @endif
                </p>
                @if($opportunity->title)
                    <p class="text-gray-700 mt-2 font-medium">{{ $opportunity->title }}</p>
                @endif
            </div>
            <div class="text-right">
                <p class="text-xs text-gray-500">Slots remaining</p>
                <p class="text-3xl font-bold text-primary">{{ $opportunity->slotsRemaining() }}</p>
                <p class="text-xs text-gray-500">of {{ $opportunity->slots }}</p>
            </div>
        </div>

        {{-- Meta --}}
        <div class="mt-6 pt-6 border-t border-gray-100 grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
            <div>
                <p class="text-xs text-gray-500">Attachment Period</p>
                <p class="font-medium text-gray-800">
                    {{ $opportunity->start_date?->format('d M') ?? '—' }}
                    → {{ $opportunity->end_date?->format('d M Y') ?? '—' }}
                </p>
            </div>
            <div>
                <p class="text-xs text-gray-500">Application Closes</p>
                <p class="font-medium {{ $opportunity->application_closes_at && $opportunity->application_closes_at->isPast() ? 'text-red-600' : 'text-gray-800' }}">
                    {{ $opportunity->application_closes_at?->format('d M Y') ?? 'Open' }}
                </p>
            </div>
            <div>
                <p class="text-xs text-gray-500">Location</p>
                <p class="font-medium text-gray-800">{{ $opportunity->location ?? '—' }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500">Term</p>
                <p class="font-medium text-gray-800">{{ $opportunity->term->name ?? '—' }}</p>
            </div>
        </div>

        @if($opportunity->is_paid || $opportunity->provides_transport)
            <div class="mt-4 flex flex-wrap gap-2">
                @if($opportunity->is_paid)
                    <span class="text-xs px-3 py-1 rounded-full bg-emerald-50 text-emerald-700 font-medium">
                        <i class="fas fa-money-bill mr-1"></i>Paid Attachment
                    </span>
                @endif
                @if($opportunity->provides_transport)
                    <span class="text-xs px-3 py-1 rounded-full bg-blue-50 text-blue-700 font-medium">
                        <i class="fas fa-bus mr-1"></i>Transport Provided
                    </span>
                @endif
            </div>
        @endif
    </div>

    {{-- Description --}}
    @if($opportunity->description || $opportunity->requirements)
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-4">
            @if($opportunity->description)
                <div>
                    <h3 class="font-semibold text-gray-800 mb-2 flex items-center">
                        <i class="fas fa-align-left text-primary mr-2"></i> Description
                    </h3>
                    <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ $opportunity->description }}</p>
                </div>
            @endif
            @if($opportunity->requirements)
                <div class="pt-4 border-t border-gray-100">
                    <h3 class="font-semibold text-gray-800 mb-2 flex items-center">
                        <i class="fas fa-check-circle text-primary mr-2"></i> Requirements
                    </h3>
                    <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ $opportunity->requirements }}</p>
                </div>
            @endif
        </div>
    @endif

    {{-- Company contact --}}
    @if($opportunity->company)
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h3 class="font-semibold text-gray-800 mb-3 flex items-center">
                <i class="fas fa-building text-primary mr-2"></i> About the Company
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                @if($opportunity->company->sector)
                    <div>
                        <p class="text-xs text-gray-500">Sector</p>
                        <p class="font-medium text-gray-800">{{ $opportunity->company->sector }}</p>
                    </div>
                @endif
                @if($opportunity->company->full_address)
                    <div>
                        <p class="text-xs text-gray-500">Address</p>
                        <p class="font-medium text-gray-800">{{ $opportunity->company->full_address }}</p>
                    </div>
                @endif
                @if($opportunity->company->company_phone)
                    <div>
                        <p class="text-xs text-gray-500">Phone</p>
                        <p class="font-medium text-gray-800">{{ $opportunity->company->company_phone }}</p>
                    </div>
                @endif
                @if($opportunity->company->website)
                    <div>
                        <p class="text-xs text-gray-500">Website</p>
                        <a href="{{ $opportunity->company->website }}" target="_blank"
                           class="text-primary hover:underline">{{ $opportunity->company->website }}</a>
                    </div>
                @endif
            </div>
        </div>
    @endif

    {{-- Apply --}}
    @if($alreadyApplied)
        <div class="bg-blue-50 border border-blue-200 rounded-xl p-6">
            <p class="text-blue-800 flex items-center">
                <i class="fas fa-check-circle mr-2"></i>
                You have already applied to this opportunity.
            </p>
            <a href="{{ route('student.attachment.applications') }}"
               class="mt-3 inline-block text-sm text-primary hover:underline font-medium">
                View my applications →
            </a>
        </div>
    @elseif(!$opportunity->isOpen())
        <div class="bg-gray-50 border border-gray-200 rounded-xl p-6">
            <p class="text-gray-700 flex items-center">
                <i class="fas fa-lock mr-2"></i>
                This opportunity is no longer accepting applications.
            </p>
        </div>
    @else
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h3 class="font-semibold text-gray-800 mb-4 flex items-center">
                <i class="fas fa-paper-plane text-primary mr-2"></i> Apply for this Attachment
            </h3>

            <form method="POST" action="{{ route('student.attachment.opportunities.apply', $opportunity) }}"
                  enctype="multipart/form-data">
                @csrf

                <div class="space-y-5">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Curriculum Vitae (CV)
                            <span class="text-xs text-gray-500 font-normal">— PDF/DOC/DOCX, max 5MB</span>
                        </label>
                        <input type="file" name="cv" accept=".pdf,.doc,.docx"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                        @error('cv')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Cover Letter
                            <span class="text-xs text-gray-500 font-normal">— optional</span>
                        </label>
                        <input type="file" name="cover_letter" accept=".pdf,.doc,.docx"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                        @error('cover_letter')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Why do you want this attachment?
                            <span class="text-xs text-gray-500 font-normal">— optional</span>
                        </label>
                        <textarea name="student_notes" rows="4"
                                  placeholder="Briefly describe your interest and what you hope to learn..."
                                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">{{ old('student_notes') }}</textarea>
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <a href="{{ route('student.attachment.opportunities') }}"
                       class="px-5 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                        Cancel
                    </a>
                    <button type="submit"
                            class="px-6 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg font-semibold">
                        <i class="fas fa-paper-plane mr-2"></i> Submit Application
                    </button>
                </div>
            </form>
        </div>
    @endif
</div>
@endsection