@extends('layouts.app')

@section('seo')
    <meta name="description" content="Explore our gallery showcasing student life, campus facilities, events, and activities at Kenswed Technical College.">
    <meta name="keywords" content="gallery, photos, campus life, student activities, events, Kenswed College">
    <meta property="og:title" content="Gallery - Kenswed Technical College">
    <meta property="og:description" content="Explore our gallery showcasing student life, campus facilities, events, and activities at Kenswed Technical College.">
    <meta property="og:image" content="{{ asset('Assets/images/Kenswed_logo.png') }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:type" content="website">
    <meta name="twitter:card" content="summary_large_image">
@endsection

@section('title', 'Gallery - Kenswed Technical College')

@section('content')
<!-- Gallery Hero Section -->
<section class="bg-gradient-to-r from-[#B91C1C] to-[#BF1F30] py-16 text-white">
    <div class="container mx-auto px-4">
        <div class="text-center">
            <h1 class="text-4xl md:text-5xl font-bold mb-4">Photo Gallery</h1>
            <p class="text-xl opacity-90 max-w-2xl mx-auto">Explore moments from campus life, events, and activities at Kenswed Technical College</p>
        </div>
    </div>
</section>

<!-- Gallery Content Section -->
<section class="py-16 bg-gray-50">
    <div class="container mx-auto px-4">
        @if($galleries->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @foreach($galleries as $gallery)
            <div class="bg-white rounded-xl shadow-lg overflow-hidden hover:shadow-2xl transition-all duration-300 border border-gray-200 hover:border-[#B91C1C]/20 group cursor-pointer"
                 onclick="openGalleryModal({{ $gallery->id }})">
                <!-- Gallery Cover Image -->
                @if($gallery->cover_image)
                    <img src="{{ Storage::url($gallery->cover_image) }}" alt="{{ $gallery->title }}"
                         class="w-full h-64 object-cover group-hover:scale-105 transition-transform duration-300">
                @elseif($gallery->images->count() > 0)
                    <img src="{{ Storage::url($gallery->images->first()->image_path) }}" alt="{{ $gallery->title }}"
                         class="w-full h-64 object-cover group-hover:scale-105 transition-transform duration-300">
                @else
                    <div class="w-full h-64 bg-gradient-to-br from-[#B91C1C] to-[#BF1F30] flex items-center justify-center">
                        <svg class="w-16 h-16 text-white opacity-90" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                @endif

                <div class="p-6">
                    <!-- Status Badge -->
                    <div class="mb-3">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold {{ $gallery->is_active ? 'bg-green-100 text-green-800 border border-green-200' : 'bg-gray-100 text-gray-800 border border-gray-200' }}">
                            <span class="w-2 h-2 rounded-full mr-2 {{ $gallery->is_active ? 'bg-green-400' : 'bg-gray-400' }}"></span>
                            {{ $gallery->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </div>

                    <h3 class="text-xl font-bold text-gray-800 mb-3 leading-tight group-hover:text-[#B91C1C] transition-colors">{{ $gallery->title }}</h3>

                    <!-- Description -->
                    @if($gallery->description)
                    <p class="text-gray-600 text-sm leading-relaxed mb-4 line-clamp-2">
                        {{ $gallery->description }}
                    </p>
                    @endif

                    <!-- Image Count & Date -->
                    <div class="flex items-center justify-between text-sm text-gray-500">
                        <div class="flex items-center">
                            <svg class="w-4 h-4 mr-2 text-[#B91C1C]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            {{ $gallery->images->count() }} photos
                        </div>
                        <span>{{ $gallery->created_at->format('M j, Y') }}</span>
                    </div>

                    <!-- View Gallery Button -->
                    <div class="mt-4 pt-4 border-t border-gray-200">
                        <button class="w-full flex items-center justify-center px-4 py-2 bg-[#B91C1C] text-white rounded-lg hover:bg-[#991B1B] transition-colors duration-200 font-semibold text-sm">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            View Gallery
                        </button>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div class="text-center py-16 bg-white rounded-xl shadow-lg border border-gray-200">
            <svg class="w-20 h-20 mx-auto text-gray-400 mb-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            <h3 class="text-2xl font-bold text-gray-800 mb-3">No Galleries Available</h3>
            <p class="text-gray-600 max-w-md mx-auto mb-6">We're currently updating our photo galleries. Please check back later to see our campus life and events.</p>
            <a href="{{ route('home') }}" class="inline-flex items-center px-6 py-3 bg-[#B91C1C] text-white font-semibold rounded-lg hover:bg-[#991B1B] transition-colors">
                Return to Homepage
            </a>
        </div>
        @endif
    </div>
</section>

<!-- Gallery Modal -->
<div id="galleryModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 py-6">
        <div class="fixed inset-0 bg-black bg-opacity-90 transition-opacity" onclick="closeGalleryModal()"></div>

        <div class="relative bg-white rounded-2xl shadow-2xl max-w-6xl w-full max-h-[90vh] overflow-hidden transform transition-all">
            <!-- Modal Header -->
            <div class="bg-gradient-to-r from-[#B91C1C] to-[#BF1F30] px-6 py-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="p-2 bg-white bg-opacity-20 rounded-lg">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 id="modalGalleryTitle" class="text-xl font-bold text-white">Gallery</h3>
                            <p id="modalGalleryCount" class="text-red-100 text-sm">Loading...</p>
                        </div>
                    </div>
                    <button onclick="closeGalleryModal()"
                            class="p-2 hover:bg-white hover:bg-opacity-20 rounded-lg transition-colors">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Modal Body -->
            <div class="p-6 overflow-y-auto max-h-[calc(90vh-80px)]">
                <div id="modalGalleryContent" class="space-y-6">
                    <!-- Content will be loaded via JavaScript -->
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .line-clamp-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .grid-cols-auto-fill {
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    }
</style>

<script>
    function openGalleryModal(galleryId) {
        // Fetch gallery data
        fetch(`/galleries/${galleryId}`)
            .then(response => response.json())
            .then(gallery => {
                const modal = document.getElementById('galleryModal');
                modal.classList.remove('hidden');
                document.body.style.overflow = 'hidden';

                // Update modal title and info
                document.getElementById('modalGalleryTitle').textContent = gallery.title;
                document.getElementById('modalGalleryCount').textContent = `${gallery.images.length} photos`;

                // Build gallery content
                let content = `
                    <div class="space-y-6">
                        ${gallery.description ? `
                        <div class="bg-gray-50 rounded-lg p-4">
                            <p class="text-gray-700 leading-relaxed">${gallery.description}</p>
                        </div>
                        ` : ''}

                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                `;

                gallery.images.forEach((image, index) => {
                    content += `
                        <div class="group relative bg-white rounded-lg overflow-hidden shadow-md hover:shadow-xl transition-all duration-300 transform hover:scale-105">
                            <img src="/storage/${image.image_path}"
                                 alt="${image.caption || gallery.title}"
                                 class="w-full h-48 object-cover cursor-pointer"
                                 onclick="openImageLightbox('${image.image_path}', ${index}, ${gallery.images.length})">

                            ${image.caption ? `
                            <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-40 transition-all duration-300 flex items-end">
                                <div class="p-3 text-white transform translate-y-full group-hover:translate-y-0 transition-transform duration-300">
                                    <p class="text-sm font-medium">${image.caption}</p>
                                </div>
                            </div>
                            ` : ''}
                        </div>
                    `;
                });

                content += `
                        </div>
                    </div>
                `;

                document.getElementById('modalGalleryContent').innerHTML = content;
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error loading gallery data');
            });
    }

    function closeGalleryModal() {
        document.getElementById('galleryModal').classList.add('hidden');
        document.body.style.overflow = 'auto';
    }

    function openImageLightbox(imagePath, currentIndex, totalImages) {
        // You can implement a lightbox here for individual image viewing
        // For now, we'll just open the image in a new tab
        window.open(`/storage/${imagePath}`, '_blank');
    }

    // Close modal on Escape key
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closeGalleryModal();
        }
    });

    // Close modal when clicking on backdrop
    document.addEventListener('click', function(event) {
        if (event.target.classList.contains('fixed') && event.target.classList.contains('inset-0')) {
            closeGalleryModal();
        }
    });
</script>
@endsection
