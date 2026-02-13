@extends('layouts.app')

@section('seo')
    <meta name="description" content="Stay updated with the latest news, events, and activities at Kenswed Technical College.">
    <meta name="keywords" content="college news, events, updates, Kenswed news, student activities">
    <meta property="og:title" content="News & Media - Kenswed Technical College">
    <meta property="og:description" content="Stay updated with the latest news, events, and activities at Kenswed Technical College.">
    <meta property="og:image" content="{{ asset('Assets/images/Kenswed_logo.png') }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:type" content="website">
    <meta name="twitter:card" content="summary_large_image">
@endsection

@section('title', 'News & Media - Kenswed Technical College')

@section('content')
<!-- News Hero Section -->
<section class="bg-gradient-to-r from-[#B91C1C] to-[#BF1F30] py-16 text-white">
    <div class="container mx-auto px-4">
        <div class="text-center">
            <h1 class="text-4xl md:text-5xl font-bold mb-4">News & Media</h1>
            <p class="text-xl opacity-90 max-w-2xl mx-auto">Stay updated with the latest news, events, and activities at Kenswed College</p>
        </div>
    </div>
</section>

<!-- News Content Section -->
<section class="py-16 bg-gray-50">
    <div class="container mx-auto px-4">
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
            <!-- Main Content -->
            <div class="lg:col-span-3">
                <!-- Categories Filter -->
                @if($categories->count() > 0)
                <div class="flex flex-wrap gap-3 mb-8">
                    <a href="{{ route('blog.index') }}"
                       class="px-5 py-2.5 rounded-full text-sm font-semibold transition-all duration-200
                              {{ request()->is('news') ? 'bg-[#B91C1C] text-white shadow-lg' : 'bg-white text-gray-700 border border-gray-300 hover:border-[#B91C1C] hover:text-[#B91C1C]' }}">
                        All News
                    </a>
                    @foreach($categories as $category)
                    <a href="{{ route('blog.by-category', $category->slug) }}"
                       class="px-5 py-2.5 rounded-full text-sm font-semibold transition-all duration-200
                              {{ request()->is('news/category/'.$category->slug) ? 'bg-[#B91C1C] text-white shadow-lg' : 'bg-white text-gray-700 border border-gray-300 hover:border-[#B91C1C] hover:text-[#B91C1C]' }}">
                        {{ $category->name }} ({{ $category->blogs_count }})
                    </a>
                    @endforeach
                </div>
                @endif

                @if($blogs->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-8">
                    @foreach($blogs as $blog)
                    <div class="bg-white rounded-xl shadow-lg overflow-hidden hover:shadow-2xl transition-all duration-300 border border-gray-200 hover:border-[#B91C1C]/20 group">
                        <!-- Blog Image -->
                        @if($blog->cover_image)
                            <img src="{{ Storage::url($blog->cover_image) }}" alt="{{ $blog->title }}" class="w-full h-48 object-cover group-hover:scale-105 transition-transform duration-300">
                        @else
                            <div class="w-full h-48 bg-gradient-to-br from-[#B91C1C] to-[#BF1F30] flex items-center justify-center">
                                <svg class="w-16 h-16 text-white opacity-90" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9m0 0v12m0 0h6m-6 0h6"/>
                                </svg>
                            </div>
                        @endif

                        <div class="p-6">
                            <!-- Category Badge -->
                            @if($blog->category)
                            <div class="mb-3">
                                <a href="{{ route('blog.by-category', $blog->category->slug) }}"
                                   class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-semibold bg-red-50 text-[#B91C1C] border border-red-200 hover:bg-red-100 transition-colors">
                                    {{ $blog->category->name }}
                                </a>
                            </div>
                            @endif

                            <h3 class="text-xl font-bold text-gray-800 mb-3 leading-tight group-hover:text-[#B91C1C] transition-colors">{{ $blog->title }}</h3>

                            <!-- Published Date -->
                            <div class="flex items-center text-sm text-gray-600 mb-3">
                                <svg class="w-4 h-4 mr-2 text-[#B91C1C]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                {{ $blog->published_at->format('M j, Y') }}
                            </div>

                            <!-- Excerpt -->
                            <p class="text-gray-600 text-sm leading-relaxed mb-4 line-clamp-3">
                                {{ Str::limit(strip_tags($blog->content), 120) }}
                            </p>

                            <!-- Read More Button -->
                            <a href="{{ route('blog.show', [$blog->category->slug, $blog->slug]) }}"
                               class="inline-flex items-center text-[#B91C1C] font-semibold text-sm hover:underline group-hover:text-[#991B1B] transition-colors">
                                Read More
                                <svg class="w-4 h-4 ml-1 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                                </svg>
                            </a>
                        </div>
                    </div>
                    @endforeach
                </div>

                <!-- Pagination -->
                @if($blogs->hasPages())
                <div class="mt-12 flex justify-center">
                    <div class="flex space-x-2">
                        @if($blogs->onFirstPage())
                        <span class="px-4 py-2 bg-gray-200 text-gray-500 rounded-lg cursor-not-allowed">Previous</span>
                        @else
                        <a href="{{ $blogs->previousPageUrl() }}" class="px-4 py-2 bg-white text-[#B91C1C] border border-[#B91C1C] rounded-lg hover:bg-red-50 transition-colors">Previous</a>
                        @endif

                        @foreach($blogs->getUrlRange(1, $blogs->lastPage()) as $page => $url)
                        <a href="{{ $url }}" class="px-4 py-2 rounded-lg transition-colors {{ $blogs->currentPage() == $page ? 'bg-[#B91C1C] text-white' : 'bg-white text-[#B91C1C] border border-[#B91C1C] hover:bg-red-50' }}">
                            {{ $page }}
                        </a>
                        @endforeach

                        @if($blogs->hasMorePages())
                        <a href="{{ $blogs->nextPageUrl() }}" class="px-4 py-2 bg-white text-[#B91C1C] border border-[#B91C1C] rounded-lg hover:bg-red-50 transition-colors">Next</a>
                        @else
                        <span class="px-4 py-2 bg-gray-200 text-gray-500 rounded-lg cursor-not-allowed">Next</span>
                        @endif
                    </div>
                </div>
                @endif

                @else
                <div class="text-center py-16 bg-white rounded-xl shadow-lg border border-gray-200">
                    <svg class="w-20 h-20 mx-auto text-gray-400 mb-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9m0 0v12m0 0h6m-6 0h6"/>
                    </svg>
                    <h3 class="text-2xl font-bold text-gray-800 mb-3">No News Available</h3>
                    <p class="text-gray-600 max-w-md mx-auto mb-6">We're currently updating our news section. Please check back later for the latest updates.</p>
                    <a href="/" class="inline-flex items-center px-6 py-3 bg-[#B91C1C] text-white font-semibold rounded-lg hover:bg-[#991B1B] transition-colors">
                        Return to Homepage
                    </a>
                </div>
                @endif
            </div>

            <!-- Sidebar -->
            <div class="lg:col-span-1">
                <!-- Featured News -->
                @if($featuredBlogs->count() > 0)
                <div class="bg-white rounded-xl p-6 shadow-lg border border-gray-200 mb-8">
                    <h3 class="text-xl font-bold text-gray-800 mb-4 border-b border-gray-200 pb-2">Featured News</h3>
                    <div class="space-y-4">
                        @foreach($featuredBlogs as $featuredBlog)
                        <a href="{{ route('blog.show', [$featuredBlog->category->slug, $featuredBlog->slug]) }}" class="block group">
                            <div class="flex items-start space-x-3">
                                @if($featuredBlog->cover_image)
                                <img src="{{ Storage::url($featuredBlog->cover_image) }}" alt="{{ $featuredBlog->title }}" class="w-16 h-16 object-cover rounded-lg flex-shrink-0">
                                @else
                                <div class="w-16 h-16 bg-gradient-to-br from-[#B91C1C] to-[#BF1F30] rounded-lg flex items-center justify-center flex-shrink-0">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9m0 0v12m0 0h6m-6 0h6"/>
                                    </svg>
                                </div>
                                @endif
                                <div class="flex-1 min-w-0">
                                    <h4 class="text-sm font-semibold text-gray-800 group-hover:text-[#B91C1C] transition-colors line-clamp-2">{{ $featuredBlog->title }}</h4>
                                    <p class="text-xs text-gray-600 mt-1">{{ $featuredBlog->published_at->format('M j, Y') }}</p>
                                </div>
                            </div>
                        </a>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- Categories -->
                @if($categories->count() > 0)
                <div class="bg-white rounded-xl p-6 shadow-lg border border-gray-200">
                    <h3 class="text-xl font-bold text-gray-800 mb-4 border-b border-gray-200 pb-2">Categories</h3>
                    <div class="space-y-2">
                        @foreach($categories as $category)
                        <a href="{{ route('blog.by-category', $category->slug) }}" class="flex items-center justify-between py-2 px-3 rounded-lg hover:bg-red-50 transition-colors group">
                            <span class="text-gray-700 group-hover:text-[#B91C1C] font-medium">{{ $category->name }}</span>
                            <span class="bg-red-100 text-[#B91C1C] text-xs font-semibold px-2 py-1 rounded-full">{{ $category->blogs_count }}</span>
                        </a>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</section>

<style>
    .line-clamp-3 {
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    .line-clamp-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
</style>
@endsection
