@extends('ktvtc.ilo.layout.ilolayout')

@section('title', 'Opportunity Detail')
@section('subtitle', ($opportunity->company->name ?? '') . ' · ' . ($opportunity->course->name ?? ''))

@section('header-actions')
<div class="flex gap-2">
    @if($opportunity->status === 'draft')
        <form action="{{ route('ilo.opportunities.publish', $opportunity) }}" method="POST" class="inline">
            @csrf
            <button type="submit" class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium transition">
                <i class="fas fa-rocket mr-2"></i> Publish
            </button>
        </form>
    @elseif($opportunity->status === 'open')
        <form action="{{ route('ilo.opportunities.close', $opportunity) }}" method="POST" class="inline"
              onsubmit="return confirm('Close this opportunity? Students will no longer be able to apply.')">
            @csrf
            <button type="submit" class="inline-flex items-center px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white rounded-lg font-medium transition">
                <i class="fas fa-lock mr-2"></i> Close
            </button>
        </form>
    @endif
    <a href="{{ route('ilo.opportunities.edit', $opportunity) }}"
       class="inline-flex items-center px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 font-medium">
        <i class="fas fa-edit mr-2"></i> Edit
    </a>
</div>
@endsection

@section('content')
{{-- Header card --}}
<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6">
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div class="flex-1 min-w-0">
            <div class="flex items-center gap-3 mb-3">
                @php
                    $badge = match($opportunity->status) {
                        'open'   => 'bg-green-100 text-green-700',
                        'closed' => 'bg-red-100 text-red-700',
                        'filled' => 'bg-blue-100 text-blue-700',
                        default  => 'bg-gray-100 text-gray-700',
                    };
                @endphp
                <span class="text-xs px-3 py-1 rounded-full font-semibold {{ $badge }}">
                    {{ ucfirst($opportunity->status) }}
                </span>
                @if($opportunity->is_paid)
                    <span class="text-xs px-3 py-1 rounded-full bg-emerald-50 text-emerald-700 font-medium">
                        <i class="fas fa-money-bill mr-1"></i>Paid
                    </span>
                @endif
                @if($opportunity->provides_transport)
                    <span class="text-xs px-3 py-1 rounded-full bg-blue-50 text-blue-700 font-medium">
                        <i class="fas fa-bus mr-1"></i>Transport
                    </span>
                @endif
            </div>

            <h2 class="text-2xl font-bold text-gray-800">
                {{ $opportunity->title ?? 'Attachment Opportunity' }}
            </h2>
            <p class="text-gray-500 mt-1">
                {{ $opportunity->company->name ?? '—' }} · {{ $opportunity->course->name ?? '—' }}
                @if($opportunity->course?->code) <span class="font-mono text-xs text-gray-400">({{ $opportunity->course->code }})</span> @endif
            </p>
        </div>

        <div class="text-right">
            <p class="text-xs text-gray-500">Slots remaining</p>
            <p class="text-3xl font-bold text-primary">{{ $opportunity->slotsRemaining() }}</p>
            <p class="text-xs text-gray-500">of {{ $opportunity->slots }} offered</p>
        </div>
    </div>

    {{-- Key dates --}}
    <div class="mt-6 pt-6 border-t border-gray-100 grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
        <div>
            <p class="text-xs text-gray-500">Application Opens</p>
            <p class="font-medium text-gray-800">
                {{ $opportunity->application_opens_at?->format('d M Y') ?? '—' }}
            </p>
        </div>
        <div>
            <p class="text-xs text-gray-500">Application Closes</p>
            <p class="font-medium {{ $opportunity->application_closes_at && $opportunity->application_closes_at->isPast() ? 'text-red-600' : 'text-gray-800' }}">
                {{ $opportunity->application_closes_at?->format('d M Y') ?? '—' }}
            </p>
        </div>
        <div>
            <p class="text-xs text-gray-500">Attachment Starts</p>
            <p class="font-medium text-gray-800">
                {{ $opportunity->start_date?->format('d M Y') ?? '—' }}
            </p>
        </div>
        <div>
            <p class="text-xs text-gray-500">Attachment Ends</p>
            <p class="font-medium text-gray-800">
                {{ $opportunity->end_date?->format('d M Y') ?? '—' }}
            </p>
        </div>
    </div>

    {{-- Descriptions --}}
    @if($opportunity->description || $opportunity->requirements || $opportunity->location)
        <div class="mt-6 pt-6 border-t border-gray-100 grid grid-cols-1 md:grid-cols-2 gap-6 text-sm">
            @if($opportunity->description)
                <div class="md:col-span-2">
                    <p class="text-xs text-gray-500 mb-1">Description</p>
                    <p class="text-gray-800">{{ $opportunity->description }}</p>
                </div>
            @endif
            @if($opportunity->requirements)
                <div class="md:col-span-2">
                    <p class="text-xs text-gray-500 mb-1">Requirements</p>
                    <p class="text-gray-800">{{ $opportunity->requirements }}</p>
                </div>
            @endif
            @if($opportunity->location)
                <div>
                    <p class="text-xs text-gray-500 mb-1">Location</p>
                    <p class="text-gray-800"><i class="fas fa-map-marker-alt text-primary mr-1"></i>{{ $opportunity->location }}</p>
                </div>
            @endif
            @if($opportunity->term)
                <div>
                    <p class="text-xs text-gray-500 mb-1">Term</p>
                    <p class="text-gray-800">{{ $opportunity->term->name }}</p>
                </div>
            @endif
        </div>
    @endif
