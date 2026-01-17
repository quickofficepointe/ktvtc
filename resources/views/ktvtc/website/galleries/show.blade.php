@extends('layouts.app')

@section('seo')
    <meta name="description" content="{{ $gallery->description ?? 'Explore photos from ' . $gallery->title . ' at Kenswed Technical College.' }}">
    <meta name="keywords" content="{{ $gallery->title }}, gallery, photos, Kenswed College">
    <meta property="og:title" content="{{ $gallery->title }} - Kenswed Gallery">
    <meta property="og:description" content="{{ $gallery->description ?? 'Explore photos from ' . $gallery->title . ' at Kenswed Technical College.' }}">
    <meta property="og:image" content="{{ $gallery->cover_image ? Storage::url($gallery->cover_image) : ($gallery->images->count() > 0 ? Storage::url($gallery->images->first()->image_path) : asset('Assets/images/Kenswed_logo.png')) }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:type" content="website">
    <meta name="twitter:card" content="summary_large_image">
@endsection

@section('title', $gallery->title . ' - Kenswed Technical College Gallery')

@section('content')
<!-- Gallery Hero Section -->
<section class="bg-gradient-to-r from-[#B91C1C] to-[#BF1F30] py-16 text-white">
    <div class="container mx-auto px-4">
        <div class="max-w-6xl mx-auto">
            <nav class="flex mb-4" aria-label="Breadcrumb">
                <ol class="flex items-center space-x-2 text-sm">
                    <li><a href="{{ route('home') }}" class="hover:underline">Home</a></li>
                    <li><span class="text-white">/</span></li>
                    <li><a href="{{ route('galleries.index') }}" class="hover:underline">Gallery</a></li>
                    <li><span class="text-white">/</span></li>
                    <li class="text-white font-semibold">{{ Str::limit($gallery->title, 40) }}</li>
                </ol>
            </nav>

            <h1 class="text-3xl md:text-4xl font-bold mb-4">{{ $gallery->title }}</h1>
            @if($gallery->description)
            <p class="text-lg opacity-90 max-w-2xl">{{ $gallery->description }}</p>
            @endif
        </div>
    </div>
</section>

<!-- Gallery Content Section -->
<section class="py-16 bg-white">
    <div class="container mx-auto px-4">
        <div class="max-w-6xl mx-auto">
            <!-- Gallery Info -->
            <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8">
                <div class="flex items-center space-x-4 mb-4 md:mb-0">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                        {{ $gallery->images->count() }} photos
                    </span>
                    <span class="text-gray-600 text-sm">
                        Created: {{ $gallery->created_at->format('F j, Y') }}
                    </span>
                </div>
                <a href="{{ route('galleries.index') }}"
                   class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors font-semibold">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Back to All Galleries
                </a>
            </div>

            <!-- Photos Grid -->
            @if($gallery->images->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($gallery->images as $image)
                <div class="group relative bg-white rounded-xl overflow-hidden shadow-lg hover:shadow-2xl transition-all duration-300 transform hover:scale-105">
                    <img src="{{ Storage::url($image->image_path) }}"
                         alt="{{ $image->caption ?? $gallery->title }}"
                         class="w-full h-64 object-cover cursor-pointer"
                         onclick="openLightbox('{{ Storage::url($image->image_path) }}', '{{ $image->caption ?? $gallery->title }}')">

                    @if($image->caption)
                    <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-40 transition-all duration-300 flex items-end">
                        <div class="p-4 text-white transform translate-y-full group-hover:translate-y-0 transition-transform duration-300 w-full">
                            <p class="text-sm font-medium">{{ $image->caption }}</p>
                        </div>
                    </div>
                    @endif
                </div>
                @endforeach
            </div>
            @else
            <div class="text-center py-16 bg-gray-50 rounded-xl">
                <svg class="w-20 h-20 mx-auto text-gray-400 mb-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <h3 class="text-xl font-bold text-gray-800 mb-2">No Photos in This Gallery</h3>
                <p class="text-gray-600">This gallery doesn't contain any photos yet.</p>
            </div>
            @endif
        </div>
    </div>
</section>

<!-- Lightbox Modal -->
<div id="lightboxModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 py-6">
        <div class="fixed inset-0 bg-black bg-opacity-95 transition-opacity" onclick="closeLightbox()"></div>

        <div class="relative max-w-4xl w-full max-h-[90vh] transform transition-all">
            <button onclick="closeLightbox()"
                    class="absolute top-4 right-4 z-10 p-2 bg-black bg-opacity-50 text-white rounded-full hover:bg-opacity-70 transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>

            <div class="bg-white rounded-lg overflow-hidden">
                <img id="lightboxImage" src="" alt="" class="w-full h-auto max-h-[70vh] object-contain">
                <div id="lightboxCaption" class="p-4 text-center text-gray-800 font-medium"></div>
            </div>
        </div>
    </div>
</div>

<script>
    function openLightbox(imageSrc, caption) {
        document.getElementById('lightboxImage').src = imageSrc;
        document.getElementById('lightboxCaption').textContent = caption;
        document.getElementById('lightboxModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeLightbox() {
        document.getElementById('lightboxModal').classList.add('hidden');
        document.body.style.overflow = 'auto';
    }

    // Close lightbox on Escape key
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closeLightbox();
        }
    });
</script>

<style>
    .grid-cols-auto-fill {
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    }
</style>
@endsection
