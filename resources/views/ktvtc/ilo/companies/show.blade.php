@extends('ktvtc.ilo.layout.ilolayout')

@section('title', $company->name)
@section('subtitle', $company->sector ?? 'Company Detail')

@section('header-actions')
<div class="flex gap-2">
    <a href="{{ route('ilo.opportunities.create', ['company_id' => $company->id]) }}"
       class="inline-flex items-center px-4 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg font-medium transition">
        <i class="fas fa-plus mr-2"></i> New Opportunity
    </a>
    <a href="{{ route('ilo.companies.edit', $company) }}"
       class="inline-flex items-center px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 font-medium">
        <i class="fas fa-edit mr-2"></i> Edit
    </a>
</div>
@endsection

@section('content')
{{-- Header --}}
<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6">
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div class="flex-1 min-w-0">
            <div class="flex items-center gap-3">
                <div class="w-14 h-14 rounded-xl bg-primary-light flex items-center justify-center">
                    <i class="fas fa-building text-primary text-2xl"></i>
                </div>
                <div>
                    <h2 class="text-2xl font-bold text-gray-800">{{ $company->name }}</h2>
                    <p class="text-sm text-gray-500">{{ $company->sector ?? 'Uncategorized' }}</p>
                </div>
            </div>

            <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                @if($company->full_address)
                    <div>
                        <p class="text-xs text-gray-500">Address</p>
                        <p class="font-medium text-gray-800">{{ $company->full_address }}</p>
                    </div>
                @endif
                @if($company->company_phone)
                    <div>
                        <p class="text-xs text-gray-500">Phone</p>
                        <p class="font-medium text-gray-800">{{ $company->company_phone }}</p>
                    </div>
                @endif
                @if($company->website)
                    <div>
                        <p class="text-xs text-gray-500">Website</p>
                        <a href="{{ $company->website }}" target="_blank" class="font-medium text-primary hover:underline">
                            {{ $company->website }}
                        </a>
                    </div>
                @endif
            </div>
        </div>

        <div>
            @if($company->is_active)
                <span class="text-xs px-3 py-1 rounded-full bg-green-100 text-green-700 font-semibold">Active</span>
            @else
                <span class="text-xs px-3 py-1 rounded-full bg-gray-100 text-gray-600 font-semibold">Inactive</span>
            @endif
        </div>
    </div>

    @if($company->contact_person_name)
        <div class="mt-6 pt-6 border-t border-gray-100">
            <h3 class="text-sm font-semibold text-gray-800 mb-3"><i class="fas fa-user-tie text-primary mr-2"></i> Primary Contact</h3>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 text-sm">
                <div>
                    <p class="text-xs text-gray-500">Name</p>
                    <p class="font-medium text-gray-800">{{ $company->contact_person_name }}</p>
                </div>
                @if($company->contact_person_position)
                    <div>
                        <p class="text-xs text-gray-500">Position</p>
                        <p class="font-medium text-gray-800">{{ $company->contact_person_position }}</p>
                    </div>
                @endif
                @if($company->contact_person_phone)
                    <div>
                        <p class="text-xs text-gray-500">Phone</p>
                        <p class="font-medium text-gray-800">{{ $company->contact_person_phone }}</p>
                    </div>
                @endif
                @if($company->contact_person_email)
                    <div>
                        <p class="text-xs text-gray-500">Email</p>
                        <a href="mailto:{{ $company->contact_person_email }}" class="font-medium text-primary hover:underline">
                            {{ $company->contact_person_email }}
                        </a>
                    </div>
                @endif
            </div>
        </div>
    @endif
</div>

{{-- Stats --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-5 mb-6">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
        <p class="text-xs text-gray-500">Total Opportunities</p>
        <p class="text-xl font-bold text-gray-800 mt-1">{{ $stats['opportunities'] ?? 0 }}</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
        <p class="text-xs text-gray-500">Open</p>
        <p class="text-xl font-bold text-green-600 mt-1">{{ $stats['open'] ?? 0 }}</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
        <p class="text-xs text-gray-500">Applications</p>
        <p class="text-xl font-bold text-primary mt-1">{{ $stats['applications'] ?? 0 }}</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
        <p class="text-xs text-gray-500">Approved</p>
        <p class="text-xl font-bold text-green-600 mt-1">{{ $stats['approved'] ?? 0 }}</p>
    </div>
</div>

{{-- Opportunities --}}
<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-6">
    <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
        <h3 class="font-semibold text-gray-800"><i class="fas fa-bullhorn text-primary mr-2"></i> Opportunities</h3>
        <a href="{{ route('ilo.opportunities.create', ['company_id' => $company->id]) }}" class="text-sm text-primary hover:underline">
            <i class="fas fa-plus mr-1"></i> Add
        </a>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Course</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Term</th>
                    <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">Slots</th>
                    <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($company->opportunities as $op)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">{{ $op->course->name ?? '—' }}</td>
                        <td class="px-4 py-3">{{ $op->term->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-center">
                            <span class="text-xs">{{ $op->slots_taken }}/{{ $op->slots }}</span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            @php
                                $badge = match($op->status) {
                                    'open'   => 'bg-green-100 text-green-700',
                                    'closed' => 'bg-red-100 text-red-700',
                                    'filled' => 'bg-blue-100 text-blue-700',
                                    default  => 'bg-gray-100 text-gray-700',
                                };
                            @endphp
                            <span class="text-xs px-2 py-1 rounded-full {{ $badge }}">{{ ucfirst($op->status) }}</span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('ilo.opportunities.show', $op) }}" class="text-primary hover:underline text-xs">View</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-8 text-center text-gray-500 text-sm">
                            No opportunities yet for this company.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Recent applications --}}
<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-100">
        <h3 class="font-semibold text-gray-800"><i class="fas fa-file-signature text-primary mr-2"></i> Recent Applications</h3>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Student</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Course</th>
                    <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Applied</th>
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
                        <td class="px-4 py-3">{{ $app->opportunity->course->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-center">
                            @php
                                $badge = match($app->status) {
                                    'pending'     => 'bg-amber-100 text-amber-700',
                                    'shortlisted' => 'bg-blue-100 text-blue-700',
                                    'approved'    => 'bg-green-100 text-green-700',
                                    'rejected'    => 'bg-red-100 text-red-700',
                                    default       => 'bg-gray-100 text-gray-700',
                                };
                            @endphp
                            <span class="text-xs px-2 py-1 rounded-full {{ $badge }}">{{ ucfirst($app->status) }}</span>
                        </td>
                        <td class="px-4 py-3 text-right text-xs text-gray-500">
                            {{ $app->created_at?->format('d M Y') }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-8 text-center text-gray-500 text-sm">
                            No applications yet.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection