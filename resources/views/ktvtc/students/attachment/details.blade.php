@extends('ktvtc.students.layout.studentlayout')

@section('title', 'Attachment Details')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <a href="{{ route('student.attachment.index') }}"
       class="inline-flex items-center text-sm text-gray-600 hover:text-primary">
        <i class="fas fa-arrow-left mr-2"></i> Back to My Attachment
    </a>

    @if(session('success'))
        <div class="p-4 rounded-lg bg-green-50 border border-green-200 text-green-800 text-sm">
            <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="p-4 rounded-lg bg-red-50 border border-red-200 text-red-800 text-sm">
            <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
        </div>
    @endif
    @if(session('info'))
        <div class="p-4 rounded-lg bg-blue-50 border border-blue-200 text-blue-800 text-sm">
            <i class="fas fa-info-circle mr-2"></i>{{ session('info') }}
        </div>
    @endif

    {{-- ═══════════ CASE 1: No attachment yet, but an approved application exists ═══════════ --}}
    @if(!$attachment && $approvedApplication)
        <div class="bg-gradient-to-r from-green-50 to-white border border-green-200 rounded-xl p-6">
            <div class="flex items-start gap-4">
                <div class="w-12 h-12 rounded-full bg-green-100 flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-check-circle text-green-600 text-xl"></i>
                </div>
                <div class="flex-1">
                    <h3 class="font-bold text-lg text-green-800">
                        You're approved for
                        {{ $approvedApplication->opportunity->company->name ?? 'this company' }}
                    </h3>
                    <p class="text-sm text-green-700 mt-1">
                        Fill in your attachment details so the ILO office can confirm your placement.
                    </p>
                    <a href="{{ route('student.attachment.details.create', ['application_id' => $approvedApplication->id]) }}"
                       class="mt-4 inline-flex items-center px-5 py-2.5 bg-primary hover:bg-primary-dark text-white rounded-lg font-semibold">
                        <i class="fas fa-edit mr-2"></i> Fill In Attachment Details
                    </a>
                </div>
            </div>
        </div>
    @endif

    {{-- ═══════════ CASE 2: Attachment exists ═══════════ --}}
    @if($attachment)

        @php
            $colorMap = [
                'draft'          => 'gray',
                'submitted'      => 'blue',
                'needs_revision' => 'amber',
                'active'         => 'green',
                'completed'      => 'purple',
                'graded'         => 'emerald',
                'rejected'       => 'red',
            ];
            $color = $colorMap[$attachment->status] ?? 'gray';
        @endphp

        {{-- Status banner --}}
        <div class="bg-{{ $color }}-50 border border-{{ $color }}-200 rounded-xl p-5">
            <div class="flex items-start gap-3">
                <i class="fas fa-info-circle text-{{ $color }}-600 text-lg mt-0.5"></i>
                <div class="flex-1">
                    <p class="font-semibold text-{{ $color }}-800">{{ $attachment->status_label }}</p>

                    @if($attachment->review_remarks)
                        <p class="text-sm text-{{ $color }}-700 mt-1">
                            <strong>ILO:</strong> {{ $attachment->review_remarks }}
                        </p>
                    @endif

                    @if($attachment->needsRevision())
                        <a href="{{ route('student.attachment.details.edit', $attachment) }}"
                           class="mt-3 inline-block px-4 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg text-sm font-semibold">
                            <i class="fas fa-edit mr-2"></i> Edit and Resubmit
                        </a>
                    @endif
                </div>
            </div>
        </div>

        {{-- Placement summary --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <div class="flex items-start justify-between gap-4 mb-4">
                <h3 class="font-bold text-lg text-gray-800 flex items-center">
                    <i class="fas fa-building text-primary mr-2"></i>
                    {{ $attachment->company_name }}
                    @if($attachment->company_registered)
                        <span class="ml-2 text-xs px-2 py-0.5 bg-primary-light text-primary rounded-full">
                            Registered
                        </span>
                    @else
                        <span class="ml-2 text-xs px-2 py-0.5 bg-amber-50 text-amber-700 rounded-full">
                            Self-found
                        </span>
                    @endif
                </h3>
                @if($attachment->canBeEditedByStudent())
                    <a href="{{ route('student.attachment.details.edit', $attachment) }}"
                       class="text-sm text-primary hover:underline">
                        <i class="fas fa-edit mr-1"></i> Edit
                    </a>
                @endif
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-5 text-sm">
                <div>
                    <p class="text-xs text-gray-500">Period</p>
                    <p class="font-medium text-gray-800">
                        {{ $attachment->start_date->format('d M Y') }}
                        → {{ $attachment->end_date->format('d M Y') }}
                    </p>
                    @if($attachment->duration_weeks)
                        <p class="text-xs text-gray-400 mt-0.5">{{ $attachment->duration_weeks }} weeks</p>
                    @endif
                </div>
                <div>
                    <p class="text-xs text-gray-500">Supervisor</p>
                    <p class="font-medium text-gray-800">{{ $attachment->supervisor_name }}</p>
                    @if($attachment->supervisor_phone)
                        <p class="text-xs text-gray-500">{{ $attachment->supervisor_phone }}</p>
                    @endif
                </div>
                <div>
                    <p class="text-xs text-gray-500">Location</p>
                    <p class="font-medium text-gray-800">{{ $attachment->company_address ?? '—' }}</p>
                </div>
            </div>

            {{-- Progress (only when active) --}}
            @if($attachment->isActive())
                <div class="mt-6 pt-5 border-t border-gray-100">
                    <div class="flex justify-between text-xs mb-1">
                        <span class="font-medium text-gray-600">Attachment progress</span>
                        <span class="font-bold">{{ $attachment->progress_percentage }}%</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-3 overflow-hidden">
                        <div class="h-3 rounded-full bg-primary"
                             style="width: {{ $attachment->progress_percentage }}%"></div>
                    </div>
                    <p class="text-xs text-gray-500 mt-2">
                        <i class="fas fa-clock mr-1"></i>
                        {{ $attachment->days_remaining }} days remaining
                    </p>
                </div>
            @endif

            {{-- Documents --}}
            @if($attachment->acceptance_letter_path || $attachment->placement_letter_path)
                <div class="mt-6 pt-5 border-t border-gray-100">
                    <p class="text-xs font-semibold text-gray-600 mb-2">Documents</p>
                    <div class="flex flex-wrap gap-3 text-sm">
                        @if($attachment->acceptance_letter_path)
                            <a href="{{ Storage::disk('public')->url($attachment->acceptance_letter_path) }}"
                               target="_blank"
                               class="inline-flex items-center px-3 py-1.5 bg-gray-100 hover:bg-gray-200 rounded-lg text-gray-700">
                                <i class="fas fa-file-pdf mr-2 text-red-600"></i> Acceptance Letter
                            </a>
                        @endif
                        @if($attachment->placement_letter_path)
                            <a href="{{ Storage::disk('public')->url($attachment->placement_letter_path) }}"
                               target="_blank"
                               class="inline-flex items-center px-3 py-1.5 bg-gray-100 hover:bg-gray-200 rounded-lg text-gray-700">
                                <i class="fas fa-file-pdf mr-2 text-red-600"></i> Placement Letter
                            </a>
                        @endif
                    </div>
                </div>
            @endif

            {{-- Grade (only when graded) --}}
            @if($attachment->isGraded() && $attachment->grade)
                <div class="mt-6 pt-5 border-t border-gray-100">
                    <p class="text-xs font-semibold text-gray-600 mb-2">Final Grade</p>
                    <p class="text-2xl font-bold text-emerald-600">{{ $attachment->grade }}</p>
                    @if($attachment->grader_remarks)
                        <p class="text-sm text-gray-700 mt-2">{{ $attachment->grader_remarks }}</p>
                    @endif
                </div>
            @endif
        </div>

    @endif

    {{-- ═══════════ CASE 3: Nothing at all ═══════════ --}}
    @if(!$attachment && !$approvedApplication)
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-12 text-center">
            <div class="w-20 h-20 bg-amber-50 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-hourglass-half text-amber-600 text-3xl"></i>
            </div>
            <p class="text-lg font-semibold text-gray-700">No attachment details yet</p>
            <p class="text-sm text-gray-500 mt-1 max-w-md mx-auto">
                You can either apply for a school-arranged placement, or declare one you've found yourself.
            </p>
            <div class="mt-6 flex flex-wrap justify-center gap-3">
                <a href="{{ route('student.attachment.opportunities') }}"
                   class="inline-flex items-center px-5 py-2.5 bg-primary hover:bg-primary-dark text-white rounded-lg font-semibold">
                    <i class="fas fa-search mr-2"></i> Browse Opportunities
                </a>
                <a href="{{ route('student.attachment.details.create') }}"
                   class="inline-flex items-center px-5 py-2.5 bg-amber-600 hover:bg-amber-700 text-white rounded-lg font-semibold">
                    <i class="fas fa-edit mr-2"></i> Declare My Attachment
                </a>
            </div>
        </div>
    @endif

</div>
@endsection