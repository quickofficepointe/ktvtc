@extends('layouts.app')

@section('title', $ebook->title . ' | Kenswed Technical College')
@section('meta_description', Str::limit($ebook->description ?? 'E-book in the Kenswed Technical College digital library', 160))

@section('content')
<section class="bg-gradient-to-r from-[#B91C1C] to-[#BF1F30] py-8 text-white">
    <div class="container mx-auto px-4">
        <a href="{{ route('library.index') }}" class="text-sm text-white/80 hover:text-white">&larr; Library</a>
        <h1 class="text-2xl md:text-3xl font-bold mt-2">{{ $ebook->title }}</h1>
        @if($ebook->author)<p class="text-white/90 mt-1">by {{ $ebook->author }}</p>@endif
    </div>
</section>

<section class="py-12 bg-gray-50">
    <div class="container mx-auto px-4 grid grid-cols-1 md:grid-cols-3 gap-8">
        <div>
            @if($ebook->cover_image)
                <img src="{{ asset('storage/' . $ebook->cover_image) }}" alt="{{ $ebook->title }}" class="w-full rounded-xl shadow">
            @else
                <div class="w-full aspect-[3/4] rounded-xl bg-white border flex items-center justify-center text-gray-400 text-sm">No cover</div>
            @endif
        </div>
        <div class="md:col-span-2 bg-white rounded-xl border p-6 space-y-4">
            <dl class="grid grid-cols-2 gap-x-6 gap-y-2 text-sm">
                <dt class="text-gray-500">Category</dt><dd>{{ $ebook->category->name ?? '—' }}</dd>
                <dt class="text-gray-500">Publisher</dt><dd>{{ $ebook->publisher ?: '—' }}</dd>
                <dt class="text-gray-500">Year</dt><dd>{{ $ebook->publication_year ?: '—' }}</dd>
                <dt class="text-gray-500">Language</dt><dd>{{ $ebook->language ?: '—' }}</dd>
                <dt class="text-gray-500">Format</dt><dd>{{ strtoupper($ebook->file_format ?? '') ?: '—' }}</dd>
                @if($ebook->isbn)<dt class="text-gray-500">ISBN</dt><dd class="font-mono">{{ $ebook->isbn }}</dd>@endif
            </dl>
            @if($ebook->description)<p class="text-gray-700 whitespace-pre-line">{{ $ebook->description }}</p>@endif
            @if($ebook->file_path)
                <a href="{{ route('library.ebook.download', $ebook) }}" class="inline-block bg-[#B91C1C] hover:bg-[#991B1B] text-white font-semibold px-6 py-3 rounded-lg">Download</a>
            @else
                <p class="text-sm text-gray-500">The file for this e-book is not available yet.</p>
            @endif
            <p class="text-xs text-gray-400">{{ (int) $ebook->view_count }} views · {{ (int) $ebook->download_count }} downloads</p>
        </div>
    </div>

    @if($relatedEBooks->isNotEmpty())
        <div class="container mx-auto px-4 mt-10">
            <h2 class="text-xl font-bold text-gray-800 mb-4">More in this category</h2>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                @foreach($relatedEBooks as $related)
                    <a href="{{ route('library.ebook.show', $related) }}" class="bg-white rounded-xl border p-4 hover:shadow">
                        <p class="font-semibold text-gray-800">{{ $related->title }}</p>
                        <p class="text-xs text-gray-500">{{ $related->author }}</p>
                    </a>
                @endforeach
            </div>
        </div>
    @endif
</section>
@endsection
