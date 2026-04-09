@extends('layouts.app')

@section('title', 'Library Catalog | Kenswed Technical College')
@section('meta_description', 'Browse our library collection of books, journals, and educational resources at Kenswed Technical College.')

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
        <form method="GET" action="{{ route('library.index') }}" class="max-w-5xl mx-auto">
            <div class="flex flex-col md:flex-row gap-4">
                <div class="flex-1">
                    <input type="text"
                           name="search"
                           value="{{ request('search') }}"
                           placeholder="Search by title, ISBN, or publisher..."
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

<!-- Branch Info Section -->
<section class="py-8 bg-white border-b">
    <div class="container mx-auto px-4">
        <h2 class="text-2xl font-bold text-gray-800 mb-6 text-center">Book Availability by Branch</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 max-w-4xl mx-auto">
            @foreach($branches as $branch)
                <div class="bg-gray-50 rounded-lg p-4 text-center border border-gray-200">
                    <div class="w-12 h-12 bg-[#B91C1C] bg-opacity-10 rounded-full flex items-center justify-center mx-auto mb-3">
                        <svg class="w-6 h-6 text-[#B91C1C]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                    </div>
                    <h3 class="font-semibold text-gray-800">{{ $branch->name }}</h3>
                    <p class="text-2xl font-bold text-[#B91C1C]">{{ $branch->items_count ?? 0 }}</p>
                    <p class="text-sm text-gray-600">Available Books</p>
                </div>
            @endforeach
        </div>
    </div>
</section>

<!-- Featured Books Section -->
<section class="py-12 bg-gray-50">
    <div class="container mx-auto px-4">
        <div class="flex justify-between items-center mb-8">
            <h2 class="text-2xl font-bold text-gray-800">Featured Books</h2>
            <a href="{{ route('library.index') }}" class="text-[#B91C1C] hover:underline">View All →</a>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @foreach($featuredBooks as $book)
                <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow">
                    <a href="{{ route('library.book.show', $book->id) }}">
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
                        <a href="{{ route('library.book.show', $book->id) }}" class="block">
                            <h3 class="font-bold text-gray-800 hover:text-[#B91C1C] transition-colors line-clamp-2">
                                {{ $book->title }}
                            </h3>
                        </a>
                        <p class="text-sm text-gray-600 mt-1">{{ $book->publisher ?? 'Unknown Publisher' }}</p>
                        <p class="text-xs text-gray-500 mt-1">{{ $book->category->name ?? 'General' }}</p>
                        <div class="mt-3">
                            <span class="text-sm {{ $book->available_copies > 0 ? 'text-green-600' : 'text-red-600' }}">
                                {{ $book->available_copies > 0 ? $book->available_copies . ' copy(s) available' : 'Currently Unavailable' }}
                            </span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

<!-- E-Books Section -->
<section class="py-12 bg-white">
    <div class="container mx-auto px-4">
        <div class="flex justify-between items-center mb-8">
            <div>
                <h2 class="text-2xl font-bold text-gray-800">Digital E-Books</h2>
                <p class="text-gray-600 mt-1">Read anytime, anywhere on any device</p>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @forelse($eBooks as $ebook)
                <div class="bg-gray-50 rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow">
                    <a href="{{ route('library.ebook.show', $ebook) }}">
                        <div class="h-48 bg-gradient-to-r from-[#B91C1C] to-[#BF1F30] flex items-center justify-center">
                            @if($ebook->cover_image)
                                <img src="{{ asset('storage/' . $ebook->cover_image) }}"
                                     alt="{{ $ebook->title }}"
                                     class="h-full w-full object-cover">
                            @else
                                <svg class="w-16 h-16 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                </svg>
                            @endif
                        </div>
                    </a>
                    <div class="p-4">
                        <a href="{{ route('library.ebook.show', $ebook) }}" class="block">
                            <h3 class="font-bold text-gray-800 hover:text-[#B91C1C] transition-colors line-clamp-2">
                                {{ $ebook->title }}
                            </h3>
                        </a>
                        <p class="text-sm text-gray-600 mt-1">by {{ $ebook->author }}</p>
                        <p class="text-xs text-gray-500">{{ $ebook->category->name ?? 'General' }}</p>
                        <div class="mt-3 flex items-center justify-between">
                            <span class="text-xs text-gray-500">{{ $ebook->file_format }} • {{ $ebook->formatted_file_size }}</span>
                            <a href="{{ route('library.ebook.show', $ebook) }}"
                               class="text-[#B91C1C] text-sm hover:underline">
                                Read Online
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full text-center py-12">
                    <p class="text-gray-500">No eBooks available at the moment.</p>
                </div>
            @endforelse
        </div>
    </div>
</section>

<!-- Donation CTA Section -->
<section class="py-12 bg-gradient-to-r from-[#B91C1C] to-[#BF1F30] text-white">
    <div class="container mx-auto px-4 text-center">
        <h2 class="text-3xl font-bold mb-4">Support Our Library</h2>
        <p class="text-lg opacity-90 max-w-2xl mx-auto mb-6">
            Donate books to help us expand our collection and serve our community better.
        </p>
        <a href="{{ route('library.donation-form') }}"
           class="inline-block bg-white text-[#B91C1C] px-8 py-3 rounded-lg font-semibold hover:bg-gray-100 transition-colors">
            Donate Books
        </a>
    </div>
</section>
@endsection
