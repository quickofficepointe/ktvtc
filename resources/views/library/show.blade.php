@extends('layouts.app')

@section('title', $book->title . ' | Library Catalog')
@section('meta_description', $book->description ?? 'Book details from Kenswed Technical College library.')

@section('content')
<section class="bg-gradient-to-r from-[#B91C1C] to-[#BF1F30] py-8 text-white">
    <div class="container mx-auto px-4">
        <a href="{{ route('library.index') }}" class="inline-flex items-center text-white text-sm mb-4 hover:underline">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Back to Catalog
        </a>
        <h1 class="text-3xl md:text-4xl font-bold">{{ $book->title }}</h1>
    </div>
</section>

<section class="py-12 bg-gray-50">
    <div class="container mx-auto px-4">
        <div class="max-w-5xl mx-auto bg-white rounded-xl shadow-lg overflow-hidden">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Book Cover -->
                <div class="p-6 bg-gray-100 flex items-center justify-center">
                    @if($book->cover_image)
                        <img src="{{ asset('storage/' . $book->cover_image) }}"
                             alt="{{ $book->title }}"
                             class="max-h-96 rounded-lg shadow-md">
                    @else
                        <div class="w-64 h-96 bg-gray-200 rounded-lg flex items-center justify-center">
                            <svg class="w-24 h-24 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                            </svg>
                        </div>
                    @endif
                </div>

                <!-- Book Details -->
                <div class="md:col-span-2 p-6">
                    <div class="mb-4">
                        <div class="flex items-center gap-2 mb-2">
                            @if($book->category)
                                <span class="bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded">
                                    {{ $book->category->name }}
                                </span>
                            @endif
                            <span class="bg-gray-100 text-gray-800 text-xs px-2 py-1 rounded">
                                ISBN: {{ $book->isbn ?? 'N/A' }}
                            </span>
                        </div>

                        <p class="text-gray-600 mt-2">by <strong>{{ $book->author->name ?? 'Unknown Author' }}</strong></p>

                        <div class="mt-4 flex items-center gap-4">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 text-gray-500 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                </svg>
                                <span>{{ $book->items->count() }} copy(s) total</span>
                            </div>
                            <div class="flex items-center">
                                <svg class="w-5 h-5 text-green-500 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                <span class="text-green-600 font-semibold">{{ $availableCopies }} available now</span>
                            </div>
                        </div>
                    </div>

                    <div class="border-t border-b py-4 my-4">
                        <h3 class="font-semibold text-gray-800 mb-2">Description</h3>
                        <p class="text-gray-600">{{ $book->description ?? 'No description available.' }}</p>
                    </div>

                    <div class="mt-6">
                        @auth
                            @if($availableCopies > 0)
                                <a href="{{ route('library.reserve', $book->id) }}"
                                   class="bg-[#B91C1C] text-white px-6 py-3 rounded-lg hover:bg-[#991B1B] transition-colors inline-block">
                                    Reserve This Book
                                </a>
                            @else
                                <button disabled
                                        class="bg-gray-400 text-white px-6 py-3 rounded-lg cursor-not-allowed">
                                    Currently Unavailable - Join Waitlist
                                </button>
                                <a href="{{ route('library.reserve', $book->id) }}"
                                   class="ml-2 bg-gray-600 text-white px-6 py-3 rounded-lg hover:bg-gray-700 transition-colors inline-block">
                                    Join Waitlist
                                </a>
                            @endif
                        @else
                            <a href="{{ route('login') }}" class="bg-[#B91C1C] text-white px-6 py-3 rounded-lg hover:bg-[#991B1B] transition-colors inline-block">
                                Login to Reserve
                            </a>
                        @endauth
                    </div>
                </div>
            </div>
        </div>

        <!-- Related Books -->
        @if($relatedBooks->count() > 0)
        <div class="mt-12">
            <h2 class="text-2xl font-bold text-gray-800 mb-6">You May Also Like</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6">
                @foreach($relatedBooks as $related)
                <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow">
                    <a href="{{ route('library.show', $related->slug) }}">
                        <div class="h-48 bg-gray-100 flex items-center justify-center">
                            @if($related->cover_image)
                                <img src="{{ asset('storage/' . $related->cover_image) }}"
                                     alt="{{ $related->title }}"
                                     class="h-full w-full object-cover">
                            @else
                                <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                </svg>
                            @endif
                        </div>
                    </a>
                    <div class="p-3">
                        <a href="{{ route('library.show', $related->slug) }}">
                            <h3 class="font-semibold text-gray-800 hover:text-[#B91C1C] text-sm line-clamp-2">
                                {{ $related->title }}
                            </h3>
                        </a>
                        <p class="text-xs text-gray-600 mt-1">{{ $related->author->name ?? 'Unknown' }}</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</section>
@endsection
