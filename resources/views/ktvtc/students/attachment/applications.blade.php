@extends('ktvtc.students.layout.studentlayout')

@section('title', 'My Applications')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">

    {{-- Header --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-800 flex items-center">
                    <i class="fas fa-file-signature text-primary mr-3"></i>
                    My Attachment Applications
                </h1>
                <p class="text-gray-500 mt-1">Track the status of your applications.</p>
            </div>
            <a href="{{ route('student.attachment.opportunities') }}"
               class="inline-flex items-center px-4 py-2 bg-primary hover:bg-primary-dark text-white font-semibold rounded-lg">
                <i class="fas fa-search mr-2"></i> Browse Opportunities
            </a>
        </div>
    </div>

    {{-- Applications --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        @forelse($applications as $app)
            <div class="border-b border-gray-100 last:border-b-0 p-5 hover:bg-gray-50">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div class="flex-1 min-w-0">
                        <p class="font-semibold text-gray-800">
                            {{ $app->opportunity->company->name ?? '—' }}
                        </p>
                        <p class="text-sm text-gray-600 mt-0.5">
                            {{ $app->opportunity->course->name ?? '' }}
                            @if($app->opportunity->term)
                                · {{ $app->opportunity->term->name }}
                            @endif
                        </p>
                        <div class="flex flex-wrap gap-x-4 gap-y-1 text-xs text-gray-500 mt-2">
                            <span><i class="fas fa-calendar mr-1"></i>Applied {{ $app->created_at?->format('d M Y') }}</span>
                            @if($app->opportunity->start_date)
                                <span>
                                    <i class="fas fa-briefcase mr-1"></i>
                                    {{ $app->opportunity->start_date->format('d M Y') }}
                                    → {{ $app->opportunity->end_date?->format('d M Y') }}
                                </span>
                            @endif
                            @if($app->opportunity->location)
                                <span><i class="fas fa-map-marker-alt mr-1"></i>{{ $app->opportunity->location }}</span>
                            @endif
                        </div>
                    </div>

                    <div class="flex flex-col items-end gap-2">
                        @php
                            $badge = match($app->status) {
                                'pending'     => 'bg-amber-100 text-amber-700',
                                'shortlisted' => 'bg-blue-100 text-blue-700',
                                'approved'    => 'bg-green-100 text-green-700',
                                'rejected'    => 'bg-red-100 text-red-700',
                                'withdrawn'   => 'bg-gray-100 text-gray-700',
                                default       => 'bg-gray-100 text-gray-700',
                            };
                        @endphp
                        <span class="text-xs px-3 py-1 rounded-full font-semibold {{ $badge }}">
                            {{ ucfirst($app->status) }}
                        </span>

                        @if($app->isPending() || $app->isShortlisted())
                            <form method="POST"
                                  action="{{ route('student.attachment.applications.withdraw', $app) }}"
                                  onsubmit="return confirm('Withdraw this application?')">
                                @csrf
                                <button type="submit"
                                        class="text-xs text-red-600 hover:text-red-700 font-medium">
                                    <i class="fas fa-times-circle mr-1"></i> Withdraw
                                </button>
                            </form>
                        @endif
                    </div>
                </div>

                {{-- Docs / review remarks --}}
                @if($app->cv_path || $app->cover_letter_path || $app->review_remarks)
                    <div class="mt-4 pt-4 border-t border-gray-100 grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
                        @if($app->cv_path || $app->cover_letter_path)
                            <div>
                                <p class="text-gray-500 mb-1">Documents</p>
                                <div class="flex gap-3">
                                    @if($app->cv_path)
                                        <a href="{{ Storage::disk('public')->url($app->cv_path) }}"
                                           target="_blank" class="text-primary hover:underline">
                                            <i class="fas fa-file-pdf mr-1"></i> CV
                                        </a>
                                    @endif
                                    @if($app->cover_letter_path)
                                        <a href="{{ Storage::disk('public')->url($app->cover_letter_path) }}"
                                           target="_blank" class="text-primary hover:underline">
                                            <i class="fas fa-file-alt mr-1"></i> Cover Letter
                                        </a>
                                    @endif
                                </div>
                            </div>
                        @endif

                        @if($app->review_remarks)
                            <div>
                                <p class="text-gray-500 mb-1">Reviewer's remarks</p>
                                <p class="text-gray-700 whitespace-pre-wrap">{{ $app->review_remarks }}</p>
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        @empty
            <div class="p-12 text-center">
                <i class="fas fa-inbox text-5xl text-gray-300 mb-3"></i>
                <p class="text-lg font-semibold text-gray-700">No applications yet</p>
                <p class="text-sm text-gray-500 mt-1">
                    Browse the opportunities for your course and apply to one.
                </p>
                <a href="{{ route('student.attachment.opportunities') }}"
                   class="mt-4 inline-block px-5 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg font-semibold">
                    <i class="fas fa-search mr-2"></i> Browse Opportunities
                </a>
            </div>
        @endforelse

        @if($applications->hasPages())
            <div class="px-6 py-4 border-t border-gray-100 bg-gray-50">
                {{ $applications->appends(request()->query())->links() }}
            </div>
        @endif
    </div>
</div>
@endsection