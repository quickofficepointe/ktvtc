@extends('layouts.app')
@section('title', $faq->question . ' | Kenswed College FAQ')
@section('meta_description', strip_tags(Str::limit($faq->answer, 150)))
@section('meta_keywords', 'Kenswed FAQ, ' . Str::words($faq->question, 10, '') . ', college questions, technical training information')

<!-- Open Graph Tags -->
@section('og_title', $faq->question . ' | Kenswed FAQ')
@section('og_description', strip_tags(Str::limit($faq->answer, 150)))
@section('og_url', url()->current())
@section('og_image', asset('Assets/images/Kenswed_logo.png'))

<!-- Twitter Card -->
@section('twitter_title', $faq->question)
@section('twitter_description', strip_tags(Str::limit($faq->answer, 150)))
@section('twitter_image', asset('Assets/images/Kenswed_logo.png'))

<!-- Canonical URL -->
@section('canonical', route('faq.show', $faq->slug))

@section('content')
<!-- FAQ Hero Section -->
<section class="bg-gradient-to-r from-[#B91C1C] to-[#BF1F30] py-16 text-white">
    <div class="container mx-auto px-4">
        <div class="max-w-4xl mx-auto">
            <nav class="flex mb-4" aria-label="Breadcrumb">
                <ol class="flex items-center space-x-2 text-sm">
                    <li><a href="{{ route('home') }}" class="hover:underline">Home</a></li>
                    <li><span class="text-white">/</span></li>
                    <li><a href="{{ route('faq.index') }}" class="hover:underline">FAQ</a></li>
                    <li><span class="text-white">/</span></li>
                    <li class="text-white font-semibold">{{ Str::limit($faq->question, 40) }}</li>
                </ol>
            </nav>

            <h1 class="text-3xl md:text-4xl font-bold mb-4">{{ $faq->question }}</h1>
            <p class="text-lg opacity-90">Detailed answer to your question</p>
        </div>
    </div>
</section>

<!-- FAQ Content Section -->
<section class="py-16 bg-white">
    <div class="container mx-auto px-4">
        <div class="max-w-4xl mx-auto">
            <!-- Single FAQ Content -->
            <div class="bg-gray-50 rounded-2xl p-8 mb-8">
                <div class="flex items-start space-x-6">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center">
                            <span class="text-lg font-bold text-[#B91C1C]">Q</span>
                        </div>
                    </div>
                    <div class="flex-1">
                        <h2 class="text-2xl font-bold text-gray-900 mb-4">{{ $faq->question }}</h2>

                        <div class="prose max-w-none text-gray-700 leading-relaxed text-lg">
                            {!! $faq->answer !!}
                        </div>

                        <!-- FAQ Meta -->
                        <div class="mt-8 pt-6 border-t border-gray-200">
                            <div class="flex items-center justify-between text-sm text-gray-600">
                                <div class="flex items-center space-x-4">
                                    <span class="flex items-center">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        Last updated: {{ $faq->updated_at->format('F j, Y') }}
                                    </span>
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium {{ $faq->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                        {{ $faq->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </div>

                                <!-- Share Buttons -->
                                <div class="flex items-center space-x-3">
                                    <span class="text-gray-600">Share:</span>
                                    <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(url()->current()) }}"
                                       target="_blank"
                                       class="text-gray-500 hover:text-[#B91C1C] transition-colors">
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                                        </svg>
                                    </a>
                                    <a href="https://twitter.com/intent/tweet?text={{ urlencode($faq->question . ' - Kenswed FAQ') }}&url={{ urlencode(url()->current()) }}"
                                       target="_blank"
                                       class="text-gray-500 hover:text-[#B91C1C] transition-colors">
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/>
                                        </svg>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Navigation Buttons -->
            <div class="flex flex-col sm:flex-row gap-4 justify-between">
                <a href="{{ route('faq.index') }}"
                   class="inline-flex items-center px-6 py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors font-semibold">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Back to All FAQs
                </a>

                <a href="{{ route('contact') }}"
                   class="inline-flex items-center px-6 py-3 bg-[#B91C1C] text-white rounded-lg hover:bg-[#991B1B] transition-colors font-semibold">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                    </svg>
                    Still Need Help? Contact Us
                </a>
            </div>
        </div>
    </div>
</section>

<style>
    .prose {
        max-width: none;
    }

    .prose p {
        margin-bottom: 1.5rem;
        line-height: 1.7;
    }

    .prose ul, .prose ol {
        margin-bottom: 1.5rem;
        padding-left: 1.5rem;
    }

    .prose li {
        margin-bottom: 0.75rem;
        line-height: 1.6;
    }

    .prose strong {
        font-weight: 600;
        color: #374151;
    }
</style>
@endsection
