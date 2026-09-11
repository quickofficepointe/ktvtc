@extends('ktvtc.trainers.layout.trainerlayout')

@section('title', 'Dashboard')

@section('content')

<div class="mb-6 flex items-center justify-between">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Trainer Dashboard</h1>
        <p class="text-sm text-gray-500">
            Welcome back, {{ auth()->user()->name }}.
            @if ($currentTerm)
                Current term: <strong>{{ $currentTerm->name }}</strong>
                ({{ $currentTerm->academic_year }})
            @endif
        </p>
    </div>
</div>

{{-- Stats cards --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-lg shadow p-4 border-l-4 border-primary">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs uppercase tracking-wider text-gray-500">My Courses</p>
                <p class="text-2xl font-bold text-gray-800">{{ $stats['courses'] }}</p>
            </div>
            <i class="fas fa-book text-2xl text-primary opacity-30"></i>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-4 border-l-4 border-green-500">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs uppercase tracking-wider text-gray-500">Active Students</p>
                <p class="text-2xl font-bold text-gray-800">{{ $stats['active_students'] }}</p>
            </div>
            <i class="fas fa-users text-2xl text-green-500 opacity-30"></i>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-4 border-l-4 border-indigo-500">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs uppercase tracking-wider text-gray-500">On Attachment</p>
                <p class="text-2xl font-bold text-gray-800">{{ $stats['students_on_attachment'] }}</p>
            </div>
            <i class="fas fa-briefcase text-2xl text-indigo-500 opacity-30"></i>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-4 border-l-4 border-amber-500">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs uppercase tracking-wider text-gray-500">Pending Approval</p>
                <p class="text-2xl font-bold text-gray-800">{{ $stats['pending_approval'] }}</p>
            </div>
            <i class="fas fa-clock text-2xl text-amber-500 opacity-30"></i>
        </div>
    </div>
</div>

{{-- Quick actions --}}
<div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
    <a href="{{ route('trainers.students.index') }}"
       class="block bg-white rounded-lg shadow hover:shadow-md transition p-5 border-l-4 border-primary">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="font-semibold text-gray-800">My Students</h3>
                <p class="text-sm text-gray-500">View enrolled students per term and their fee balances</p>
            </div>
            <i class="fas fa-arrow-right text-primary"></i>
        </div>
    </a>

    <a href="{{ route('trainers.attachments.index') }}"
       class="block bg-white rounded-lg shadow hover:shadow-md transition p-5 border-l-4 border-indigo-500">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="font-semibold text-gray-800">Attachments</h3>
                <p class="text-sm text-gray-500">Review and approve attachment submissions from your students</p>
            </div>
            <i class="fas fa-arrow-right text-indigo-500"></i>
        </div>
    </a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

    {{-- Recent enrollments --}}
    <div class="bg-white rounded-lg shadow">
        <div class="p-4 border-b flex items-center justify-between">
            <h3 class="font-semibold text-gray-800">
                <i class="fas fa-user-graduate text-primary mr-2"></i>Recent Enrollments
            </h3>
            <a href="{{ route('trainers.students.index') }}"
               class="text-sm text-primary hover:underline">View all</a>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 text-gray-500 uppercase text-xs">
                    <tr>
                        <th class="text-left px-4 py-2">Student</th>
                        <th class="text-left px-4 py-2">Course</th>
                        <th class="text-right px-4 py-2">Balance</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($recentEnrollments as $e)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-2">
                                <div class="font-medium text-gray-800">{{ $e->student_name }}</div>
                                <div class="text-xs text-gray-500">{{ $e->student_number }}</div>
                            </td>
                            <td class="px-4 py-2 text-gray-700">
                                {{ $e->course->name ?? $e->course_name }}
                            </td>
                            <td class="px-4 py-2 text-right">
                                <span class="font-semibold {{ $e->balance > 0 ? 'text-red-600' : 'text-green-600' }}">
                                    {{ number_format($e->balance, 2) }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-4 py-6 text-center text-gray-400">
                                No active enrollments yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Recent attachments --}}
    <div class="bg-white rounded-lg shadow">
        <div class="p-4 border-b flex items-center justify-between">
            <h3 class="font-semibold text-gray-800">
                <i class="fas fa-briefcase text-indigo-500 mr-2"></i>Recent Attachments
            </h3>
            <a href="{{ route('trainers.attachments.index') }}"
               class="text-sm text-primary hover:underline">View all</a>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 text-gray-500 uppercase text-xs">
                    <tr>
                        <th class="text-left px-4 py-2">Student</th>
                        <th class="text-left px-4 py-2">Company</th>
                        <th class="text-left px-4 py-2">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($recentAttachments as $a)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-2">
                                <div class="font-medium text-gray-800">
                                    {{ $a->student->first_name ?? '' }} {{ $a->student->last_name ?? '' }}
                                </div>
                                <div class="text-xs text-gray-500">{{ $a->student->student_number ?? '' }}</div>
                            </td>
                            <td class="px-4 py-2 text-gray-700">
                                {{ $a->company->name ?? $a->company_name }}
                            </td>
                            <td class="px-4 py-2">
                                <a href="{{ route('trainers.attachments.show', $a) }}">
                                    <span class="badge
                                        @switch($a->status)
                                            @case('submitted') bg-blue-100 text-blue-700 @break
                                            @case('trainer_approved') bg-indigo-100 text-indigo-700 @break
                                            @case('needs_revision') bg-amber-100 text-amber-700 @break
                                            @case('active') bg-green-100 text-green-700 @break
                                            @default bg-gray-100 text-gray-700
                                        @endswitch">
                                        {{ $a->status_label }}
                                    </span>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-4 py-6 text-center text-gray-400">
                                No recent attachments.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

@endsection