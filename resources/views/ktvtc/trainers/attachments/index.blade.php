@extends('ktvtc.trainers.layout.trainerlayout')

@section('title', 'Attachments')

@section('content')

<div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Attachments</h1>
        <p class="text-sm text-gray-500">
            Attachment submissions from students enrolled in your courses.
        </p>
    </div>
</div>

{{-- Stats --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-6">
    <div class="bg-white rounded-lg shadow p-3 border-l-4 border-blue-500">
        <p class="text-xs uppercase text-gray-500">Submitted</p>
        <p class="text-xl font-bold text-gray-800">{{ $stats['submitted'] }}</p>
    </div>
    <div class="bg-white rounded-lg shadow p-3 border-l-4 border-indigo-500">
        <p class="text-xs uppercase text-gray-500">Trainer Approved</p>
        <p class="text-xl font-bold text-gray-800">{{ $stats['trainer_approved'] }}</p>
    </div>
    <div class="bg-white rounded-lg shadow p-3 border-l-4 border-green-500">
        <p class="text-xs uppercase text-gray-500">Active</p>
        <p class="text-xl font-bold text-gray-800">{{ $stats['active'] }}</p>
    </div>
    <div class="bg-white rounded-lg shadow p-3 border-l-4 border-amber-500">
        <p class="text-xs uppercase text-gray-500">Needs Revision</p>
        <p class="text-xl font-bold text-gray-800">{{ $stats['needs_revision'] }}</p>
    </div>
</div>

{{-- Filters --}}
<div class="bg-white rounded-lg shadow p-4 mb-6">
    <form method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-3">
        <div>
            <label class="block text-xs text-gray-500 uppercase mb-1">Status</label>
            <select name="status" class="w-full border-gray-300 rounded-md text-sm">
                <option value="">All open</option>
                @foreach ([
                    'submitted'        => 'Submitted',
                    'trainer_approved' => 'Trainer Approved',
                    'needs_revision'   => 'Needs Revision',
                    'active'           => 'Active',
                    'completed'        => 'Completed',
                    'graded'           => 'Graded',
                ] as $key => $label)
                    <option value="{{ $key }}" @selected(request('status') === $key)>
                        {{ $label }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-xs text-gray-500 uppercase mb-1">Course</label>
            <select name="course_id" class="w-full border-gray-300 rounded-md text-sm">
                <option value="">All my courses</option>
                @foreach ($courses as $c)
                    <option value="{{ $c->id }}" @selected(request('course_id') == $c->id)>
                        {{ $c->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-xs text-gray-500 uppercase mb-1">Company Type</label>
            <select name="company_type" class="w-full border-gray-300 rounded-md text-sm">
                <option value="">All</option>
                <option value="registered" @selected(request('company_type') === 'registered')>Registered</option>
                <option value="self" @selected(request('company_type') === 'self')>Self-found</option>
            </select>
        </div>

        <div>
            <label class="block text-xs text-gray-500 uppercase mb-1">Search</label>
            <input name="search" value="{{ request('search') }}"
                   placeholder="Student or company"
                   class="w-full border-gray-300 rounded-md text-sm">
        </div>

        <div class="flex items-end gap-2">
            <button class="bg-primary hover:bg-secondary text-white text-sm font-medium px-4 py-2 rounded-md">
                <i class="fas fa-filter mr-1"></i> Filter
            </button>
            <a href="{{ route('trainers.attachments.index') }}"
               class="text-sm text-gray-500 hover:text-gray-700 px-3 py-2">Reset</a>
        </div>
    </form>
</div>

{{-- Table --}}
<div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50 text-gray-500 uppercase text-xs">
                <tr>
                    <th class="text-left px-4 py-3">Student</th>
                    <th class="text-left px-4 py-3">Company</th>
                    <th class="text-left px-4 py-3">Where</th>
                    <th class="text-left px-4 py-3">Supervisor</th>
                    <th class="text-left px-4 py-3">Period</th>
                    <th class="text-left px-4 py-3">Status</th>
                    <th class="text-right px-4 py-3">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($attachments as $a)
                    <tr class="hover:bg-gray-50 align-top">
                        <td class="px-4 py-3">
                            <div class="font-medium text-gray-800">
                                {{ $a->student->first_name ?? '' }} {{ $a->student->last_name ?? '' }}
                            </div>
                            <div class="text-xs font-mono text-gray-500">
                                {{ $a->student->student_number ?? '' }}
                            </div>
                        </td>

                        <td class="px-4 py-3">
                            <div class="text-gray-800">{{ $a->company->name ?? $a->company_name }}</div>
                            <div class="text-xs text-gray-500">
                                {{ $a->company->sector ?? $a->company_sector ?? '—' }}
                            </div>
                            @if ($a->company_id)
                                <span class="badge bg-green-100 text-green-700 mt-1">Registered</span>
                            @else
                                <span class="badge bg-amber-100 text-amber-700 mt-1">Self-found</span>
                            @endif
                        </td>

                        <td class="px-4 py-3 text-gray-700">
                            {{ $a->company_address ?? '—' }}
                        </td>

                        <td class="px-4 py-3 text-gray-700">
                            <div>{{ $a->supervisor_name }}</div>
                            <div class="text-xs text-gray-500">{{ $a->supervisor_phone }}</div>
                        </td>

                        <td class="px-4 py-3 text-gray-700 text-xs">
                            {{ $a->start_date?->format('d M Y') }}<br>
                            <span class="text-gray-400">to</span>
                            {{ $a->end_date?->format('d M Y') }}
                        </td>

                        <td class="px-4 py-3">
                            <span class="badge
                                @switch($a->status)
                                    @case('submitted') bg-blue-100 text-blue-700 @break
                                    @case('trainer_approved') bg-indigo-100 text-indigo-700 @break
                                    @case('needs_revision') bg-amber-100 text-amber-700 @break
                                    @case('active') bg-green-100 text-green-700 @break
                                    @case('completed') bg-purple-100 text-purple-700 @break
                                    @case('graded') bg-emerald-100 text-emerald-700 @break
                                    @case('rejected') bg-red-100 text-red-700 @break
                                    @default bg-gray-100 text-gray-700
                                @endswitch">
                                {{ $a->status_label }}
                            </span>
                        </td>

                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('trainers.attachments.show', $a) }}"
                               class="text-primary hover:underline text-xs font-medium">
                                <i class="fas fa-eye"></i> View
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-10 text-center text-gray-400">
                            <i class="fas fa-briefcase text-3xl mb-2 block"></i>
                            No attachments found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($attachments->hasPages())
        <div class="px-4 py-3 border-t bg-gray-50">
            {{ $attachments->links() }}
        </div>
    @endif
</div>

@endsection