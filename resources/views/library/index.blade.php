@extends('layouts.app')

@section('title', 'Library Catalog | Kenswed Technical College')
@section('meta_description', 'Browse our library collection of books, journals, and resources at Kenswed Technical College.')

@section('content')
<!-- Library Header -->
<section class="bg-gradient-to-r from-[#B91C1C] to-[#BF1F30] py-12 text-white">
    <div class="container mx-auto px-4">
        <div class="text-center max-w-4xl mx-auto">
            <h1 class="text-4xl md:text-5xl font-bold mb-4">Library Catalog</h1>
            <p class="text-xl opacity-90">Discover our collection of books, journals, and educational resources</p>
        </div>
    </div>
</section>

<!-- Search & Filter Section -->
<section class="py-8 bg-gray-100 border-b">
    <div class="container mx-auto px-4">
        <form method="GET" action="{{ route('library.index') }}" class="max-w-4xl mx-auto">
            <div class="flex flex-col md:flex-row gap-4">
                <div class="flex-1">
                    <input type="text"
                           name="search"
                           value="{{ request('search') }}"
                           placeholder="Search by title, author, or ISBN..."
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#B91C1C] focus:border-transparent">
                </div>
                <div class="w-full md:w-64">
                    <select name="category"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#B91C1C] focus:border-transparent">
                        <option value="">All Categories</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="w-full md:w-48">
                    <select name="availability"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#B91C1C] focus:border-transparent">
                        <option value="">All Books</option>
                        <option value="available" {{ request('availability') == 'available' ? 'selected' : '' }}>Available Now</option>
                    </select>
                </div>
                <button type="submit"
                        class="bg-[#B91C1C] text-white px-6 py-3 rounded-lg hover:bg-[#991B1B] transition-colors">
                    Search
                </button>
            </div>
        </form>
    </div>
</section>

<!-- Books Grid -->
<section class="py-12">
    <div class="container mx-auto px-4">
        @if($books->count() > 0)
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                @foreach($books as $book)
                    <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow">
                        <a href="{{ route('library.show', $book->slug) }}">
                            <div class="h-64 bg-gray-200 flex items-center justify-center">
                                @if($book->cover_image)
                                    <img src="{{ asset('storage/' . $book->cover_image) }}"
                                         alt="{{ $book->title }}"
                                         class="h-full w-full object-cover">
                                @else
                                    <svg class="w-20 h-20 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                    </svg>
                                @endif
                            </div>
                        </a>
                        <div class="p-4">
                            <a href="{{ route('library.show', $book->slug) }}" class="block">
                                <h3 class="font-bold text-gray-800 hover:text-[#B91C1C] transition-colors line-clamp-2">
                                    {{ $book->title }}
                                </h3>
                            </a>
                            <p class="text-sm text-gray-600 mt-1">by {{ $book->author->name ?? 'Unknown' }}</p>
                            <p class="text-xs text-gray-500 mt-1">{{ $book->category->name ?? 'General' }}</p>

                            <div class="mt-3 flex items-center justify-between">
                                <span class="text-sm {{ $book->available_copies > 0 ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $book->available_copies > 0 ? $book->available_copies . ' copy(s) available' : 'Currently Unavailable' }}
                                </span>
                                @auth
                                    @if($book->available_copies > 0)
                                        <a href="{{ route('library.reserve', $book->id) }}"
                                           class="bg-[#B91C1C] text-white text-sm px-3 py-1 rounded hover:bg-[#991B1B] transition-colors">
                                            Reserve
                                        </a>
                                    @else
                                        <a href="{{ route('library.reserve', $book->id) }}"
                                           class="bg-gray-400 text-white text-sm px-3 py-1 rounded hover:bg-gray-500 transition-colors">
                                            Waitlist
                                        </a>
                                    @endif
                                @else
                                    <a href="{{ route('login') }}" class="text-[#B91C1C] text-sm hover:underline">
                                        Login to Reserve
                                    </a>
                                @endauth
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="mt-8">
                {{ $books->withQueryString()->links() }}
            </div>
        @else
            <div class="text-center py-12">
                <svg class="w-24 h-24 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
                <h3 class="text-xl font-semibold text-gray-800 mb-2">No books found</h3>
                <p class="text-gray-600">Try adjusting your search or filter criteria.</p>
            </div>
        @endif
    </div>
</section>
@endsection
