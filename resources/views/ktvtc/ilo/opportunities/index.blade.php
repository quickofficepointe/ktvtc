@extends('ktvtc.ilo.layout.ilolayout')

@section('title', 'Opportunities')
@section('subtitle', 'Attachment opportunities offered by host companies')

@section('header-actions')
<a href="{{ route('ilo.opportunities.create') }}"
   class="inline-flex items-center px-4 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg font-medium transition">
    <i class="fas fa-plus mr-2"></i> New Opportunity
</a>
@endsection

@section('content')
{{-- Stats --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-5 mb-6">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
        <p class="text-sm text-gray-500">Total</p>
        <p class="text-2xl font-bold text-gray-800 mt-1">{{ $stats['total'] ?? 0 }}</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
        <p class="text-sm text-gray-500">Open</p>
        <p class="text-2xl font-bold text-green-600 mt-1">{{ $stats['open'] ?? 0 }}</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
        <p class="text-sm text-gray-500">Filled</p>
        <p class="text-2xl font-bold text-blue-600 mt-1">{{ $stats['filled'] ?? 0 }}</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
        <p class="text-sm text-gray-500">Draft</p>
        <p class="text-2xl font-bold text-gray-500 mt-1">{{ $stats['draft'] ?? 0 }}</p>
    </div>
</div>

{{-- Filters --}}
<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-6">
    <form method="GET" action="{{ route('ilo.opportunities.index') }}"
          class="grid grid-cols-1 md:grid-cols-12 gap-3 items-end">

        <div class="md:col-span-4">
            <label class="block text-xs font-semibold text-gray-600 mb-1">Search</label>
            <div class="relative">
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Company, course, location..."
                       class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
            </div>
        </div>

        <div class="md:col-span-3">
            <label class="block text-xs font-semibold text-gray-600 mb-1">Company</label>
            <select name="company_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                <option value="">All Companies</option>
                @foreach($companies as $c)
                    <option value="{{ $c->id }}" {{ request('company_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="md:col-span-3">
            <label class="block text-xs font-semibold text-gray-600 mb-1">Course</label>
            <select name="course_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                <option value="">All Courses</option>
                @foreach($courses as $c)
                    <option value="{{ $c->id }}" {{ request('course_id') == $c->id ? 'selected' : '' }}>
                        {{ $c->name }} @if($c->code) ({{ $c->code }}) @endif
                    </option>
                @endforeach
            </select>
        </div>

        <div class="md:col-span-2">
            <label class="block text-xs font-semibold text-gray-600 mb-1">Status</label>
            <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                <option value="">All</option>
                @foreach(['draft','open','closed','filled'] as $s)
                    <option value="{{ $s }}" {{ request('status') == $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                @endforeach
            </select>
        </div>

        <div class="md:col-span-12 flex gap-2 justify-end">
            <a href="{{ route('ilo.opportunities.index') }}" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 text-sm">
                Reset
            </a>
            <button type="submit" class="px-6 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg font-medium text-sm">
                <i class="fas fa-search mr-2"></i> Filter
            </button>
        </div>
    </form>
</div>

{{-- Table --}}
<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Opportunity</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Course</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Term</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Slots</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Closes</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($opportunities as $op)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">
                            <a href="{{ route('ilo.opportunities.show', $op) }}"
                               class="font-medium text-gray-800 hover:text-primary">
                                {{ $op->company->name ?? '—' }}
                            </a>
                            @if($op->title)
                                <p class="text-xs text-gray-500">{{ $op->title }}</p>
                            @endif
                            @if($op->location)
                                <p class="text-xs text-gray-400"><i class="fas fa-map-marker-alt mr-1"></i>{{ $op->location }}</p>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-sm">
                            {{ $op->course->name ?? '—' }}
                            @if($op->course?->code)
                                <span class="text-xs text-gray-400 font-mono block">{{ $op->course->code }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-600">{{ $op->term->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-center">
                            <span class="text-xs">{{ $op->slots_taken }}/{{ $op->slots }}</span>
                            <div class="w-full bg-gray-200 rounded-full h-1.5 mt-1">
                                @php
                                    $fill = $op->slots > 0 ? min(100, ($op->slots_taken / $op->slots) * 100) : 0;
                                @endphp
                                <div class="h-1.5 rounded-full {{ $fill >= 100 ? 'bg-blue-500' : 'bg-primary' }}"
                                     style="width: {{ $fill }}%"></div>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-center text-xs">
                            @if($op->application_closes_at)
                                @if($op->application_closes_at->isPast())
                                    <span class="text-red-600">Closed</span>
                                @else
                                    <span class="text-gray-600">{{ $op->application_closes_at->format('d M Y') }}</span>
                                @endif
                            @else
                                <span class="text-gray-400">—</span>
                            @endif
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
                            <div class="flex items-center justify-end gap-1">
                                <a href="{{ route('ilo.opportunities.show', $op) }}"
                                   class="p-2 text-gray-600 hover:text-primary hover:bg-gray-100 rounded-lg" title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('ilo.opportunities.edit', $op) }}"
                                   class="p-2 text-gray-600 hover:text-blue-600 hover:bg-gray-100 rounded-lg" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @if($op->status === 'draft')
                                    <form action="{{ route('ilo.opportunities.publish', $op) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="p-2 text-green-600 hover:bg-gray-100 rounded-lg" title="Publish">
                                            <i class="fas fa-rocket"></i>
                                        </button>
                                    </form>
                                @elseif($op->status === 'open')
                                    <form action="{{ route('ilo.opportunities.close', $op) }}" method="POST" class="inline"
                                          onsubmit="return confirm('Close this opportunity?')">
                                        @csrf
                                        <button type="submit" class="p-2 text-amber-600 hover:bg-gray-100 rounded-lg" title="Close">
                                            <i class="fas fa-lock"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-12 text-center">
                            <i class="fas fa-bullhorn text-5xl text-gray-300 mb-3"></i>
                            <p class="text-lg font-semibold text-gray-600">No opportunities yet</p>
                            <p class="text-sm text-gray-400 mt-1">Create one to start receiving applications.</p>
                            <a href="{{ route('ilo.opportunities.create') }}"
                               class="mt-4 inline-block px-4 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg font-semibold">
                                <i class="fas fa-plus mr-2"></i> Create Opportunity
                            </a>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($opportunities->hasPages())
        <div class="px-6 py-4 border-t border-gray-100 bg-gray-50">
            {{ $opportunities->appends(request()->query())->links() }}
        </div>
    @endif
</div>
@endsection