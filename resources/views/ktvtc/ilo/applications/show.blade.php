@extends('ktvtc.ilo.layout.ilolayout')

@section('title', 'Application Review')
@section('subtitle', ($application->student->full_name ?? '') . ' → ' . ($application->opportunity->company->name ?? ''))

@section('header-actions')
<a href="{{ route('ilo.applications.index') }}"
   class="inline-flex items-center px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 font-medium transition">
    <i class="fas fa-arrow-left mr-2"></i> Back to Applications
</a>
@endsection

@section('content')

@php
    $status = $application->status;
    $badge = match($status) {
        'pending'     => 'bg-amber-100 text-amber-700',
        'shortlisted' => 'bg-blue-100 text-blue-700',
        'approved'    => 'bg-green-100 text-green-700',
        'rejected'    => 'bg-red-100 text-red-700',
        'withdrawn'   => 'bg-gray-100 text-gray-700',
        default       => 'bg-gray-100 text-gray-700',
    };
@endphp

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- LEFT: Application + documents --}}
    <div class="lg:col-span-2 space-y-6">

        {{-- Header --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h2 class="text-xl font-bold text-gray-800">
                        {{ $application->student->full_name ?? 'Student' }}
                    </h2>
                    <p class="text-sm text-gray-500 font-mono">
                        {{ $application->student->student_number ?? '' }}
                    </p>
                    <p class="text-sm text-gray-600 mt-2">
                        <i class="fas fa-phone text-gray-400 mr-1"></i>{{ $application->student->phone ?? '—' }}
                        @if($application->student->email)
                            <span class="ml-3">
                                <i class="fas fa-envelope text-gray-400 mr-1"></i>{{ $application->student->email }}
                            </span>
                        @endif
                    </p>
                </div>
                <span class="text-xs px-3 py-1 rounded-full font-semibold {{ $badge }}">
                    {{ ucfirst($status) }}
                </span>
            </div>
        </div>

        {{-- Opportunity context --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h3 class="font-semibold text-gray-800 mb-4">
                <i class="fas fa-bullhorn text-primary mr-2"></i> Opportunity
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                <div>
                    <p class="text-xs text-gray-500">Company</p>
                    <p class="font-medium text-gray-800">
                        {{ $application->opportunity->company->name ?? '—' }}
                    </p>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Course</p>
                    <p class="font-medium text-gray-800">
                        {{ $application->opportunity->course->name ?? '—' }}
                        @if($application->opportunity->course?->code)
                            <span class="text-xs text-gray-400 font-mono">
                                ({{ $application->opportunity->course->code }})
                            </span>
                        @endif
                    </p>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Attachment Period</p>
                    <p class="font-medium text-gray-800">
                        {{ $application->opportunity->start_date?->format('d M Y') ?? '—' }}
                        → {{ $application->opportunity->end_date?->format('d M Y') ?? '—' }}
                    </p>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Term</p>
                    <p class="font-medium text-gray-800">
                        {{ $application->opportunity->term->name ?? '—' }}
                    </p>
                </div>
                @if($application->opportunity->location)
                    <div class="md:col-span-2">
                        <p class="text-xs text-gray-500">Location</p>
                        <p class="font-medium text-gray-800">
                            <i class="fas fa-map-marker-alt text-primary mr-1"></i>
                            {{ $application->opportunity->location }}
                        </p>
                    </div>
                @endif
            </div>
        </div>

        {{-- Documents --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h3 class="font-semibold text-gray-800 mb-4">
                <i class="fas fa-file-alt text-primary mr-2"></i> Submitted Documents
            </h3>

            <div class="space-y-3">
                @if($application->cv_path)
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-red-100 flex items-center justify-center">
                                <i class="fas fa-file-pdf text-red-600"></i>
                            </div>
                            <div>
                                <p class="font-medium text-gray-800">Curriculum Vitae</p>
                                <p class="text-xs text-gray-500">{{ basename($application->cv_path) }}</p>
                            </div>
                        </div>
                        <a href="{{ Storage::disk('public')->url($application->cv_path) }}"
                           target="_blank"
                           class="text-primary hover:text-primary-dark text-sm font-medium">
                            <i class="fas fa-external-link-alt mr-1"></i> Open
                        </a>
                    </div>
                @else
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                        <p class="text-sm text-gray-500 italic">No CV uploaded</p>
                    </div>
                @endif

                @if($application->cover_letter_path)
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center">
                                <i class="fas fa-file-alt text-blue-600"></i>
                            </div>
                            <div>
                                <p class="font-medium text-gray-800">Cover Letter</p>
                                <p class="text-xs text-gray-500">{{ basename($application->cover_letter_path) }}</p>
                            </div>
                        </div>
                        <a href="{{ Storage::disk('public')->url($application->cover_letter_path) }}"
                           target="_blank"
                           class="text-primary hover:text-primary-dark text-sm font-medium">
                            <i class="fas fa-external-link-alt mr-1"></i> Open
                        </a>
                    </div>
                @else
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                        <p class="text-sm text-gray-500 italic">No cover letter uploaded</p>
                    </div>
                @endif
            </div>

            @if($application->student_notes)
                <div class="mt-4 pt-4 border-t border-gray-100">
                    <p class="text-xs text-gray-500 mb-1">Student's note</p>
                    <p class="text-sm text-gray-800 whitespace-pre-wrap">{{ $application->student_notes }}</p>
                </div>
            @endif
        </div>

        {{-- Prior review --}}
        @if($application->reviewed_at)
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h3 class="font-semibold text-gray-800 mb-4">
                    <i class="fas fa-history text-primary mr-2"></i> Review History
                </h3>
                <div class="space-y-2 text-sm">
                    <div>
                        <p class="text-xs text-gray-500">Reviewed by</p>
                        <p class="font-medium text-gray-800">{{ $application->reviewer->name ?? 'System' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Reviewed at</p>
                        <p class="font-medium text-gray-800">
                            {{ $application->reviewed_at->format('d M Y H:i') }}
                        </p>
                    </div>
                    @if($application->review_remarks)
                        <div>
                            <p class="text-xs text-gray-500">Remarks</p>
                            <p class="text-gray-800 whitespace-pre-wrap">{{ $application->review_remarks }}</p>
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </div>

    {{-- RIGHT: Actions --}}
    <div class="space-y-6">

        @if($application->canBeReviewed())
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h3 class="font-semibold text-gray-800 mb-4">
                    <i class="fas fa-gavel text-primary mr-2"></i> Decision
                </h3>

                @php
                    $remaining = $application->opportunity->slotsRemaining();
                @endphp

                @if($remaining <= 0)
                    <div class="mb-4 p-3 rounded-lg bg-red-50 border border-red-200 text-red-700 text-sm">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        This opportunity is full. Increase slots in the opportunity edit page to approve more students.
                    </div>
                @else
                    <div class="mb-4 p-3 rounded-lg bg-blue-50 border border-blue-200 text-blue-700 text-sm">
                        <i class="fas fa-info-circle mr-2"></i>
                        {{ $remaining }} of {{ $application->opportunity->slots }} slots remaining.
                    </div>
                @endif

                {{-- Approve --}}
                <form method="POST" action="{{ route('ilo.applications.approve', $application) }}"
                      onsubmit="return confirm('Approve this application? A slot will be reserved.')"
                      class="mb-4">
                    @csrf
                    <label class="block text-xs font-medium text-gray-600 mb-1">Approval remarks (optional)</label>
                    <textarea name="remarks" rows="2"
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary text-sm mb-2"
                              placeholder="e.g. Good academic record, recommended"></textarea>
                    <button type="submit"
                            @if($remaining <= 0) disabled @endif
                            class="w-full py-2.5 bg-green-600 hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed text-white rounded-lg font-semibold transition">
                        <i class="fas fa-check-circle mr-2"></i> Approve Application
                    </button>
                </form>

                {{-- Shortlist --}}
                <form method="POST" action="{{ route('ilo.applications.shortlist', $application) }}"
                      onsubmit="return confirm('Shortlist this application?')"
                      class="mb-4">
                    @csrf
                    <button type="submit"
                            class="w-full py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-semibold transition">
                        <i class="fas fa-star mr-2"></i> Shortlist
                    </button>
                </form>

                {{-- Reject --}}
                <form method="POST" action="{{ route('ilo.applications.reject', $application) }}"
                      onsubmit="return confirm('Reject this application?')">
                    @csrf
                    <label class="block text-xs font-medium text-gray-600 mb-1">
                        Rejection reason <span class="text-red-500">*</span>
                    </label>
                    <textarea name="remarks" rows="2" required
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary text-sm mb-2"
                              placeholder="Explain why the application is being rejected..."></textarea>
                    <button type="submit"
                            class="w-full py-2.5 bg-red-600 hover:bg-red-700 text-white rounded-lg font-semibold transition">
                        <i class="fas fa-times-circle mr-2"></i> Reject Application
                    </button>
                </form>
            </div>
        @else
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h3 class="font-semibold text-gray-800 mb-2">
                    <i class="fas fa-info-circle text-primary mr-2"></i> No Further Action
                </h3>
                <p class="text-sm text-gray-600">
                    This application has already been {{ $status }}.
                </p>
            </div>
        @endif

        {{-- Student card --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h3 class="font-semibold text-gray-800 mb-4">
                <i class="fas fa-user-graduate text-primary mr-2"></i> Student Info
            </h3>
            <div class="space-y-3 text-sm">
                <div>
                    <p class="text-xs text-gray-500">Full Name</p>
                    <p class="font-medium text-gray-800">{{ $application->student->full_name ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Student Number</p>
                    <p class="font-mono font-medium text-gray-800">{{ $application->student->student_number ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Phone</p>
                    <p class="font-medium text-gray-800">{{ $application->student->phone ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Email</p>
                    <p class="font-medium text-gray-800">{{ $application->student->email ?? '—' }}</p>
                </div>

                <a href="{{ route('admin.students.show', $application->student_id) }}"
                   class="block text-center text-primary hover:text-primary-dark text-sm font-medium pt-2 border-t border-gray-100">
                    View full student profile →
                </a>
            </div>
        </div>

        {{-- Timeline --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h3 class="font-semibold text-gray-800 mb-4">
                <i class="fas fa-clock text-primary mr-2"></i> Timeline
            </h3>
            <div class="space-y-3 text-sm">
                <div class="flex items-start gap-3">
                    <div class="w-2 h-2 rounded-full bg-primary mt-1.5"></div>
                    <div>
                        <p class="text-gray-800 font-medium">Application submitted</p>
                        <p class="text-xs text-gray-500">{{ $application->created_at?->format('d M Y H:i') }}</p>
                    </div>
                </div>
                @if($application->reviewed_at)
                    <div class="flex items-start gap-3">
                        <div class="w-2 h-2 rounded-full bg-blue-500 mt-1.5"></div>
                        <div>
                            <p class="text-gray-800 font-medium">Status set to {{ $status }}</p>
                            <p class="text-xs text-gray-500">{{ $application->reviewed_at->format('d M Y H:i') }}</p>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection