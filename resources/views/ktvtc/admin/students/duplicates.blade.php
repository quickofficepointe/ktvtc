@extends('ktvtc.admin.layout.adminlayout')

@section('title', 'Duplicate Students')
@section('subtitle', 'Records that look like the same person')

@section('header-actions')
<a href="{{ route('admin.students.index') }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg font-medium"><i class="fas fa-arrow-left mr-1"></i> Students</a>
@endsection

@section('content')
<div class="space-y-4">
    <div class="bg-white rounded-xl border p-4 text-sm text-gray-600">
        For each group, choose the record to <strong>keep</strong> and tick the ones that are the same person.
        Their enrollments, payments, terms, results and login move to the kept record, and they are archived (not deleted).
        If a group is actually different people, leave it.
    </div>

    @forelse($groups as $i => $group)
        <form method="POST" action="{{ route('admin.students.duplicates.merge') }}" class="bg-white rounded-xl border p-4"
              onsubmit="return confirm('Merge the ticked records into the one marked Keep? This moves their enrollments and payments.')">
            @csrf
            <p class="text-xs font-semibold text-gray-500 uppercase mb-2">{{ $group['why'] }}</p>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-gray-500 border-b">
                            <th class="py-2 px-2">Keep</th><th class="py-2 px-2">Merge in</th><th class="py-2 px-2">Name</th><th class="py-2 px-2">Number</th>
                            <th class="py-2 px-2">Phone</th><th class="py-2 px-2">ID no.</th><th class="py-2 px-2 text-right">Enrollments</th>
                            <th class="py-2 px-2 text-right">Paid</th><th class="py-2 px-2">Login</th><th class="py-2 px-2">Created</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($group['students'] as $j => $s)
                            <tr class="border-b">
                                <td class="py-2 px-2"><input type="radio" name="keep_id" value="{{ $s->id }}" @checked($j === 0) required></td>
                                <td class="py-2 px-2"><input type="checkbox" name="merge_ids[]" value="{{ $s->id }}"></td>
                                <td class="py-2 px-2"><a href="{{ route('admin.students.show', $s) }}" target="_blank" class="hover:underline">{{ $s->full_name }}</a></td>
                                <td class="py-2 px-2 font-mono text-xs">{{ $s->student_number }}@if($s->legacy_student_code)<br><span class="text-gray-400">old: {{ $s->legacy_student_code }}</span>@endif</td>
                                <td class="py-2 px-2">{{ $s->phone }}</td>
                                <td class="py-2 px-2">{{ $s->id_number ?? '—' }}</td>
                                <td class="py-2 px-2 text-right">{{ $s->enrollments_count }}</td>
                                <td class="py-2 px-2 text-right">{{ number_format($s->payments_total, 2) }}</td>
                                <td class="py-2 px-2">{!! $s->has_login ? '<i class="fas fa-check text-green-600"></i>' : '—' !!}</td>
                                <td class="py-2 px-2 text-xs">{{ $s->created_at?->format('d M Y') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <button class="mt-3 px-4 py-2 bg-primary text-white rounded-lg text-sm font-semibold">Merge ticked into Keep</button>
        </form>
    @empty
        <div class="bg-white rounded-xl border p-6 text-sm text-gray-500"><i class="fas fa-check-circle text-green-500 mr-1"></i> No likely duplicates found.</div>
    @endforelse
</div>
@endsection
