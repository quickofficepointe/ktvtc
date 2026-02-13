@extends('layouts.app')

@section('seo')
    <meta name="description" content="Download important documents, forms, and resources from Kenswed Technical College.">
    <meta name="keywords" content="downloads, documents, forms, resources, Kenswed College">
    <meta property="og:title" content="Downloads - Kenswed Technical College">
    <meta property="og:description" content="Download important documents, forms, and resources from Kenswed Technical College.">
    <meta property="og:image" content="{{ asset('Assets/images/Kenswed_logo.png') }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:type" content="website">
    <meta name="twitter:card" content="summary_large_image">
@endsection

@section('title', 'Downloads - Kenswed Technical College')

@section('content')
<!-- Downloads Hero Section -->
<section class="bg-gradient-to-r from-[#B91C1C] to-[#BF1F30] py-16 text-white">
    <div class="container mx-auto px-4">
        <div class="text-center">
            <h1 class="text-4xl md:text-5xl font-bold mb-4">Downloads</h1>
            <p class="text-xl opacity-90 max-w-2xl mx-auto">Access important documents, forms, and resources for students and applicants</p>
        </div>
    </div>
</section>

<!-- Downloads Content Section -->
<section class="py-16 bg-gray-50">
    <div class="container mx-auto px-4">
        @if($downloads->count() > 0)
        <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden">
            <!-- Table Header -->
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <h2 class="text-lg font-semibold text-gray-900">Available Downloads</h2>
                <p class="text-sm text-gray-600 mt-1">Click the download icon to get your files</p>
            </div>

            <!-- Downloads Table -->
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Document</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Description</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">File Type</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Download</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($downloads as $download)
                        <tr class="hover:bg-gray-50 transition-colors duration-150">
                            <!-- Document Title -->
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center">
                                        @php
                                            $extension = pathinfo($download->file_path, PATHINFO_EXTENSION);
                                            $fileType = strtolower($extension);
                                        @endphp

                                        @if(in_array($fileType, ['pdf']))
                                            <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                            </svg>
                                        @elseif(in_array($fileType, ['doc', 'docx']))
                                            <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                            </svg>
                                        @elseif(in_array($fileType, ['xls', 'xlsx']))
                                            <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                            </svg>
                                        @else
                                            <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                            </svg>
                                        @endif
                                    </div>
                                    <div class="ml-4">
                                        <h3 class="text-lg font-semibold text-gray-900">{{ $download->title }}</h3>
                                        <p class="text-sm text-gray-500 mt-1">
                                            {{ strtoupper($fileType) }} •
                                            @php
                                                $fileSize = Storage::disk('public')->size($download->file_path);
                                                $fileSizeFormatted = round($fileSize / 1024 / 1024, 2) . ' MB';
                                            @endphp
                                            {{ $fileSizeFormatted }}
                                        </p>
                                    </div>
                                </div>
                            </td>

                            <!-- Description -->
                            <!-- Description -->
<td class="px-6 py-4">
    @if($download->description)
        <p class="text-gray-600 text-sm leading-relaxed max-w-xs">{!! $download->description !!}</p>
    @else
        <span class="text-gray-400 text-sm">No description</span>
    @endif
</td>

                            <!-- File Type -->
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    {{ strtoupper($fileType) }}
                                </span>
                            </td>

                            <!-- Status -->
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium {{ $download->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                    <span class="w-2 h-2 rounded-full mr-2 {{ $download->is_active ? 'bg-green-400' : 'bg-gray-400' }}"></span>
                                    {{ $download->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>

                            <!-- Download Action -->
                            <td class="px-6 py-4">
                                @if($download->is_active)
                                <a href="{{ Storage::url($download->file_path) }}"
                                   download
                                   class="inline-flex items-center px-4 py-2 bg-[#B91C1C] text-white rounded-lg hover:bg-[#991B1B] transition-colors duration-200 font-semibold text-sm">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                    </svg>
                                    Download
                                </a>
                                @else
                                <span class="inline-flex items-center px-4 py-2 bg-gray-300 text-gray-600 rounded-lg cursor-not-allowed font-semibold text-sm">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                    </svg>
                                    Unavailable
                                </span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Table Footer -->
            <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                <div class="flex items-center justify-between">
                    <p class="text-sm text-gray-600">
                        Showing <span class="font-semibold">{{ $downloads->count() }}</span> documents
                    </p>
                    <p class="text-sm text-gray-600">
                        Need help? <a href="{{ route('contact') }}" class="text-[#B91C1C] hover:underline font-semibold">Contact us</a>
                    </p>
                </div>
            </div>
        </div>
        @else
        <div class="text-center py-16 bg-white rounded-xl shadow-lg border border-gray-200">
            <svg class="w-20 h-20 mx-auto text-gray-400 mb-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
            </svg>
            <h3 class="text-2xl font-bold text-gray-800 mb-3">No Downloads Available</h3>
            <p class="text-gray-600 max-w-md mx-auto mb-6">There are currently no documents available for download. Please check back later.</p>
            <a href="/" class="inline-flex items-center px-6 py-3 bg-[#B91C1C] text-white font-semibold rounded-lg hover:bg-[#991B1B] transition-colors">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                Return to Homepage
            </a>
        </div>
        @endif
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
