@extends('ktvtc.ilo.layout.ilolayout')

@section('title', 'Review Attachment')
@section('subtitle', ($attachment->student->full_name ?? '—') . ' · ' . ($attachment->company_name ?? '—'))

@section('header-actions')
<a href="{{ route('ilo.attachments.index') }}"
   class="inline-flex items-center px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 font-medium">
    <i class="fas fa-arrow-left mr-2"></i> Back
</a>
@endsection

@section('content')

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

{{-- ═══════════ STATUS BANNER ═══════════ --}}
<div class="bg-{{ $color }}-50 border border-{{ $color }}-200 rounded-xl p-5 mb-6">
    <div class="flex items-start justify-between gap-4 flex-wrap">
        <div class="flex items-start gap-3">
            <i class="fas fa-info-circle text-{{ $color }}-600 text-lg mt-0.5"></i>
            <div>
                <p class="font-semibold text-{{ $color }}-800">
                    {{ $attachment->status_label ?? ucfirst($attachment->status) }}
                </p>
                @if($attachment->review_remarks)
                    <p class="text-sm text-{{ $color }}-700 mt-1">
                        <strong>Remarks:</strong> {{ $attachment->review_remarks }}
                    </p>
                @endif
                @if($attachment->reviewed_at)
                    <p class="text-xs text-{{ $color }}-600 mt-1">
                        Reviewed {{ $attachment->reviewed_at->format('d M Y H:i') }}
                        @if($attachment->reviewer)
                            by {{ $attachment->reviewer->name }}
                        @endif
                    </p>
                @endif
            </div>
        </div>

        @if(is_null($attachment->company_id))
            <span class="text-xs px-3 py-1 bg-amber-100 text-amber-800 rounded-full font-medium">
                <i class="fas fa-user mr-1"></i> Self-Found Placement
            </span>
        @else
            <span class="text-xs px-3 py-1 bg-primary-light text-primary rounded-full font-medium">
                <i class="fas fa-star mr-1"></i> Registered Company
            </span>
        @endif
    </div>
</div>

