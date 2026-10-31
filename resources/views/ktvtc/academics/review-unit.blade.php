@extends(auth()->user()->canManageAcademics() ? 'ktvtc.academics.layout' : 'ktvtc.admin.layout.adminlayout')

@section('title', $unit->label)
@section('subtitle', $unit->course->name . ' · Term ' . $unit->term_number . ' · Trainer: ' . ($unit->trainer->name ?? 'none'))

@section('header-actions')
@if(auth()->user()->canManageAcademics())
<a href="{{ route('academics.review') }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg font-medium"><i class="fas fa-arrow-left mr-1"></i> Review</a>
@endif
@endsection

@section('content')
<div class="{{ auth()->user()->canManageAcademics() ? '' : 'academics-ro' }}">
@include('ktvtc.academics.partials.readonly')
@php
    $badge = ['draft' => 'bg-gray-100 text-gray-700', 'submitted' => 'bg-yellow-100 text-yellow-800', 'published' => 'bg-green-100 text-green-800'];
@endphp
<form method="POST" action="{{ route('academics.review.decide', $unit) }}" class="bg-white rounded-xl border p-4 space-y-4" id="decideForm">
    @csrf
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-gray-500 border-b">
                    <th class="py-2 px-2"><input type="checkbox" onclick="document.querySelectorAll('.pick').forEach(c => c.checked = this.checked)"></th>
                    <th class="py-2 px-2">Student</th><th class="py-2 px-2">Attempt</th>
                    <th class="py-2 px-2 text-right">{{ $scale->component_a ?? 'A' }}</th><th class="py-2 px-2 text-right">{{ $scale->component_b ?? 'B' }}</th>
                    <th class="py-2 px-2 text-right">Total</th><th class="py-2 px-2">Grade</th><th class="py-2 px-2">Status</th><th class="py-2 px-2"></th>
                </tr>
            </thead>
            <tbody>
                @foreach($results as $r)
                    <tr class="border-b">
                        <td class="py-2 px-2">@if($r->status === 'submitted')<input type="checkbox" class="pick" name="result_ids[]" value="{{ $r->id }}" checked>@endif</td>
                        <td class="py-2 px-2">{{ $r->student->full_name ?? '—' }} <span class="text-xs text-gray-400">{{ $r->student->student_number ?? '' }}</span></td>
                        <td class="py-2 px-2">{{ $r->attempt > 1 ? 'Re-sit ' . ($r->attempt - 1) : 'First' }}</td>
                        <td class="py-2 px-2 text-right">{{ $r->score_a ?? '—' }}</td>
                        <td class="py-2 px-2 text-right">{{ $r->score_b ?? '—' }}</td>
                        <td class="py-2 px-2 text-right font-semibold">{{ $r->total ?? '—' }}</td>
                        <td class="py-2 px-2 {{ $r->passed === false ? 'text-red-600 font-semibold' : '' }}">{{ $r->grade ?? '—' }}</td>
                        <td class="py-2 px-2">
                            <span class="px-2 py-0.5 rounded text-xs {{ $badge[$r->status] }}">{{ ucfirst($r->status) }}</span>
                            @if($r->returned_reason)<div class="text-xs text-orange-700">Returned: {{ $r->returned_reason }}</div>@endif
                            @if($r->remarks)<div class="text-xs text-gray-500">{{ $r->remarks }}</div>@endif
                        </td>
                        <td class="py-2 px-2 text-right">
                            @if($r->status === 'published')
                                <button type="button" class="text-xs text-red-600 hover:underline" onclick="unpublish({{ $r->id }})">Withdraw</button>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @if($results->where('status', 'submitted')->isNotEmpty())
        <div class="flex flex-wrap items-center gap-2 text-sm">
            <button name="action" value="publish" class="px-4 py-2 bg-green-600 text-white rounded-lg font-semibold"><i class="fas fa-check mr-1"></i> Publish selected</button>
            <input type="text" name="reason" placeholder="Reason, if returning to trainer" class="border rounded-lg px-3 py-2 flex-1 min-w-[220px]">
            <button name="action" value="return" class="px-4 py-2 bg-orange-500 text-white rounded-lg font-semibold">Return selected to trainer</button>
        </div>
    @endif
</form>

<form method="POST" id="unpublishForm" class="hidden">@csrf<input type="hidden" name="reason"></form>
<script>
    function unpublish(id) {
        const reason = prompt('Why withdraw this published result? The trainer will correct it.');
        if (!reason || reason.length < 5) return;
        const f = document.getElementById('unpublishForm');
        f.action = '{{ url('admin/academics/results') }}/' + id + '/unpublish';
        f.reason.value = reason;
        f.submit();
    }
</script>
</div>
@endsection
