@extends('ktvtc.trainers.layout.trainerlayout')

@section('title', 'Attachment Detail')

@section('content')

<div class="mb-4">
    <a href="{{ route('trainers.attachments.index') }}"
       class="text-sm text-gray-500 hover:text-primary">
        <i class="fas fa-arrow-left mr-1"></i> Back to attachments
    </a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- LEFT — main details --}}
    <div class="lg:col-span-2 space-y-6">

        {{-- Header card --}}
        <div class="bg-white rounded-lg shadow p-5">
            <div class="flex items-start justify-between mb-3">
                <div>
                    <h2 class="text-xl font-bold text-gray-800">
                        {{ $attachment->student->first_name ?? '' }}
                        {{ $attachment->student->last_name ?? '' }}
                    </h2>
                    <p class="text-sm text-gray-500">
                        {{ $attachment->student->student_number ?? '' }}
                        · {{ $attachment->student->email ?? '' }}
                    </p>
                </div>
                <span class="badge
                    @switch($attachment->status)
                        @case('submitted') bg-blue-100 text-blue-700 @break
                        @case('trainer_approved') bg-indigo-100 text-indigo-700 @break
                        @case('needs_revision') bg-amber-100 text-amber-700 @break
                        @case('active') bg-green-100 text-green-700 @break
                        @case('completed') bg-purple-100 text-purple-700 @break
                        @case('graded') bg-emerald-100 text-emerald-700 @break
                        @case('rejected') bg-red-100 text-red-700 @break
                        @default bg-gray-100 text-gray-700
                    @endswitch">
                    {{ $attachment->status_label }}
                </span>
            </div>

            <div class="grid grid-cols-2 gap-3 text-sm">
                <div>
                    <p class="text-xs uppercase text-gray-500">Course</p>
                    <p class="text-gray-800">
                        {{ optional($attachment->trainerApprovedCourse)->name
                           ?? optional($attachment->student->activeEnrollment)->course->name
                           ?? '—' }}
                    </p>
                </div>
                <div>
                    <p class="text-xs uppercase text-gray-500">Term</p>
                    <p class="text-gray-800">{{ $attachment->term->name ?? '—' }}</p>
                </div>
            </div>
        </div>

        {{-- Company --}}
        <div class="bg-white rounded-lg shadow p-5">
            <h3 class="font-semibold text-gray-800 mb-3 flex items-center gap-2">
                <i class="fas fa-building text-primary"></i> Company / Placement
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                <div>
                    <p class="text-xs uppercase text-gray-500">Company</p>
                    <p class="text-gray-800">{{ $attachment->company->name ?? $attachment->company_name }}</p>
                </div>
                <div>
                    <p class="text-xs uppercase text-gray-500">Sector</p>
                    <p class="text-gray-800">
                        {{ $attachment->company->sector ?? $attachment->company_sector ?? '—' }}
                    </p>
                </div>
                <div class="md:col-span-2">
                    <p class="text-xs uppercase text-gray-500">Address / Where</p>
                    <p class="text-gray-800">
                        {{ $attachment->company->physical_address ?? $attachment->company_address ?? '—' }}
                    </p>
                </div>
                <div>
                    <p class="text-xs uppercase text-gray-500">Phone</p>
                    <p class="text-gray-800">
                        {{ $attachment->company->company_phone ?? $attachment->company_phone ?? '—' }}
                    </p>
                </div>
                <div>
                    <p class="text-xs uppercase text-gray-500">Email</p>
                    <p class="text-gray-800">
                        {{ $attachment->company->company_email ?? $attachment->company_email ?? '—' }}
                    </p>
                </div>
            </div>
        </div>

        {{-- Supervisor + period --}}
        <div class="bg-white rounded-lg shadow p-5">
            <h3 class="font-semibold text-gray-800 mb-3 flex items-center gap-2">
                <i class="fas fa-user-tie text-primary"></i> Supervisor & Period
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                <div>
                    <p class="text-xs uppercase text-gray-500">Supervisor</p>
                    <p class="text-gray-800">{{ $attachment->supervisor_name }}</p>
                    <p class="text-gray-500 text-xs">{{ $attachment->supervisor_position ?? '' }}</p>
                </div>
                <div>
                    <p class="text-xs uppercase text-gray-500">Supervisor Contact</p>
                    <p class="text-gray-800">{{ $attachment->supervisor_phone }}</p>
                    <p class="text-gray-500 text-xs">{{ $attachment->supervisor_email ?? '' }}</p>
                </div>
                <div>
                    <p class="text-xs uppercase text-gray-500">Start Date</p>
                    <p class="text-gray-800">{{ $attachment->start_date?->format('d M Y') }}</p>
                </div>
                <div>
                    <p class="text-xs uppercase text-gray-500">End Date</p>
                    <p class="text-gray-800">{{ $attachment->end_date?->format('d M Y') }}</p>
                </div>
                <div class="md:col-span-2">
                    <div class="flex items-center gap-2">
                        <div class="flex-1 bg-gray-200 rounded-full h-2">
                            <div class="h-2 rounded-full bg-primary"
                                 style="width: {{ $attachment->progress_percentage }}%"></div>
                        </div>
                        <span class="text-xs text-gray-500">
                            {{ $attachment->progress_percentage }}%
                            · {{ $attachment->days_remaining }} days left
                        </span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Description --}}
        <div class="bg-white rounded-lg shadow p-5">
            <h3 class="font-semibold text-gray-800 mb-3 flex items-center gap-2">
                <i class="fas fa-file-alt text-primary"></i> Work Details
            </h3>

            <div class="space-y-3 text-sm">
                <div>
                    <p class="text-xs uppercase text-gray-500 mb-1">Job Description</p>
                    <p class="text-gray-800 whitespace-pre-line">
                        {{ $attachment->job_description ?? '—' }}
                    </p>
                </div>
                <div>
                    <p class="text-xs uppercase text-gray-500 mb-1">Learning Objectives</p>
                    <p class="text-gray-800 whitespace-pre-line">
                        {{ $attachment->learning_objectives ?? '—' }}
                    </p>
                </div>
                <div>
                    <p class="text-xs uppercase text-gray-500 mb-1">Student Notes</p>
                    <p class="text-gray-800 whitespace-pre-line">
                        {{ $attachment->student_notes ?? '—' }}
                    </p>
                </div>
            </div>

            {{-- Uploaded documents --}}
            @if ($attachment->acceptance_letter_path || $attachment->placement_letter_path)
                <div class="mt-4 pt-4 border-t">
                    <p class="text-xs uppercase text-gray-500 mb-2">Documents</p>
                    <div class="flex flex-wrap gap-2">
                        @if ($attachment->acceptance_letter_path)
                            <a href="{{ asset('storage/'.$attachment->acceptance_letter_path) }}"
                               target="_blank"
                               class="text-xs bg-gray-100 hover:bg-gray-200 text-gray-800 px-3 py-1 rounded">
                                <i class="fas fa-file-pdf mr-1 text-red-500"></i> Acceptance Letter
                            </a>
                        @endif
                        @if ($attachment->placement_letter_path)
                            <a href="{{ asset('storage/'.$attachment->placement_letter_path) }}"
                               target="_blank"
                               class="text-xs bg-gray-100 hover:bg-gray-200 text-gray-800 px-3 py-1 rounded">
                                <i class="fas fa-file-pdf mr-1 text-red-500"></i> Placement Letter
                            </a>
                        @endif
                    </div>
                </div>
            @endif
        </div>

    </div>

    {{-- RIGHT — actions --}}
    <div class="space-y-6">

        {{-- Approval card --}}
        @if ($attachment->canBeApprovedByTrainer())
            <div class="bg-white rounded-lg shadow p-5 border-t-4 border-primary">
                <h3 class="font-semibold text-gray-800 mb-3">
                    <i class="fas fa-check-circle text-primary mr-2"></i> Trainership Action
                </h3>

                @can('approveAsTrainer', $attachment)
                    <form method="POST"
                          action="{{ route('trainers.attachments.approve', $attachment) }}"
                          class="space-y-3">
                        @csrf

                        @if ($courses->count() > 1)
                            <div>
                                <label class="block text-xs uppercase text-gray-500 mb-1">
                                    Approval Course
                                </label>
                                <select name="course_id" class="w-full border-gray-300 rounded-md text-sm">
                                    @foreach ($courses as $c)
                                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        <div>
                            <label class="block text-xs uppercase text-gray-500 mb-1">
                                Remarks (optional)
                            </label>
                            <textarea name="remarks" rows="3"
                                      class="w-full border-gray-300 rounded-md text-sm"
                                      placeholder="e.g. Cleared — student meets requirements."></textarea>
                        </div>

                        <button class="w-full bg-green-600 hover:bg-green-700 text-white text-sm font-medium py-2 rounded-md">
                            <i class="fas fa-check mr-1"></i> Approve Student
                        </button>
                    </form>

                    <hr class="my-4">

                    <form method="POST"
                          action="{{ route('trainers.attachments.request-revision', $attachment) }}"
                          class="space-y-3">
                        @csrf
                        <div>
                            <label class="block text-xs uppercase text-gray-500 mb-1">
                                Request Revision
                            </label>
                            <textarea name="remarks" rows="3" required
                                      class="w-full border-gray-300 rounded-md text-sm"
                                      placeholder="Explain what needs to change…"></textarea>
                        </div>
                        <button class="w-full bg-amber-500 hover:bg-amber-600 text-white text-sm font-medium py-2 rounded-md">
                            <i class="fas fa-undo mr-1"></i> Send Back for Revision
                        </button>
                    </form>
                @else
                    <p class="text-sm text-gray-500">
                        You do not have permission to approve this attachment.
                    </p>
                @endcan
            </div>
        @endif

        {{-- Eligibility card --}}
        <div class="bg-white rounded-lg shadow p-5">
            <h3 class="font-semibold text-gray-800 mb-3">
                <i class="fas fa-clipboard-check text-primary mr-2"></i> Eligibility
            </h3>

            @if ($attachment->eligibility_checked_at)
                <p class="text-sm text-gray-700 mb-1">
                    <strong>Status:</strong>
                    {{ $attachment->isEligibleForAttachment() ? 'Eligible' : 'Not eligible' }}
                </p>
                <p class="text-xs text-gray-500 mb-3">
                    Checked
                    {{ $attachment->eligibility_checked_at->diffForHumans() }}
                    @if ($attachment->eligibilityCheckedBy)
                        by {{ $attachment->eligibilityCheckedBy->name }}
                    @endif
                </p>
                @if ($attachment->eligibility_notes)
                    <p class="text-sm text-gray-700 bg-gray-50 rounded p-2 mb-3">
                        {{ $attachment->eligibility_notes }}
                    </p>
                @endif
            @else
                <p class="text-sm text-gray-500 mb-3">Not yet reviewed.</p>
            @endif

            @can('markEligibilityAsTrainer', $attachment)
                <form method="POST"
                      action="{{ route('trainers.attachments.eligibility', $attachment) }}"
                      class="space-y-2">
                    @csrf
                    <select name="is_eligible" class="w-full border-gray-300 rounded-md text-sm">
                        <option value="1" @selected($attachment->is_eligible_for_attachment)>Eligible</option>
                        <option value="0" @selected(!$attachment->is_eligible_for_attachment)>Not eligible</option>
                    </select>
                    <textarea name="notes" rows="2"
                              class="w-full border-gray-300 rounded-md text-sm"
                              placeholder="Notes (optional)"></textarea>
                    <button class="w-full bg-primary hover:bg-secondary text-white text-sm font-medium py-2 rounded-md">
                        Save Eligibility
                    </button>
                </form>
            @endcan
        </div>

        {{-- Visit log --}}
        <div class="bg-white rounded-lg shadow p-5">
            <h3 class="font-semibold text-gray-800 mb-3">
                <i class="fas fa-map-marker-alt text-primary mr-2"></i> Site Visits
            </h3>

            <p class="text-sm text-gray-700 mb-2">
                <strong>{{ $attachment->trainer_visit_count ?? 0 }}</strong> visit(s) logged
            </p>
            @if ($attachment->last_trainer_visit_at)
                <p class="text-xs text-gray-500 mb-3">
                    Last visit: {{ $attachment->last_trainer_visit_at->diffForHumans() }}
                </p>
            @endif

            @if ($attachment->status === 'active')
                @can('logVisitAsTrainer', $attachment)
                    <form method="POST"
                          action="{{ route('trainers.attachments.visit', $attachment) }}"
                          class="space-y-2">
                        @csrf
                        <textarea name="notes" rows="3" required
                                  class="w-full border-gray-300 rounded-md text-sm"
                                  placeholder="Visit notes…"></textarea>
                        <button class="w-full bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium py-2 rounded-md">
                            <i class="fas fa-plus mr-1"></i> Log Visit
                        </button>
                    </form>
                @endcan
            @else
                <p class="text-xs text-gray-500">
                    Visits can only be logged for active attachments.
                </p>
            @endif
        </div>

        {{-- Approval trail --}}
        @if ($attachment->trainer_approved_at)
            <div class="bg-white rounded-lg shadow p-5">
                <h3 class="font-semibold text-gray-800 mb-3">
                    <i class="fas fa-history text-primary mr-2"></i> Approval Trail
                </h3>
                <p class="text-sm text-gray-700">
                    Approved by
                    <strong>{{ $attachment->trainerApprovedBy->name ?? '—' }}</strong>
                    {{ $attachment->trainer_approved_at->diffForHumans() }}
                </p>
                @if ($attachment->trainerApprovedCourse)
                    <p class="text-xs text-gray-500 mt-1">
                        Course: {{ $attachment->trainerApprovedCourse->name }}
                    </p>
                @endif
                @if ($attachment->trainer_remarks)
                    <p class="text-sm text-gray-700 bg-gray-50 rounded p-2 mt-2">
                        {{ $attachment->trainer_remarks }}
                    </p>
                @endif
            </div>
        @endif

    </div>
</div>

@endsection