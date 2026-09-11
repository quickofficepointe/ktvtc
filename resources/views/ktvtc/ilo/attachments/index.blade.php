@extends('ktvtc.ilo.layout.ilolayout')

@section('title', 'Attachments')
@section('subtitle', 'Students on industrial attachment')

@section('content')

{{-- ═══════════ STATS ═══════════ --}}
<div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-6">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
        <p class="text-xs text-gray-500">Awaiting Review</p>
        <p class="text-2xl font-bold text-blue-600 mt-1">{{ $stats['submitted'] ?? 0 }}</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
        <p class="text-xs text-gray-500">Needs Revision</p>
        <p class="text-2xl font-bold text-amber-600 mt-1">{{ $stats['needs_revision'] ?? 0 }}</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
        <p class="text-xs text-gray-500">Active</p>
        <p class="text-2xl font-bold text-green-600 mt-1">{{ $stats['active'] ?? 0 }}</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
        <p class="text-xs text-gray-500">Completed</p>
        <p class="text-2xl font-bold text-purple-600 mt-1">{{ $stats['completed'] ?? 0 }}</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
        <p class="text-xs text-gray-500">Registered</p>
        <p class="text-2xl font-bold text-primary mt-1">{{ $stats['registered'] ?? 0 }}</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
        <p class="text-xs text-gray-500">Self-Found</p>
        <p class="text-2xl font-bold text-amber-600 mt-1">{{ $stats['self_found'] ?? 0 }}</p>
    </div>
</div>

{{-- ═══════════ FILTERS ═══════════ --}}
<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-6">
    <form method="GET" action="{{ route('ilo.attachments.index') }}" class="flex flex-wrap items-end gap-3">

        <div class="flex-1 min-w-[200px]">
            <label class="block text-xs font-semibold text-gray-600 mb-1">Search</label>
            <div class="relative">
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Student name, student number, company, supervisor..."
                       class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
            </div>
        </div>

        <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1">Status</label>
            <select name="status" class="px-4 py-2 border border-gray-300 rounded-lg">
                <option value="">All Active</option>
                <option value="draft"          {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                <option value="submitted"      {{ request('status') === 'submitted' ? 'selected' : '' }}>Submitted</option>
                <option value="needs_revision" {{ request('status') === 'needs_revision' ? 'selected' : '' }}>Needs Revision</option>
                <option value="active"         {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                <option value="completed"      {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                <option value="graded"         {{ request('status') === 'graded' ? 'selected' : '' }}>Graded</option>
                <option value="rejected"       {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
            </select>
        </div>

        <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1">Company Type</label>
            <select name="company_type" class="px-4 py-2 border border-gray-300 rounded-lg">
                <option value="">All Companies</option>
                <option value="registered" {{ request('company_type') === 'registered' ? 'selected' : '' }}>
                    Registered ({{ $stats['registered'] ?? 0 }})
                </option>
                <option value="self"       {{ request('company_type') === 'self' ? 'selected' : '' }}>
                    Self-found ({{ $stats['self_found'] ?? 0 }})
                </option>
            </select>
        </div>

        <div class="flex items-center gap-2">
            <button type="submit" class="px-6 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg font-medium">
                <i class="fas fa-filter mr-2"></i> Filter
            </button>
            <a href="{{ route('ilo.attachments.index') }}"
               class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                <i class="fas fa-times"></i>
            </a>
        </div>
    </form>
</div>

{{-- ═══════════ TABLE ═══════════ --}}
<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">

    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Student</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Company</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Period</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Supervisor</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($attachments as $attachment)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">
                            <a href="{{ route('ilo.attachments.show', $attachment) }}"
                               class="font-medium text-gray-800 hover:text-primary">
                                {{ $attachment->student->full_name ?? '—' }}
                            </a>
                            <p class="text-xs text-gray-500 font-mono">
                                {{ $attachment->student->student_number ?? '' }}
                            </p>
                        </td>

                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                <span class="font-medium text-gray-800 truncate max-w-[180px]">
                                    {{ $attachment->company_name ?? '—' }}
                                </span>

                                {{-- Registered vs. Self-found badge --}}
                                @if(is_null($attachment->company_id))
                                    <span class="text-xs px-2 py-0.5 bg-amber-50 text-amber-700 rounded-full shrink-0">
                                        <i class="fas fa-user mr-1"></i> Self-found
                                    </span>
                                @else
                                    <span class="text-xs px-2 py-0.5 bg-primary-light text-primary rounded-full shrink-0">
                                        <i class="fas fa-star mr-1"></i> Registered
                                    </span>
                                @endif
                            </div>
                            @if($attachment->company_sector)
                                <p class="text-xs text-gray-500 mt-0.5">{{ $attachment->company_sector }}</p>
                            @endif
                        </td>

                        <td class="px-4 py-3 text-sm text-gray-600">
                            @if($attachment->start_date && $attachment->end_date)
                                <div>{{ $attachment->start_date->format('d M Y') }}</div>
                                <div class="text-xs text-gray-400">→ {{ $attachment->end_date->format('d M Y') }}</div>
                                @if($attachment->duration_weeks)
                                    <div class="text-xs text-gray-400">{{ $attachment->duration_weeks }} weeks</div>
                                @endif
                            @else
                                <span class="text-gray-400">—</span>
                            @endif
                        </td>

                        <td class="px-4 py-3 text-sm">
                            @if($attachment->supervisor_name)
                                <div class="text-gray-800">{{ $attachment->supervisor_name }}</div>
                                @if($attachment->supervisor_phone)
                                    <div class="text-xs text-gray-500">{{ $attachment->supervisor_phone }}</div>
                                @endif
                            @else
                                <span class="text-gray-400">—</span>
                            @endif
                        </td>

                        <td class="px-4 py-3 text-center">
                            @php
                                $badge = match($attachment->status) {
                                    'draft'          => 'bg-gray-100 text-gray-700',
                                    'submitted'      => 'bg-blue-100 text-blue-700',
                                    'needs_revision' => 'bg-amber-100 text-amber-700',
                                    'active'         => 'bg-green-100 text-green-700',
                                    'completed'      => 'bg-purple-100 text-purple-700',
                                    'graded'         => 'bg-emerald-100 text-emerald-700',
                                    'rejected'       => 'bg-red-100 text-red-700',
                                    default          => 'bg-gray-100 text-gray-700',
                                };
                            @endphp
                            <span class="text-xs px-2 py-1 rounded-full font-medium {{ $badge }}">
                                {{ $attachment->status_label ?? ucfirst($attachment->status) }}
                            </span>
                        </td>

                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('ilo.attachments.show', $attachment) }}"
                               class="p-2 text-gray-600 hover:text-primary hover:bg-gray-100 rounded-lg inline-flex"
                               title="Review">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-12 text-center">
                            <i class="fas fa-inbox text-5xl text-gray-300 mb-3"></i>
                            <p class="text-lg font-semibold text-gray-600">No attachments found</p>
                            <p class="text-sm text-gray-400 mt-1">
                                @if(request('company_type') === 'self')
                                    No self-found placements submitted yet.
                                @elseif(request('status'))
                                    No attachments with status "{{ request('status') }}".
                                @else
                                    Students haven't submitted any attachment details yet.
                                @endif
                            </p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($attachments->hasPages())
        <div class="px-6 py-4 border-t border-gray-100 bg-gray-50">
            {{ $attachments->appends(request()->query())->links() }}
        </div>
    @endif
</div>

@endsection