{{-- ═══════════ MAIN GRID ═══════════ --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- LEFT: Details --}}
    <div class="lg:col-span-2 space-y-6">

        {{-- STUDENT --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h3 class="font-semibold text-gray-800 mb-4 flex items-center">
                <i class="fas fa-user-graduate text-primary mr-2"></i> Student
            </h3>
            @if($attachment->student)
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    <div>
                        <p class="text-xs text-gray-500">Full Name</p>
                        <p class="font-medium text-gray-800">{{ $attachment->student->full_name }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Student Number</p>
                        <p class="font-mono font-medium text-gray-800">{{ $attachment->student->student_number ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Phone</p>
                        <p class="font-medium text-gray-800">{{ $attachment->student->phone ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Email</p>
                        <p class="font-medium text-gray-800">{{ $attachment->student->email ?? '—' }}</p>
                    </div>
                </div>
                <div class="mt-4 pt-4 border-t border-gray-100">
                    <a href="{{ route('admin.students.show', $attachment->student_id) }}"
                       class="text-sm text-primary hover:underline">
                        View full student profile →
                    </a>
                </div>
            @else
                <p class="text-sm text-gray-500 italic">No student linked.</p>
            @endif
        </div>

        {{-- COMPANY --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h3 class="font-semibold text-gray-800 mb-4 flex items-center">
                <i class="fas fa-building text-primary mr-2"></i> Company
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                <div class="md:col-span-2">
                    <p class="text-xs text-gray-500">Name</p>
                    <p class="font-medium text-gray-800">{{ $attachment->company_name ?? '—' }}</p>
                </div>
                @if($attachment->company_sector)
                    <div>
                        <p class="text-xs text-gray-500">Sector</p>
                        <p class="font-medium text-gray-800">{{ $attachment->company_sector }}</p>
                    </div>
                @endif
                @if($attachment->company_phone)
                    <div>
                        <p class="text-xs text-gray-500">Phone</p>
                        <p class="font-medium text-gray-800">{{ $attachment->company_phone }}</p>
                    </div>
                @endif
                @if($attachment->company_email)
                    <div class="md:col-span-2">
                        <p class="text-xs text-gray-500">Email</p>
                        <p class="font-medium text-gray-800">{{ $attachment->company_email }}</p>
                    </div>
                @endif
                @if($attachment->company_address)
                    <div class="md:col-span-2">
                        <p class="text-xs text-gray-500">Address</p>
                        <p class="font-medium text-gray-800">{{ $attachment->company_address }}</p>
                    </div>
                @endif
            </div>

            {{-- Promote self-found to registry --}}
            @if(is_null($attachment->company_id))
                <div class="mt-4 pt-4 border-t border-gray-100">
                    <form method="POST"
                          action="{{ route('ilo.attachments.promote-company', $attachment) }}"
                          onsubmit="return confirm('Add this company to the registry? It will become available for future students.')">
                        @csrf
                        <button type="submit"
                                class="inline-flex items-center px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white rounded-lg font-semibold text-sm">
                            <i class="fas fa-plus mr-2"></i> Add to Company Registry
                        </button>
                    </form>
                    <p class="text-xs text-gray-500 mt-2">
                        The company is not in the registry yet. Adding it lets future students of the same course apply.
                    </p>
                </div>
            @endif
        </div>

        {{-- SUPERVISOR --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h3 class="font-semibold text-gray-800 mb-4 flex items-center">
                <i class="fas fa-user-tie text-primary mr-2"></i> On-Site Supervisor
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                <div>
                    <p class="text-xs text-gray-500">Name</p>
                    <p class="font-medium text-gray-800">{{ $attachment->supervisor_name ?? '—' }}</p>
                </div>
                @if($attachment->supervisor_position)
                    <div>
                        <p class="text-xs text-gray-500">Position</p>
                        <p class="font-medium text-gray-800">{{ $attachment->supervisor_position }}</p>
                    </div>
                @endif
                <div>
                    <p class="text-xs text-gray-500">Phone</p>
                    <p class="font-medium text-gray-800">{{ $attachment->supervisor_phone ?? '—' }}</p>
                </div>
                @if($attachment->supervisor_email)
                    <div>
                        <p class="text-xs text-gray-500">Email</p>
                        <p class="font-medium text-gray-800">{{ $attachment->supervisor_email }}</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- PERIOD + WORK --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h3 class="font-semibold text-gray-800 mb-4 flex items-center">
                <i class="fas fa-calendar-alt text-primary mr-2"></i> Period & Work
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm mb-4">
                <div>
                    <p class="text-xs text-gray-500">Start Date</p>
                    <p class="font-medium text-gray-800">
                        {{ $attachment->start_date?->format('d M Y') ?? '—' }}
                    </p>
                </div>
                <div>
                    <p class="text-xs text-gray-500">End Date</p>
                    <p class="font-medium text-gray-800">
                        {{ $attachment->end_date?->format('d M Y') ?? '—' }}
                    </p>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Duration</p>
                    <p class="font-medium text-gray-800">
                        {{ $attachment->duration_weeks ? $attachment->duration_weeks . ' weeks' : '—' }}
                    </p>
                </div>
            </div>

            @if($attachment->job_description)
                <div class="mt-4 pt-4 border-t border-gray-100">
                    <p class="text-xs text-gray-500 mb-1">Job Description</p>
                    <p class="text-sm text-gray-800 whitespace-pre-wrap">{{ $attachment->job_description }}</p>
                </div>
            @endif

            @if($attachment->learning_objectives)
                <div class="mt-4 pt-4 border-t border-gray-100">
                    <p class="text-xs text-gray-500 mb-1">Learning Objectives</p>
                    <p class="text-sm text-gray-800 whitespace-pre-wrap">{{ $attachment->learning_objectives }}</p>
                </div>
            @endif

            @if($attachment->student_notes)
                <div class="mt-4 pt-4 border-t border-gray-100">
                    <p class="text-xs text-gray-500 mb-1">Student Notes</p>
                    <p class="text-sm text-gray-800 whitespace-pre-wrap">{{ $attachment->student_notes }}</p>
                </div>
            @endif
        </div>

        {{-- DOCUMENTS --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h3 class="font-semibold text-gray-800 mb-4 flex items-center">
                <i class="fas fa-file-alt text-primary mr-2"></i> Documents
            </h3>

            <div class="space-y-3">
                @if($attachment->acceptance_letter_path)
                    <a href="{{ Storage::disk('public')->url($attachment->acceptance_letter_path) }}"
                       target="_blank"
                       class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100">
                        <div class="flex items-center gap-3">
                            <i class="fas fa-file-pdf text-red-500 text-xl"></i>
                            <div>
                                <p class="font-medium text-gray-800">Acceptance Letter</p>
                                <p class="text-xs text-gray-500">{{ basename($attachment->acceptance_letter_path) }}</p>
                            </div>
                        </div>
                        <i class="fas fa-external-link-alt text-primary"></i>
                    </a>
                @endif

                @if($attachment->placement_letter_path)
                    <a href="{{ Storage::disk('public')->url($attachment->placement_letter_path) }}"
                       target="_blank"
                       class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100">
                        <div class="flex items-center gap-3">
                            <i class="fas fa-file-pdf text-red-500 text-xl"></i>
                            <div>
                                <p class="font-medium text-gray-800">Placement Letter</p>
                                <p class="text-xs text-gray-500">{{ basename($attachment->placement_letter_path) }}</p>
                            </div>
                        </div>
                        <i class="fas fa-external-link-alt text-primary"></i>
                    </a>
                @endif

                @if(!$attachment->acceptance_letter_path && !$attachment->placement_letter_path)
                    <p class="text-sm text-gray-500 italic">No documents uploaded.</p>
                @endif
            </div>
        </div>

        {{-- GRADE (if any) --}}
        @if($attachment->grade || $attachment->grader_remarks)
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h3 class="font-semibold text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-award text-primary mr-2"></i> Grade
                </h3>
                <div class="text-sm space-y-3">
                    @if($attachment->grade)
                        <div>
                            <p class="text-xs text-gray-500">Grade</p>
                            <p class="text-2xl font-bold text-emerald-600">{{ $attachment->grade }}</p>
                        </div>
                    @endif
                    @if($attachment->grader_remarks)
                        <div>
                            <p class="text-xs text-gray-500">Grader Remarks</p>
                            <p class="text-gray-800 whitespace-pre-wrap">{{ $attachment->grader_remarks }}</p>
                        </div>
                    @endif
                    @if($attachment->graded_at)
                        <p class="text-xs text-gray-500">
                            Graded {{ $attachment->graded_at->format('d M Y H:i') }}
                            @if($attachment->grader) by {{ $attachment->grader->name }} @endif
                        </p>
                    @endif
                </div>
            </div>
        @endif

        {{-- SITE VISITS --}}
        @if($attachment->last_visit_date || $attachment->last_visit_notes)
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h3 class="font-semibold text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-map-marked-alt text-primary mr-2"></i> Last Site Visit
                </h3>
                <div class="text-sm space-y-3">
                    <div>
                        <p class="text-xs text-gray-500">Visit Date</p>
                        <p class="font-medium text-gray-800">
                            {{ $attachment->last_visit_date?->format('d M Y') ?? '—' }}
                        </p>
                    </div>
                    @if($attachment->last_visit_notes)
                        <div>
                            <p class="text-xs text-gray-500">Notes</p>
                            <p class="text-gray-800 whitespace-pre-wrap">{{ $attachment->last_visit_notes }}</p>
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </div>

    {{-- RIGHT: Actions --}}
    <div class="space-y-6">

        {{-- APPROVE / REVISION / REJECT — only when status is submitted --}}
        @if($attachment->canBeActivated())
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h3 class="font-semibold text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-gavel text-primary mr-2"></i> Review Decision
                </h3>

                {{-- Approve --}}
                <form method="POST"
                      action="{{ route('ilo.attachments.approve', $attachment) }}"
                      onsubmit="return confirm('Approve this attachment? It will become active.')"
                      class="mb-4">
                    @csrf
                    <label class="block text-xs font-medium text-gray-600 mb-1">
                        Approval remarks (optional)
                    </label>
                    <textarea name="remarks" rows="2"
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm mb-2"
                              placeholder="e.g. Details verified, placement accepted"></textarea>
                    <button type="submit"
                            class="w-full py-2.5 bg-green-600 hover:bg-green-700 text-white rounded-lg font-semibold transition">
                        <i class="fas fa-check-circle mr-2"></i> Approve & Activate
                    </button>
                </form>

                <hr class="my-4 border-gray-100">

                {{-- Request Revision --}}
                <form method="POST"
                      action="{{ route('ilo.attachments.request-revision', $attachment) }}"
                      onsubmit="return confirm('Request a revision? The student will be able to edit and resubmit.')"
                      class="mb-4">
                    @csrf
                    <label class="block text-xs font-medium text-gray-600 mb-1">
                        What needs to change? <span class="text-red-500">*</span>
                    </label>
                    <textarea name="remarks" rows="2" required
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm mb-2"
                              placeholder="e.g. Supervisor phone number is invalid; upload acceptance letter"></textarea>
                    <button type="submit"
                            class="w-full py-2.5 bg-amber-500 hover:bg-amber-600 text-white rounded-lg font-semibold transition">
                        <i class="fas fa-undo mr-2"></i> Request Revision
                    </button>
                </form>

                <hr class="my-4 border-gray-100">

                {{-- Reject --}}
                <form method="POST"
                      action="{{ route('ilo.attachments.reject', $attachment) }}"
                      onsubmit="return confirm('Reject this attachment? The student will see the reason.')">
                    @csrf
                    <label class="block text-xs font-medium text-gray-600 mb-1">
                        Rejection reason <span class="text-red-500">*</span>
                    </label>
                    <textarea name="remarks" rows="2" required
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm mb-2"
                              placeholder="Explain why this attachment is being rejected"></textarea>
                    <button type="submit"
                            class="w-full py-2.5 bg-red-600 hover:bg-red-700 text-white rounded-lg font-semibold transition">
                        <i class="fas fa-times-circle mr-2"></i> Reject Attachment
                    </button>
                </form>
            </div>
        @endif

        {{-- COMPLETE — only when active --}}
        @if($attachment->canBeCompleted())
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h3 class="font-semibold text-gray-800 mb-3 flex items-center">
                    <i class="fas fa-flag-checkered text-primary mr-2"></i> Mark as Completed
                </h3>
                <p class="text-xs text-gray-500 mb-3">
                    Use this when the student has finished the attachment period.
                </p>
                <form method="POST"
                      action="{{ route('ilo.attachments.complete', $attachment) }}"
                      onsubmit="return confirm('Mark this attachment as completed?')">
                    @csrf
                    <textarea name="remarks" rows="2"
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm mb-2"
                              placeholder="Completion remarks (optional)"></textarea>
                    <button type="submit"
                            class="w-full py-2.5 bg-purple-600 hover:bg-purple-700 text-white rounded-lg font-semibold transition">
                        <i class="fas fa-flag-checkered mr-2"></i> Mark Completed
                    </button>
                </form>
            </div>
        @endif

        {{-- GRADE — for active or completed --}}
        @if(in_array($attachment->status, ['active', 'completed', 'graded']))
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h3 class="font-semibold text-gray-800 mb-3 flex items-center">
                    <i class="fas fa-award text-primary mr-2"></i>
                    {{ $attachment->grade ? 'Update Grade' : 'Grade Attachment' }}
                </h3>
                <form method="POST" action="{{ route('ilo.attachments.grade', $attachment) }}">
                    @csrf
                    <div class="mb-3">
                        <label class="block text-xs font-medium text-gray-600 mb-1">
                            Grade <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="grade"
                               value="{{ $attachment->grade }}"
                               required maxlength="10"
                               placeholder="A / B / Pass / Distinction"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    </div>
                    <div class="mb-3">
                        <label class="block text-xs font-medium text-gray-600 mb-1">Remarks</label>
                        <textarea name="remarks" rows="2"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"
                                  placeholder="Optional remarks">{{ $attachment->grader_remarks }}</textarea>
                    </div>
                    <button type="submit"
                            class="w-full py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg font-semibold transition">
                        <i class="fas fa-save mr-2"></i> Save Grade
                    </button>
                </form>
            </div>
        @endif

        {{-- LOG VISIT — active only --}}
        @if($attachment->status === 'active')
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h3 class="font-semibold text-gray-800 mb-3 flex items-center">
                    <i class="fas fa-map-marked-alt text-primary mr-2"></i> Log Site Visit
                </h3>
                <form method="POST" action="{{ route('ilo.attachments.visit', $attachment) }}">
                    @csrf
                    <div class="mb-3">
                        <label class="block text-xs font-medium text-gray-600 mb-1">Visit Date</label>
                        <input type="date" name="visit_date"
                               value="{{ now()->toDateString() }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    </div>
                    <div class="mb-3">
                        <label class="block text-xs font-medium text-gray-600 mb-1">
                            Notes <span class="text-red-500">*</span>
                        </label>
                        <textarea name="notes" rows="3" required
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"
                                  placeholder="What did you observe at the site?"></textarea>
                    </div>
                    <button type="submit"
                            class="w-full py-2.5 bg-primary hover:bg-primary-dark text-white rounded-lg font-semibold transition">
                        <i class="fas fa-save mr-2"></i> Save Visit Log
                    </button>
                </form>
            </div>
        @endif

        {{-- META --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h3 class="font-semibold text-gray-800 mb-3 flex items-center">
                <i class="fas fa-info-circle text-primary mr-2"></i> Meta
            </h3>
            <div class="space-y-3 text-xs">
                <div>
                    <p class="text-gray-500">Submitted</p>
                    <p class="font-medium text-gray-800">
                        {{ $attachment->created_at?->format('d M Y H:i') ?? '—' }}
                    </p>
                </div>
                <div>
                    <p class="text-gray-500">Last Updated</p>
                    <p class="font-medium text-gray-800">
                        {{ $attachment->updated_at?->format('d M Y H:i') ?? '—' }}
                    </p>
                </div>
                @if($attachment->term)
                    <div>
                        <p class="text-gray-500">Term</p>
                        <p class="font-medium text-gray-800">{{ $attachment->term->name }}</p>
                    </div>
                @endif
                @if($attachment->opportunity)
                    <div>
                        <p class="text-gray-500">Linked Opportunity</p>
                        <a href="{{ route('ilo.opportunities.show', $attachment->opportunity) }}"
                           class="text-primary hover:underline font-medium">
                            {{ $attachment->opportunity->title ?? 'View opportunity' }}
                        </a>
                    </div>
                @endif
                @if($attachment->application)
                    <div>
                        <p class="text-gray-500">Linked Application</p>
                        <a href="{{ route('ilo.applications.show', $attachment->application) }}"
                           class="text-primary hover:underline font-medium">
                            View application →
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@endsection