@extends('ktvtc.students.layout.studentlayout')

@section('title', 'My Attachment')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">

    {{-- Header --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-800 flex items-center">
                    <i class="fas fa-briefcase text-primary mr-3"></i>
                    My Industrial Attachment
                </h1>
                <p class="text-gray-500 mt-1">
                    Apply for a school-arranged placement, or declare one you've found yourself.
                </p>
            </div>
        </div>
    </div>

    {{-- ═══════════ TWO PATHS ═══════════ --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

        {{-- Path A — school-arranged --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex flex-col">
            <div class="w-12 h-12 rounded-full bg-primary-light flex items-center justify-center mb-3">
                <i class="fas fa-search text-primary text-xl"></i>
            </div>
            <h3 class="font-bold text-gray-800">Apply for a School-Arranged Placement</h3>
            <p class="text-sm text-gray-500 mt-1 flex-1">
                Browse companies the ILO office has registered for your course.
                Apply to one, get approved, and you'll be placed.
            </p>
            <a href="{{ route('student.attachment.opportunities') }}"
               class="mt-4 inline-flex items-center justify-center px-5 py-2.5 bg-primary hover:bg-primary-dark text-white rounded-lg font-semibold">
                <i class="fas fa-search mr-2"></i> Browse Opportunities
            </a>
        </div>

        {{-- Path B — self-found --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex flex-col">
            <div class="w-12 h-12 rounded-full bg-amber-100 flex items-center justify-center mb-3">
                <i class="fas fa-handshake text-amber-600 text-xl"></i>
            </div>
            <h3 class="font-bold text-gray-800">I Found My Own Placement</h3>
            <p class="text-sm text-gray-500 mt-1 flex-1">
                Already secured attachment with a company that isn't on the ILO list?
                Declare it here — the ILO office will review and approve.
            </p>
            <a href="{{ route('student.attachment.details.create') }}"
               class="mt-4 inline-flex items-center justify-center px-5 py-2.5 bg-amber-600 hover:bg-amber-700 text-white rounded-lg font-semibold">
                <i class="fas fa-edit mr-2"></i> Declare My Attachment
            </a>
        </div>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-5">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <p class="text-sm text-gray-500">Open Opportunities</p>
            <p class="text-2xl font-bold text-primary mt-1">{{ $openCount ?? 0 }}</p>
            <p class="text-xs text-gray-400 mt-1">for your course</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <p class="text-sm text-gray-500">My Applications</p>
            <p class="text-2xl font-bold text-gray-800 mt-1">{{ $stats['total'] ?? 0 }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <p class="text-sm text-gray-500">Pending</p>
            <p class="text-2xl font-bold text-amber-600 mt-1">{{ $stats['pending'] ?? 0 }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <p class="text-sm text-gray-500">Approved</p>
            <p class="text-2xl font-bold text-green-600 mt-1">{{ $stats['approved'] ?? 0 }}</p>
        </div>
    </div>

    {{-- Current enrollment summary --}}
    @if($currentEnrollment)
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h3 class="font-semibold text-gray-800 mb-3 flex items-center">
                <i class="fas fa-graduation-cap text-primary mr-2"></i> Your Course
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                <div>
                    <p class="text-xs text-gray-500">Course</p>
                    <p class="font-medium text-gray-800">{{ $currentEnrollment->course_name }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Code</p>
                    <p class="font-mono text-gray-800">{{ $currentEnrollment->course_code ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Term</p>
                    <p class="font-medium text-gray-800">
                        {{ $currentEnrollment->term->name ?? ($currentEnrollment->intake_month . ' ' . $currentEnrollment->intake_year) }}
                    </p>
                </div>
            </div>
        </div>
    @else
        <div class="bg-amber-50 border border-amber-200 rounded-xl p-6">
            <p class="text-amber-800">
                <i class="fas fa-info-circle mr-2"></i>
                You don't have an active enrollment yet. Attachment opportunities are tied to your course.
            </p>
        </div>
    @endif

    {{-- Recent applications --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="font-semibold text-gray-800 flex items-center">
                <i class="fas fa-file-signature text-primary mr-2"></i> Recent Applications
            </h3>
            <a href="{{ route('student.attachment.applications') }}"
               class="text-sm text-primary hover:underline">View all →</a>
        </div>
        <div class="divide-y divide-gray-100">
            @forelse($applications as $app)
                <div class="px-6 py-4 flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <p class="font-medium text-gray-800 truncate">
                            {{ $app->opportunity->company->name ?? '—' }}
                        </p>
                        <p class="text-xs text-gray-500 truncate">
                            {{ $app->opportunity->course->name ?? '—' }}
                            @if($app->opportunity->term) · {{ $app->opportunity->term->name }} @endif
                        </p>
                        <p class="text-xs text-gray-400 mt-1">
                            Applied {{ $app->created_at?->format('d M Y') }}
                        </p>
                    </div>
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
                    <span class="shrink-0 text-xs px-2 py-1 rounded-full font-medium {{ $badge }}">
                        {{ ucfirst($app->status) }}
                    </span>
                </div>
            @empty
                <div class="px-6 py-12 text-center text-gray-500">
                    <i class="fas fa-inbox text-3xl text-gray-300 mb-2 block"></i>
                    <p>You haven't applied to any opportunities yet.</p>
                    <a href="{{ route('student.attachment.opportunities') }}"
                       class="mt-4 inline-block px-5 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg font-semibold">
                        <i class="fas fa-search mr-2"></i> Browse Opportunities
                    </a>
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection