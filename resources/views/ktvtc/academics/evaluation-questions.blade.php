@extends(auth()->user()->canManageAcademics() ? 'ktvtc.academics.layout' : 'ktvtc.admin.layout.adminlayout')

@section('title', 'Evaluation Questions')
@section('subtitle', 'What students are asked about each trainer')

@section('header-actions')
@if(auth()->user()->canManageAcademics())
<a href="{{ route('academics.evaluations') }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg font-medium"><i class="fas fa-arrow-left mr-1"></i> Evaluations</a>
@endif
@endsection

@section('content')
<div class="{{ auth()->user()->canManageAcademics() ? '' : 'academics-ro' }}">
@include('ktvtc.academics.partials.readonly')
<div class="bg-white rounded-xl border p-5 space-y-3">
    @foreach($questions as $q)
        <form method="POST" action="{{ route('academics.evaluations.questions.update', $q) }}" class="flex flex-wrap items-center gap-2 border-b pb-3 text-sm {{ $q->is_active ? '' : 'opacity-50' }}">
            @csrf @method('PUT')
            <input type="number" name="sort_order" value="{{ $q->sort_order }}" class="w-16 border rounded px-2 py-1" title="Order">
            <input type="text" name="text" value="{{ $q->text }}" required class="flex-1 min-w-[250px] border rounded px-2 py-1">
            <select name="type" class="border rounded px-2 py-1">
                <option value="rating" @selected($q->type === 'rating')>Rating 1–5</option>
                <option value="text" @selected($q->type === 'text')>Written answer</option>
            </select>
            <label class="flex items-center gap-1 text-xs"><input type="hidden" name="is_required" value="0"><input type="checkbox" name="is_required" value="1" @checked($q->is_required)> Required</label>
            <label class="flex items-center gap-1 text-xs"><input type="hidden" name="is_active" value="0"><input type="checkbox" name="is_active" value="1" @checked($q->is_active)> Active</label>
            <button class="px-3 py-1 bg-gray-100 rounded text-xs">Save</button>
            <span class="text-xs text-gray-400">{{ $q->answers_count }} answers</span>
        </form>
    @endforeach

    <form method="POST" action="{{ route('academics.evaluations.questions.store') }}" class="flex flex-wrap items-center gap-2 pt-2 text-sm">
        @csrf
        <input type="number" name="sort_order" value="{{ ($questions->max('sort_order') ?? 0) + 1 }}" class="w-16 border rounded px-2 py-1">
        <input type="text" name="text" required placeholder="New question" class="flex-1 min-w-[250px] border rounded px-2 py-1">
        <select name="type" class="border rounded px-2 py-1"><option value="rating">Rating 1–5</option><option value="text">Written answer</option></select>
        <label class="flex items-center gap-1 text-xs"><input type="hidden" name="is_required" value="0"><input type="checkbox" name="is_required" value="1" checked> Required</label>
        <button class="px-3 py-1 bg-primary text-white rounded text-xs">Add question</button>
    </form>
</div>
</div>
@endsection
