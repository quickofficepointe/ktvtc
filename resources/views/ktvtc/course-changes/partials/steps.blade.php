{{-- Approval progress: Academics -> Finance -> Admissions --}}
@php
    $order = ['academics', 'finance', 'admin'];
    $reached = $change->status === 'approved' ? 3 : (array_search($change->rejected_at_stage ?? $change->status, $order, true) ?: 0);
@endphp
<ol class="flex flex-wrap items-center gap-2 mt-2 text-xs">
    @foreach($order as $i => $stage)
        @php
            $by = $change->{$stage . '_at'};
            $state = match (true) {
                $change->status === 'rejected' && $change->rejected_at_stage === $stage => 'rejected',
                $by !== null || $i < $reached => 'done',
                $change->status === $stage => 'current',
                default => 'todo',
            };
        @endphp
        <li class="flex items-center gap-1 px-2 py-1 rounded-full
            {{ ['done' => 'bg-green-100 text-green-700', 'current' => 'bg-blue-100 text-blue-700', 'rejected' => 'bg-red-100 text-red-700', 'todo' => 'bg-gray-100 text-gray-500'][$state] }}">
            <i class="fas {{ ['done' => 'fa-check', 'current' => 'fa-hourglass-half', 'rejected' => 'fa-times', 'todo' => 'fa-circle'][$state] }}"></i>
            {{ \App\Models\CourseChangeRequest::STAGE_NAMES[$stage] }}
            @if($by)<span class="opacity-75">· {{ $by->format('d M') }}</span>@endif
        </li>
        @if(!$loop->last)<li class="text-gray-300"><i class="fas fa-chevron-right"></i></li>@endif
    @endforeach
</ol>
