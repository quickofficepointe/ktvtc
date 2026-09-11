@extends('ktvtc.students.layout.studentlayout')

@section('title', 'Attachment Opportunities')

@section('content')
<div class="max-w-6xl mx-auto space-y-6">

    {{-- Header --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-800 flex items-center">
                    <i class="fas fa-search text-primary mr-3"></i>
                    Attachment Opportunities
                </h1>
                <p class="text-gray-500 mt-1">
                    Opportunities available for
                    <strong>{{ $currentEnrollment->course_name ?? 'your course' }}</strong>.
                </p>
            </div>
            <a href="{{ route('student.attachment.index') }}"
               class="inline-flex items-center px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 font-medium">
                <i class="fas fa-arrow-left mr-2"></i> Back
            </a>
        </div>
    </div>

    {{-- Search --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
        <form method="GET" action="{{ route('student.attachment.opportunities') }}" class="flex gap-3">
            <div class="relative flex-1">
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Search by company, title or location..."
                       class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
            </div>
            <button type="submit" class="px-6 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg font-medium">
                <i class="fas fa-search mr-2"></i> Search
            </button>
            @if(request('search'))
                <a href="{{ route('student.attachment.opportunities') }}"
                   class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                    <i class="fas fa-times"></i>
                </a>
            @endif
        </form>
    </div>

    {{-- Opportunity cards --}}
    @if($opportunities->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            @foreach($opportunities as $op)
                @php $alreadyApplied = in_array($op->id, $appliedIds); @endphp
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex flex-col card-hover">
                    <div class="flex items-start justify-between gap-3 mb-3">
                        <div class="min-w-0">
                            <h3 class="font-bold text-lg text-gray-800 truncate">
                                {{ $op->company->name ?? 'Company' }}
                            </h3>
                            <p class="text-sm text-gray-500">
                                {{ $op->course->name ?? '' }}
                                @if($op->course?->code)
                                    <span class="font-mono text-xs">({{ $op->course->code }})</span>
                                @endif
                            </p>
                        </div>
                        <div class="shrink-0 text-right">
                            <p class="text-xs text-gray-500">Slots</p>
                            <p class="text-lg font-bold text-primary">{{ $op->slotsRemaining() }}/{{ $op->slots }}</p>
                        </div>
                    </div>

                    @if($op->title)
                        <p class="text-sm text-gray-700 mb-2 font-medium">{{ $op->title }}</p>
                    @endif

                    @if($op->description)
                        <p class="text-xs text-gray-500 mb-3 line-clamp-2">
                            {{ Str::limit($op->description, 140) }}
                        </p>
                    @endif

                    <div class="flex flex-wrap gap-2 mb-3 text-xs">
                        @if($op->location)
                            <span class="px-2 py-0.5 rounded bg-gray-100 text-gray-700">
                                <i class="fas fa-map-marker-alt mr-1"></i>{{ $op->location }}
                            </span>
                        @endif
                        @if($op->is_paid)
                            <span class="px-2 py-0.5 rounded bg-emerald-50 text-emerald-700">
                                <i class="fas fa-money-bill mr-1"></i>Paid
                            </span>
                        @endif
                        @if($op->provides_transport)
                            <span class="px-2 py-0.5 rounded bg-blue-50 text-blue-700">
                                <i class="fas fa-bus mr-1"></i>Transport
                            </span>
                        @endif
                    </div>

                    <div class="grid grid-cols-2 gap-3 text-xs text-gray-600 mb-4 mt-auto pt-3 border-t border-gray-100">
                        <div>
                            <p class="text-gray-400">Attachment</p>
                            <p class="font-medium">
                                {{ $op->start_date?->format('d M') ?? '—' }}
                                → {{ $op->end_date?->format('d M Y') ?? '—' }}
                            </p>
                        </div>
                        <div>
                            <p class="text-gray-400">Apply by</p>
                            <p class="font-medium
                                {{ $op->application_closes_at && $op->application_closes_at->isPast() ? 'text-red-600' : '' }}">
                                {{ $op->application_closes_at?->format('d M Y') ?? 'Open' }}
                            </p>
                        </div>
                    </div>

                    <div class="flex gap-2 mt-auto">
                        <a href="{{ route('student.attachment.opportunities.show', $op) }}"
                           class="flex-1 text-center px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 font-medium text-sm">
                            View Details
                        </a>
                        @if($alreadyApplied)
                            <span class="flex-1 text-center px-4 py-2 bg-blue-50 text-blue-700 rounded-lg font-medium text-sm">
                                <i class="fas fa-check mr-1"></i>Applied
                            </span>
                        @elseif($op->slotsRemaining() <= 0)
                            <span class="flex-1 text-center px-4 py-2 bg-gray-100 text-gray-500 rounded-lg font-medium text-sm">
                                Full
                            </span>
                        @else
                            <a href="{{ route('student.attachment.opportunities.show', $op) }}"
                               class="flex-1 text-center px-4 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg font-semibold text-sm">
                                Apply
                            </a>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        @if($opportunities->hasPages())
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
                {{ $opportunities->appends(request()->query())->links() }}
            </div>
        @endif
    @else
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-12 text-center">
            <i class="fas fa-search text-5xl text-gray-300 mb-3"></i>
            <p class="text-lg font-semibold text-gray-700">No open opportunities right now</p>
            <p class="text-sm text-gray-500 mt-1">
                Check back soon, or contact the ILO office if you have a company in mind.
            </p>
            <a href="{{ route('student.attachment.index') }}"
               class="mt-4 inline-block px-5 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                Back to My Attachment
            </a>
        </div>
    @endif
</div>
@endsection