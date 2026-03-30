@extends('layouts.app')

@section('seo')
    <meta name="description" content="{{ $blog->meta_description ?? Str::limit(strip_tags($blog->content), 160) }}">
    <meta name="keywords" content="{{ $blog->meta_keywords ?? $blog->title }}, Kenswed news, college updates">
    <meta property="og:title" content="{{ $blog->title }} - Kenswed College">
    <meta property="og:description" content="{{ $blog->meta_description ?? Str::limit(strip_tags($blog->content), 160) }}">
    <meta property="og:image" content="{{ $blog->cover_image ? Storage::url($blog->cover_image) : asset('Assets/images/Kenswed_logo.png') }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:type" content="article">
    <meta name="twitter:card" content="summary_large_image">
@endsection

@section('title', $blog->title . ' - Kenswed Technical College')

@section('content')
<!-- Blog Hero Section -->
<section class="bg-gradient-to-r from-[#B91C1C] to-[#BF1F30] py-16 text-white">
    <div class="container mx-auto px-4">
        <div class="max-w-4xl mx-auto">
            <nav class="flex mb-4" aria-label="Breadcrumb">
                <ol class="flex items-center space-x-2 text-sm">
                    <li><a href="{{ route('home') }}" class="hover:underline">Home</a></li>
                    <li><span class="text-white">/</span></li>
                    <li><a href="{{ route('blog.index') }}" class="hover:underline">News</a></li>
                    <li><span class="text-white">/</span></li>
                    <li><a href="{{ route('blog.by-category', $blog->category->slug) }}" class="hover:underline">{{ $blog->category->name }}</a></li>
                    <li><span class="text-white">/</span></li>
                    <li class="text-white font-semibold">{{ Str::limit($blog->title, 40) }}</li>
                </ol>
            </nav>

            <h1 class="text-3xl md:text-4xl font-bold mb-4">{{ $blog->title }}</h1>

            <div class="flex flex-wrap items-center gap-4 text-sm">
                <!-- Category -->
                <a href="{{ route('blog.by-category', $blog->category->slug) }}"
                   class="inline-flex items-center px-3 py-1 bg-white bg-opacity-20 rounded-full hover:bg-opacity-30 transition-colors">
                    {{ $blog->category->name }}
                </a>

                <!-- Published Date -->
                <div class="flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    {{ $blog->published_at->format('F j, Y') }}
                </div>

                <!-- Reading Time -->
                @php
                    $wordCount = str_word_count(strip_tags($blog->content));
                    $readingTime = ceil($wordCount / 200);
                @endphp
                <div class="flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    {{ $readingTime }} min read
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Blog Content Section -->
<section class="py-16 bg-white">
    <div class="container mx-auto px-4">
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
            <!-- Main Content -->
            <div class="lg:col-span-3">
                <!-- Blog Image -->
                @if($blog->cover_image)
                <div class="mb-8 rounded-xl overflow-hidden shadow-lg">
                    <img src="{{ Storage::url($blog->cover_image) }}" alt="{{ $blog->title }}" class="w-full h-64 md:h-96 object-cover">
                </div>
                @endif

                <!-- Blog Content -->
                <article class="prose max-w-none prose-lg">
                    <div class="blog-content">
                        {!! $blog->content !!}
                    </div>
                </article>

                <!-- Social Sharing -->
                <div class="mt-12 pt-8 border-t border-gray-200">
                    <div class="flex items-center justify-between">
                        <span class="text-gray-700 font-medium">Share this article:</span>
                        <div class="flex space-x-3">
                            <!-- Facebook -->
                            <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(url()->current()) }}"
                               target="_blank"
                               class="bg-[#1877F2] text-white p-2 rounded-full hover:bg-[#166FE5] transition-colors">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                                </svg>
                            </a>

                            <!-- Twitter -->
                            <a href="https://twitter.com/intent/tweet?text={{ urlencode($blog->title) }}&url={{ urlencode(url()->current()) }}"
                               target="_blank"
                               class="bg-[#1DA1F2] text-white p-2 rounded-full hover:bg-[#0d8bd9] transition-colors">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/>
                                </svg>
                            </a>

                            <!-- LinkedIn -->
                            <a href="https://www.linkedin.com/sharing/share-offsite/?url={{ urlencode(url()->current()) }}"
                               target="_blank"
                               class="bg-[#0A66C2] text-white p-2 rounded-full hover:bg-[#004182] transition-colors">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
                                </svg>
                            </a>

                            <!-- WhatsApp -->
                            <a href="https://wa.me/?text={{ urlencode('Check out this article: ' . $blog->title . ' - ' . url()->current()) }}"
                               target="_blank"
                               class="bg-[#25D366] text-white p-2 rounded-full hover:bg-[#128C7E] transition-colors">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893c0-3.176-1.24-6.165-3.495-8.411"/>
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="lg:col-span-1">
                <!-- Related Articles -->
                @if($relatedBlogs->count() > 0)
                <div class="bg-white rounded-xl p-6 shadow-lg border border-gray-200 mb-8">
                    <h3 class="text-xl font-bold text-gray-800 mb-4 border-b border-gray-200 pb-2">Related Articles</h3>
                    <div class="space-y-4">
                        @foreach($relatedBlogs as $relatedBlog)
                        <a href="{{ route('blog.show', [$relatedBlog->category->slug, $relatedBlog->slug]) }}" class="block group">
                            <div class="flex items-start space-x-3">
                                @if($relatedBlog->cover_image)
                                <img src="{{ Storage::url($relatedBlog->cover_image) }}" alt="{{ $relatedBlog->title }}" class="w-16 h-16 object-cover rounded-lg flex-shrink-0">
                                @else
                                <div class="w-16 h-16 bg-gradient-to-br from-[#B91C1C] to-[#BF1F30] rounded-lg flex items-center justify-center flex-shrink-0">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9m0 0v12m0 0h6m-6 0h6"/>
                                    </svg>
                                </div>
                                @endif
                                <div class="flex-1 min-w-0">
                                    <h4 class="text-sm font-semibold text-gray-800 group-hover:text-[#B91C1C] transition-colors line-clamp-2">{{ $relatedBlog->title }}</h4>
                                    <p class="text-xs text-gray-600 mt-1">{{ $relatedBlog->published_at->format('M j, Y') }}</p>
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
    .blog-content {
        line-height: 1.8;
        color: #374151;
    }

    .blog-content h2 {
        font-size: 1.5rem;
        font-weight: bold;
        color: #1f2937;
        margin-top: 2rem;
        margin-bottom: 1rem;
        border-left: 4px solid #B91C1C;
        padding-left: 1rem;
    }

    .blog-content h3 {
        font-size: 1.25rem;
        font-weight: bold;
        color: #374151;
        margin-top: 1.5rem;
        margin-bottom: 0.75rem;
    }

    .blog-content p {
        margin-bottom: 1rem;
    }

    .blog-content ul, .blog-content ol {
        margin-bottom: 1rem;
        padding-left: 1.5rem;
    }

    .blog-content li {
        margin-bottom: 0.5rem;
    }

    .blog-content blockquote {
        border-left: 4px solid #B91C1C;
        padding-left: 1rem;
        margin: 1.5rem 0;
        font-style: italic;
        color: #6b7280;
    }

    .line-clamp-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
</style>
@endsection