</div>

{{-- Stats --}}
<div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
        <p class="text-xs text-gray-500">Applications</p>
        <p class="text-xl font-bold text-gray-800 mt-1">{{ $stats['applications'] ?? 0 }}</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
        <p class="text-xs text-gray-500">Pending</p>
        <p class="text-xl font-bold text-amber-600 mt-1">{{ $stats['pending'] ?? 0 }}</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
        <p class="text-xs text-gray-500">Approved</p>
        <p class="text-xl font-bold text-green-600 mt-1">{{ $stats['approved'] ?? 0 }}</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
        <p class="text-xs text-gray-500">Rejected</p>
        <p class="text-xl font-bold text-red-600 mt-1">{{ $stats['rejected'] ?? 0 }}</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
        <p class="text-xs text-gray-500">Remaining Slots</p>
        <p class="text-xl font-bold text-primary mt-1">{{ $stats['remaining'] ?? 0 }}</p>
    </div>
</div>

{{-- Applications --}}
<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
        <h3 class="font-semibold text-gray-800">
            <i class="fas fa-users text-primary mr-2"></i> Applications
        </h3>
        <a href="{{ route('ilo.applications.index', ['opportunity_id' => $opportunity->id]) }}"
           class="text-sm text-primary hover:underline">Filter in Applications →</a>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Student</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Course</th>
                    <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Applied</th>
                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($applications as $app)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">
                            <a href="{{ route('ilo.applications.show', $app) }}" class="font-medium text-gray-800 hover:text-primary">
                                {{ $app->student->full_name ?? '—' }}
                            </a>
                            <p class="text-xs text-gray-500">{{ $app->student->student_number ?? '' }}</p>
                        </td>
                        <td class="px-4 py-3 text-gray-600">
                            {{ $app->student->course_name ?? ($opportunity->course->name ?? '—') }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            @php
                                $b = match($app->status) {
                                    'pending'     => 'bg-amber-100 text-amber-700',
                                    'shortlisted' => 'bg-blue-100 text-blue-700',
                                    'approved'    => 'bg-green-100 text-green-700',
                                    'rejected'    => 'bg-red-100 text-red-700',
                                    default       => 'bg-gray-100 text-gray-700',
                                };
                            @endphp
                            <span class="text-xs px-2 py-1 rounded-full {{ $b }}">{{ ucfirst($app->status) }}</span>
                        </td>
                        <td class="px-4 py-3 text-right text-xs text-gray-500">
                            {{ $app->created_at?->format('d M Y') }}
                        </td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('ilo.applications.show', $app) }}" class="text-primary hover:underline text-xs">
                                Review
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-8 text-center text-gray-500 text-sm">
                            <i class="fas fa-inbox text-2xl text-gray-300 mb-2 block"></i>
                            No applications yet.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($applications->hasPages())
        <div class="px-6 py-4 border-t border-gray-100 bg-gray-50">
            {{ $applications->appends(request()->query())->links() }}
        </div>
    @endif
</div>
@endsection