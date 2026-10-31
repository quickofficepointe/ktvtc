@extends('ktvtc.library.layout.librarylayout')

@section('title', trim($author->first_name . ' ' . $author->last_name) . ' | Authors')

@section('content')
<div class="space-y-6">
    <div class="bg-white rounded-xl shadow-sm border p-6 flex flex-wrap gap-6">
        @if($author->photo)
            <img src="{{ asset('storage/' . $author->photo) }}" alt="" class="w-24 h-24 rounded-xl object-cover">
        @else
            <div class="w-24 h-24 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center text-3xl font-bold">
                {{ strtoupper(substr($author->first_name, 0, 1) . substr($author->last_name, 0, 1)) }}
            </div>
        @endif
        <div class="flex-1 min-w-0">
            <a href="{{ route('library.authors.index') }}" class="text-sm text-amber-700 hover:underline">&larr; Authors</a>
            <h1 class="text-2xl font-bold text-gray-800 mt-1">{{ trim($author->first_name . ' ' . $author->last_name) }}</h1>
            <p class="text-sm text-gray-500">
                {{ $author->nationality ?: 'Nationality not recorded' }}
                @if($author->birth_date) · born {{ \Carbon\Carbon::parse($author->birth_date)->format('Y') }}@endif
                @if($author->death_date) · died {{ \Carbon\Carbon::parse($author->death_date)->format('Y') }}@endif
            </p>
            @if($author->bio)<p class="text-sm text-gray-700 mt-3 whitespace-pre-line">{{ $author->bio }}</p>@endif
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border p-6 overflow-x-auto">
        <h2 class="font-semibold text-gray-800 mb-3">Books in the library ({{ $author->books->count() }})</h2>
        <table class="w-full text-sm">
            <thead><tr class="text-left text-gray-500 border-b"><th class="py-2">Title</th><th class="py-2">Category</th><th class="py-2">ISBN</th><th class="py-2 text-right">On shelf</th></tr></thead>
            <tbody>
                @forelse($author->books as $book)
                    <tr class="border-b">
                        <td class="py-2">{{ $book->title }}</td>
                        <td class="py-2">{{ $book->category->name ?? '—' }}</td>
                        <td class="py-2 font-mono text-xs">{{ $book->isbn }}</td>
                        <td class="py-2 text-right">{{ $book->available_copies }} / {{ $book->total_copies }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="py-4 text-center text-gray-500">No books linked to this author yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
