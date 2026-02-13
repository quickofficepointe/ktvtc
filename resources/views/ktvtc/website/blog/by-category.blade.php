@extends('layouts.app')

@section('seo')
    <meta name="description" content="Browse all {{ $category->name }} news and articles from Kenswed Technical College.">
    <meta name="keywords" content="{{ $category->name }}, college news, Kenswed updates, {{ $category->name }} articles">
    <meta property="og:title" content="{{ $category->name }} - Kenswed College News">
    <meta property="og:description" content="Browse all {{ $category->name }} news and articles from Kenswed Technical College.">
    <meta property="og:image" content="{{ $category->cover_image ? Storage::url($category->cover_image) : asset('Assets/images/Kenswed_logo.png') }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:type" content="website">
    <meta name="twitter:card" content="summary_large_image">
@endsection

@section('title', $category->name . ' - Kenswed Technical College News')

@section('content')
<!-- Category Hero Section -->
<section class="bg-gradient-to-r from-[#B91C1C] to-[#BF1F30] py-16 text-white">
    <div class="container mx-auto px-4">
        <div class="text-center">
            <nav class="flex mb-4 justify-center" aria-label="Breadcrumb">
                <ol class="flex items-center space-x-2 text-sm">
                    <li><a href="{{ route('home') }}" class="hover:underline">Home</a></li>
                    <li><span class="text-white">/</span></li>
                    <li><a href="{{ route('blog.index') }}" class="hover:underline">News</a></li>
                    <li><span class="text-white">/</span></li>
                    <li class="text-white font-semibold">{{ $category->name }}</li>
                </ol>
            </nav>

            <h1 class="text-4xl md:text-5xl font-bold mb-4">{{ $category->name }}</h1>
            @if($category->description)
            <p class="text-xl opacity-90 max-w-2xl mx-auto">{{ $category->description }}</p>
            @endif
        </div>
    </div>
</section>

<!-- Category Content Section -->
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
                              bg-white text-gray-700 border border-gray-300 hover:border-[#B91C1C] hover:text-[#B91C1C]">
                        All News
                    </a>
                    @foreach($categories as $cat)
                    <a href="{{ route('blog.by-category', $cat->slug) }}"
                       class="px-5 py-2.5 rounded-full text-sm font-semibold transition-all duration-200
                              {{ $cat->id == $category->id ? 'bg-[#B91C1C] text-white shadow-lg' : 'bg-white text-gray-700 border border-gray-300 hover:border-[#B91C1C] hover:text-[#B91C1C]' }}">
                        {{ $cat->name }} ({{ $cat->blogs_count }})
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
                    <h3 class="text-2xl font-bold text-gray-800 mb-3">No Articles in {{ $category->name }}</h3>
                    <p class="text-gray-600 max-w-md mx-auto mb-6">There are no articles available in this category yet. Please check back later.</p>
                    <a href="{{ route('blog.index') }}" class="inline-flex items-center px-6 py-3 bg-[#B91C1C] text-white font-semibold rounded-lg hover:bg-[#991B1B] transition-colors">
                        Browse All News
                    </a>
                </div>
                @endif
            </div>

            <!-- Sidebar -->
            <div class="lg:col-span-1">
                <!-- Category Info -->
                <div class="bg-white rounded-xl p-6 shadow-lg border border-gray-200 mb-8">
                    <h3 class="text-xl font-bold text-gray-800 mb-4 border-b border-gray-200 pb-2">About {{ $category->name }}</h3>
                    @if($category->description)
                    <p class="text-gray-600 text-sm leading-relaxed mb-4">{{ $category->description }}</p>
                    @endif
                    <div class="flex items-center justify-between py-2 px-3 bg-red-50 rounded-lg">
                        <span class="text-sm font-semibold text-[#B91C1C]">Total Articles</span>
                        <span class="bg-[#B91C1C] text-white text-sm font-bold px-3 py-1 rounded-full">{{ $category->blogs_count }}</span>
                    </div>
                </div>

                <!-- Categories -->
                @if($categories->count() > 0)
                <div class="bg-white rounded-xl p-6 shadow-lg border border-gray-200">
                    <h3 class="text-xl font-bold text-gray-800 mb-4 border-b border-gray-200 pb-2">All Categories</h3>
                    <div class="space-y-2">
                        @foreach($categories as $cat)
                        <a href="{{ route('blog.by-category', $cat->slug) }}" class="flex items-center justify-between py-2 px-3 rounded-lg hover:bg-red-50 transition-colors group {{ $cat->id == $category->id ? 'bg-red-50 border border-red-200' : '' }}">
                            <span class="text-gray-700 group-hover:text-[#B91C1C] font-medium {{ $cat->id == $category->id ? 'text-[#B91C1C]' : '' }}">{{ $cat->name }}</span>
                            <span class="bg-red-100 text-[#B91C1C] text-xs font-semibold px-2 py-1 rounded-full">{{ $cat->blogs_count }}</span>
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
</style>
@endsection
