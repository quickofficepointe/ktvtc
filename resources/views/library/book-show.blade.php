@extends('layouts.app')

@section('title', $book->title . ' | Kenswed Technical College')
@section('meta_description', Str::limit($book->description ?? 'Book details and availability at Kenswed Technical College Library', 160))

@section('content')
<!-- Book Header -->
<section class="bg-gradient-to-r from-[#B91C1C] to-[#BF1F30] py-8 text-white">
    <div class="container mx-auto px-4">
        <div class="flex items-center text-sm mb-4">
            <a href="{{ route('library.index') }}" class="text-white opacity-80 hover:opacity-100 transition">
                <i class="fas fa-home mr-1"></i> Library
            </a>
            <span class="mx-2">/</span>
            <a href="{{ route('library.index') }}?category={{ $book->category_id }}" class="text-white opacity-80 hover:opacity-100 transition">
                {{ $book->category->name ?? 'Books' }}
            </a>
            <span class="mx-2">/</span>
            <span class="text-white">{{ Str::limit($book->title, 50) }}</span>
        </div>
    </div>
</section>

<!-- Book Details Section -->
<section class="py-12 bg-gray-50">
    <div class="container mx-auto px-4">
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 p-6 lg:p-8">
                <!-- Book Cover -->
                <div class="lg:col-span-1">
                    <div class="bg-gray-100 rounded-lg overflow-hidden shadow-md">
                        @if($book->cover_image)
                            <img src="{{ asset('storage/' . $book->cover_image) }}"
                                 alt="{{ $book->title }}"
                                 class="w-full h-auto object-cover">
                        @else
                            <div class="aspect-[3/4] bg-gradient-to-br from-gray-200 to-gray-300 flex items-center justify-center">
                                <svg class="w-32 h-32 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                </svg>
                            </div>
                        @endif
                    </div>

                    <!-- Availability Badge -->
                    <div class="mt-4 text-center">
                        @if($book->available_copies > 0)
                            <div class="inline-flex items-center px-4 py-2 bg-green-100 text-green-800 rounded-full">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span class="font-semibold">{{ $book->available_copies }} copy(s) available</span>
                            </div>
                        @else
                            <div class="inline-flex items-center px-4 py-2 bg-red-100 text-red-800 rounded-full">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span class="font-semibold">Currently Unavailable</span>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Book Information -->
                <div class="lg:col-span-2">
                    <h1 class="text-3xl lg:text-4xl font-bold text-gray-800 mb-2">{{ $book->title }}</h1>

                    <div class="flex flex-wrap gap-4 mb-4 text-sm text-gray-600">
                        @if($book->publisher)
                            <span class="flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z"/>
                                </svg>
                                {{ $book->publisher }}
                            </span>
                        @endif
                        @if($book->publication_year)
                            <span class="flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                {{ $book->publication_year }}
                            </span>
                        @endif
                        @if($book->isbn)
                            <span class="flex items-center font-mono">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M6 6h12M6 18h12M3 14h18"/>
                                </svg>
                                ISBN: {{ $book->isbn }}
                            </span>
                        @endif
                        @if($book->language)
                            <span class="flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"/>
                                </svg>
                                {{ $book->language }}
                            </span>
                        @endif
                        @if($book->page_count)
                            <span class="flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                {{ $book->page_count }} pages
                            </span>
                        @endif
                    </div>

                    <!-- Category Badge -->
                    <div class="mb-4">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium text-white"
                              style="background-color: {{ $book->category->color ?? '#6B7280' }}">
                            {{ $book->category->name ?? 'General' }}
                        </span>
                    </div>

                    <!-- Description -->
                    @if($book->description)
                        <div class="mb-6">
                            <h3 class="text-lg font-semibold text-gray-800 mb-2">Description</h3>
                            <p class="text-gray-600 leading-relaxed">{{ $book->description }}</p>
                        </div>
                    @endif

                    <!-- Copies per Branch -->
                    @if($copiesByBranch && $copiesByBranch->count() > 0)
                        <div class="mb-6">
                            <h3 class="text-lg font-semibold text-gray-800 mb-3">Available Copies by Branch</h3>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                @foreach($copiesByBranch as $branchName => $count)
                                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg border border-gray-200">
                                        <div class="flex items-center">
                                            <div class="w-8 h-8 bg-[#B91C1C] bg-opacity-10 rounded-full flex items-center justify-center mr-3">
                                                <svg class="w-4 h-4 text-[#B91C1C]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                                </svg>
                                            </div>
                                            <span class="font-medium text-gray-800">{{ $branchName }}</span>
                                        </div>
                                        <span class="text-green-600 font-semibold">{{ $count }} copy(s)</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- Action Buttons -->
                    <div class="flex flex-wrap gap-3 pt-4 border-t border-gray-200">
                        @auth
                            @if($book->available_copies > 0)
                                <a href="{{ route('library.reserve', $book->id) }}"
                                   class="bg-[#B91C1C] text-white px-6 py-3 rounded-lg font-semibold hover:bg-[#991B1B] transition-colors inline-flex items-center">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                    </svg>
                                    Reserve This Book
                                </a>
                            @else
                                <button disabled
                                        class="bg-gray-400 text-white px-6 py-3 rounded-lg font-semibold cursor-not-allowed inline-flex items-center">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    Currently Unavailable
                                </button>
                            @endif
                        @else
                            <a href="{{ route('login') }}"
                               class="bg-[#B91C1C] text-white px-6 py-3 rounded-lg font-semibold hover:bg-[#991B1B] transition-colors inline-flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                                </svg>
                                Login to Reserve
                            </a>
                        @endauth

                        <button onclick="window.print()"
                                class="border border-gray-300 text-gray-700 px-6 py-3 rounded-lg font-semibold hover:bg-gray-50 transition-colors inline-flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                            </svg>
                            Print Details
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Related Books Section -->
@if($relatedBooks->count() > 0)
<section class="py-12 bg-white">
    <div class="container mx-auto px-4">
        <h2 class="text-2xl font-bold text-gray-800 mb-6">Related Books</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach($relatedBooks as $relatedBook)
                <div class="bg-gray-50 rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow">
                    <a href="{{ route('library.book.show', $relatedBook->id) }}">
                        <div class="h-48 bg-gray-200 flex items-center justify-center">
                            @if($relatedBook->cover_image)
                                <img src="{{ asset('storage/' . $relatedBook->cover_image) }}"
                                     alt="{{ $relatedBook->title }}"
                                     class="h-full w-full object-cover">
                            @else
                                <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                </svg>
                            @endif
                        </div>
                    </a>
                    <div class="p-4">
                        <a href="{{ route('library.book.show', $relatedBook->id) }}" class="block">
                            <h3 class="font-bold text-gray-800 hover:text-[#B91C1C] transition-colors line-clamp-2">
                                {{ $relatedBook->title }}
                            </h3>
                        </a>
                        <p class="text-sm text-gray-600 mt-1">{{ $relatedBook->publisher ?? 'Unknown Publisher' }}</p>
                        <div class="mt-2">
                            <span class="text-xs {{ $relatedBook->available_copies > 0 ? 'text-green-600' : 'text-red-600' }}">
                                {{ $relatedBook->available_copies > 0 ? $relatedBook->available_copies . ' available' : 'Unavailable' }}
                            </span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif

<!-- Library Info Footer -->
<section class="py-8 bg-gray-100">
    <div class="container mx-auto px-4">
        <div class="bg-white rounded-lg p-6 text-center max-w-2xl mx-auto">
            <h3 class="text-lg font-semibold text-gray-800 mb-2">Need Help?</h3>
            <p class="text-gray-600 mb-4">Contact our library staff for assistance with reservations or book availability.</p>
            <div class="flex justify-center space-x-4">
                <div class="flex items-center text-gray-600">
                    <svg class="w-5 h-5 mr-2 text-[#B91C1C]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                    <span>library@kenswed.ac.ke</span>
                </div>
                <div class="flex items-center text-gray-600">
                    <svg class="w-5 h-5 mr-2 text-[#B91C1C]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                    </svg>
                    <span>+254 700 000 000</span>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
