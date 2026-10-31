@extends('ktvtc.finance.layouts.app')

@section('title', 'Term Fees')
@section('subtitle', 'How each course is split into terms, and what each term costs')

@section('breadcrumb')
<li><span class="mx-2">/</span><span class="text-gray-400">Term fees</span></li>
@endsection

@section('header-actions')
<a href="{{ route('finance.fee-structure.export') }}" class="border border-gray-300 text-gray-700 px-3 py-2 rounded-lg text-sm"><i class="fas fa-file-csv mr-1"></i> Export</a>
@endsection

@section('content')
<div class="space-y-6">
    <form method="GET" class="finance-card p-4 flex flex-wrap gap-3 text-sm">
        <input type="text" name="q" value="{{ request('q') }}" placeholder="Course name" class="flex-1 min-w-[200px] border rounded-lg px-3 py-2">
        <select name="filter" class="border rounded-lg px-3 py-2">
            <option value="">All courses</option>
            <option value="pending" @selected(request('filter') === 'pending')>Not set up yet</option>
        </select>
        <button class="btn-primary text-white px-4 py-2 rounded-lg"><i class="fas fa-search mr-1"></i>Filter</button>
    </form>

    <div class="finance-card p-4 overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-gray-500 border-b">
                    <th class="py-2 px-3">Course</th>
                    <th class="py-2 px-3">Duration (as advertised)</th>
                    <th class="py-2 px-3">Terms</th>
                    <th class="py-2 px-3 text-right">Fee versions</th>
                    <th class="py-2 px-3 text-right">Active students</th>
                    <th class="py-2 px-3"></th>
                </tr>
            </thead>
            <tbody>
                @foreach($courses as $course)
                    <tr class="border-b hover:bg-gray-50">
                        <td class="py-2 px-3 font-medium">{{ $course->name }}</td>
                        <td class="py-2 px-3 text-gray-500">{{ $course->duration ?? '—' }}</td>
                        <td class="py-2 px-3">
                            @if($course->hasTermSetup())
                                {{ $course->term_count }} × {{ $course->term_length }} {{ $course->term_length_unit }}
                            @else
                                <span class="text-xs bg-yellow-100 text-yellow-800 px-2 py-0.5 rounded">Not set</span>
                            @endif
                        </td>
                        <td class="py-2 px-3 text-right">
                            @if($course->term_fees_count)
                                {{ $course->term_fees_count }}
                            @else
                                <span class="text-xs bg-yellow-100 text-yellow-800 px-2 py-0.5 rounded">No fees</span>
                            @endif
                        </td>
                        <td class="py-2 px-3 text-right">{{ $course->active_enrollments_count }}</td>
                        <td class="py-2 px-3 text-right"><a href="{{ route('finance.fee-structure.course', $course) }}" class="btn-primary text-white px-3 py-1 rounded-lg text-xs">Set up</a></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
