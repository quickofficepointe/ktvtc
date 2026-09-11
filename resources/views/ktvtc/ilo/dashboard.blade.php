@extends('ktvtc.ilo.layout.ilolayout')

@section('title', 'ILO Dashboard')
@section('subtitle', 'Industrial attachment coordination at a glance')

@section('header-actions')
<a href="{{ route('ilo.companies.create') }}"
   class="inline-flex items-center px-4 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg font-medium transition">
    <i class="fas fa-plus mr-2"></i> Register Company
</a>
@endsection

@section('content')
{{-- STATS --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-5 mb-6">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Companies</p>
                <p class="text-2xl font-bold text-gray-800 mt-1">{{ $stats['companies'] ?? 0 }}</p>
                <p class="text-xs text-gray-400 mt-1">{{ $stats['active_companies'] ?? 0 }} active</p>
            </div>
            <div class="w-11 h-11 rounded-lg bg-primary-light flex items-center justify-center">
                <i class="fas fa-building text-primary"></i>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Open Opportunities</p>
                <p class="text-2xl font-bold text-gray-800 mt-1">{{ $stats['open_opportunities'] ?? 0 }}</p>
                <p class="text-xs text-gray-400 mt-1">{{ $stats['slots_offered'] ?? 0 }} slots offered</p>
            </div>
            <div class="w-11 h-11 rounded-lg bg-blue-50 flex items-center justify-center">
                <i class="fas fa-bullhorn text-blue-600"></i>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Pending Applications</p>
                <p class="text-2xl font-bold text-amber-600 mt-1">{{ $stats['pending_applications'] ?? 0 }}</p>
                <p class="text-xs text-gray-400 mt-1">{{ $stats['approved_applications'] ?? 0 }} approved</p>
            </div>
            <div class="w-11 h-11 rounded-lg bg-amber-50 flex items-center justify-center">
                <i class="fas fa-hourglass-half text-amber-600"></i>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Slots Filled</p>
                <p class="text-2xl font-bold text-green-600 mt-1">{{ $stats['slots_filled'] ?? 0 }}</p>
                <p class="text-xs text-gray-400 mt-1">of {{ $stats['slots_offered'] ?? 0 }} offered</p>
            </div>
            <div class="w-11 h-11 rounded-lg bg-green-50 flex items-center justify-center">
                <i class="fas fa-user-check text-green-600"></i>
            </div>
        </div>
    </div>
</div>

{{-- QUICK ACTIONS --}}
<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 mb-6">
    <div class="flex flex-wrap gap-3">
        <a href="{{ route('ilo.companies.create') }}"
           class="inline-flex items-center px-4 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg font-medium transition">
            <i class="fas fa-plus mr-2"></i> Register Company
        </a>
        <a href="{{ route('ilo.opportunities.create') }}"
           class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition">
            <i class="fas fa-plus mr-2"></i> Create Opportunity
        </a>
        <a href="{{ route('ilo.applications.index') }}"
           class="inline-flex items-center px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 font-medium transition">
            <i class="fas fa-inbox mr-2"></i> Review Applications
        </a>
    </div>
</div>

{{-- TWO COLUMNS --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

    {{-- Open opportunities --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="font-semibold text-gray-800"><i class="fas fa-bullhorn text-primary mr-2"></i> Open Opportunities</h3>
            <a href="{{ route('ilo.opportunities.index') }}" class="text-primary text-sm hover:underline">View all</a>
        </div>
        <div class="divide-y divide-gray-100">
            @forelse($openOpportunities as $op)
                <a href="{{ route('ilo.opportunities.show', $op) }}" class="block px-5 py-3 hover:bg-gray-50">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="font-medium text-gray-800 truncate">{{ $op->company->name ?? 'Company' }}</p>
                            <p class="text-xs text-gray-500">{{ $op->course->name ?? 'Course' }}</p>
                            @if($op->application_closes_at)
                                <p class="text-xs text-amber-600 mt-1">
                                    <i class="fas fa-clock mr-1"></i>Closes {{ $op->application_closes_at->format('d M Y') }}
                                </p>
                            @endif
                        </div>
                        <span class="shrink-0 text-xs px-2 py-1 rounded-full bg-primary-light text-primary font-medium">
                            {{ $op->slotsRemaining() }}/{{ $op->slots }} slots
                        </span>
                    </div>
                </a>
            @empty
                <div class="p-8 text-center text-gray-500 text-sm">
                    <i class="fas fa-inbox text-2xl text-gray-300 mb-2 block"></i>
                    No open opportunities yet.
                </div>
            @endforelse
        </div>
    </div>

    {{-- Recent applications --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="font-semibold text-gray-800"><i class="fas fa-file-signature text-primary mr-2"></i> Recent Applications</h3>
            <a href="{{ route('ilo.applications.index') }}" class="text-primary text-sm hover:underline">View all</a>
        </div>
        <div class="divide-y divide-gray-100">
            @forelse($recentApplications as $app)
                <a href="{{ route('ilo.applications.show', $app) }}" class="block px-5 py-3 hover:bg-gray-50">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="font-medium text-gray-800 truncate">
                                {{ $app->student->full_name ?? 'Student' }}
                            </p>
                            <p class="text-xs text-gray-500 truncate">
                                {{ $app->opportunity->company->name ?? '—' }} · {{ $app->opportunity->course->name ?? '—' }}
                            </p>
                        </div>
                        @php
                            $badge = match($app->status) {
                                'pending'     => 'bg-amber-100 text-amber-700',
                                'shortlisted' => 'bg-blue-100 text-blue-700',
                                'approved'    => 'bg-green-100 text-green-700',
                                'rejected'    => 'bg-red-100 text-red-700',
                                default       => 'bg-gray-100 text-gray-700',
                            };
                        @endphp
                        <span class="shrink-0 text-xs px-2 py-1 rounded-full {{ $badge }}">
                            {{ ucfirst($app->status) }}
                        </span>
                    </div>
                </a>
            @empty
                <div class="p-8 text-center text-gray-500 text-sm">
                    <i class="fas fa-inbox text-2xl text-gray-300 mb-2 block"></i>
                    No applications yet.
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection