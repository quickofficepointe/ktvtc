@extends($layout)

@section('title', 'Course Changes')
@section('subtitle', 'Students asking to move to another course')

@section('content')
<div class="space-y-6">
    @php
        $tabs = [
            'mine'      => 'Waiting for me (' . ($counts[$myStage] ?? 0) . ')',
            'pending'   => 'All waiting (' . collect(\App\Models\CourseChangeRequest::PENDING)->sum(fn ($s) => $counts[$s] ?? 0) . ')',
            'approved'  => 'Approved (' . ($counts['approved'] ?? 0) . ')',
            'rejected'  => 'Not approved (' . ($counts['rejected'] ?? 0) . ')',
            'cancelled' => 'Withdrawn (' . ($counts['cancelled'] ?? 0) . ')',
            'all'       => 'All',
        ];
    @endphp
    <div class="flex flex-wrap gap-2 text-sm">
        @foreach($tabs as $key => $label)
            <a href="{{ route('course-changes.index', ['status' => $key]) }}"
               class="px-3 py-1.5 rounded-lg border {{ $filter === $key ? 'bg-gray-800 text-white border-gray-800' : 'bg-white text-gray-700 hover:bg-gray-50' }}">{{ $label }}</a>
        @endforeach
    </div>

    <div class="bg-white rounded-xl border p-5 overflow-x-auto">
        <p class="text-xs text-gray-500 mb-3">Order of approval: Academics (starting term) → Finance (money moved) → Admissions (final, issues the new number).</p>
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-gray-500 border-b">
                    <th class="py-2 pr-3">Student</th><th class="py-2 pr-3">From</th><th class="py-2 pr-3">To</th><th class="py-2 pr-3">Sent</th><th class="py-2 pr-3">Status</th><th class="py-2"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($requests as $r)
                    <tr class="border-b align-top">
                        <td class="py-2 pr-3"><p class="font-medium">{{ $r->student->full_name ?? '—' }}</p><p class="text-xs text-gray-500 font-mono">{{ $r->student->student_number ?? '' }}</p></td>
                        <td class="py-2 pr-3">{{ $r->fromEnrollment->course_name ?? '—' }}<p class="text-xs text-gray-500 font-mono">{{ $r->fromEnrollment->student_number ?? '' }}</p></td>
                        <td class="py-2 pr-3">{{ $r->toCourse->name ?? '—' }}</td>
                        <td class="py-2 pr-3 whitespace-nowrap">{{ $r->created_at->format('d M Y') }}</td>
                        <td class="py-2 pr-3 text-xs">{{ $r->status_label }}</td>
                        <td class="py-2 text-right">
                            <a href="{{ route('course-changes.show', $r) }}" class="text-blue-600 hover:underline whitespace-nowrap">{{ $r->awaits(auth()->user()) ? 'Review' : 'View' }}</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="py-6 text-center text-gray-500">No requests here.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="mt-4">{{ $requests->links() }}</div>
    </div>
</div>
@endsection
