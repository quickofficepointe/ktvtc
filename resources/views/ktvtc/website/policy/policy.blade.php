@extends('layouts.app')

@section('seo')
    <meta name="description" content="Read our policies and terms at Kenswed Technical College. Learn about our privacy policy, terms of service, and other important documents.">
    <meta name="keywords" content="policies, terms and conditions, privacy policy, student policies, Kenswed College">
    <meta property="og:title" content="Policies & Terms - Kenswed Technical College">
    <meta property="og:description" content="Read our policies and terms at Kenswed Technical College. Learn about our privacy policy, terms of service, and other important documents.">
    <meta property="og:image" content="{{ asset('Assets/images/Kenswed_logo.png') }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:type" content="website">
    <meta name="twitter:card" content="summary_large_image">
@endsection

@section('title', 'Policies & Terms - Kenswed Technical College')

@section('content')
<!-- Policies Hero Section -->
<section class="bg-gradient-to-r from-[#B91C1C] to-[#BF1F30] py-16 text-white">
    <div class="container mx-auto px-4">
        <div class="text-center">
            <h1 class="text-4xl md:text-5xl font-bold mb-4">Policies & Terms</h1>
            <p class="text-xl opacity-90 max-w-2xl mx-auto">Important documents governing our operations and your relationship with Kenswed Technical College</p>
        </div>
    </div>
</section>

<!-- Policies Content Section -->
<section class="py-16 bg-gray-50">
    <div class="container mx-auto px-4">
        @if($policies->count() > 0)
        <div class="max-w-4xl mx-auto">
            <!-- Policies List -->
            <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden">
                <!-- Header -->
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <h2 class="text-lg font-semibold text-gray-900">Our Policies</h2>
                    <p class="text-sm text-gray-600 mt-1">Click on any policy to read the full document</p>
                </div>

                <!-- Policies Table -->
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Policy Document</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Last Updated</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Action</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($policies as $policy)
                            <tr class="hover:bg-gray-50 transition-colors duration-150">
                                <!-- Policy Title & Preview -->
                                <td class="px-6 py-4">
                                    <div class="flex items-start space-x-4">
                                        <div class="flex-shrink-0 w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center">
                                            <svg class="w-6 h-6 text-[#B91C1C]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                            </svg>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <h3 class="text-lg font-semibold text-gray-900 mb-1">{{ $policy->title }}</h3>
                                            <p class="text-sm text-gray-600 line-clamp-2 max-w-2xl">
                                                {{ Str::limit(strip_tags($policy->content), 150) }}
                                            </p>
                                        </div>
                                    </div>
                                </td>

                                <!-- Status -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium {{ $policy->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                        <span class="w-2 h-2 rounded-full mr-2 {{ $policy->is_active ? 'bg-green-400' : 'bg-gray-400' }}"></span>
                                        {{ $policy->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>

                                <!-- Last Updated -->
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $policy->updated_at->format('M j, Y') }}
                                </td>

                                <!-- Action -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <a href="{{ route('policies.show', $policy->slug) }}"
                                       class="inline-flex items-center px-4 py-2 bg-[#B91C1C] text-white rounded-lg hover:bg-[#991B1B] transition-colors duration-200 font-semibold text-sm">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                        Read Policy
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Footer -->
                <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                    <p class="text-sm text-gray-600">
                        Showing <span class="font-semibold">{{ $policies->count() }}</span> policy documents
                    </p>
                </div>
            </div>

            <!-- Contact Information -->
            <div class="mt-12 bg-gradient-to-r from-[#B91C1C] to-[#BF1F30] rounded-2xl p-8 text-white">
                <div class="text-center">
                    <h3 class="text-2xl font-bold mb-4">Questions About Our Policies?</h3>
                    <p class="text-red-100 mb-6 max-w-md mx-auto">If you have any questions or concerns about our policies, please don't hesitate to contact us.</p>
                    <a href="{{ route('contact') }}"
                       class="inline-flex items-center bg-white text-[#B91C1C] px-8 py-3 rounded-lg font-semibold hover:bg-gray-100 transition-colors duration-200">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                        </svg>
                        Contact Us
                    </a>
                </div>
            </div>
        </div>
        @else
        <div class="text-center py-16 bg-white rounded-xl shadow-lg border border-gray-200">
            <svg class="w-20 h-20 mx-auto text-gray-400 mb-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <h3 class="text-2xl font-bold text-gray-800 mb-3">No Policies Available</h3>
            <p class="text-gray-600 max-w-md mx-auto mb-6">We're currently updating our policy documents. Please check back later.</p>
            <a href="/" class="inline-flex items-center px-6 py-3 bg-[#B91C1C] text-white font-semibold rounded-lg hover:bg-[#991B1B] transition-colors">
                Return to Homepage
            </a>
        </div>
        @endif
    </div>
</section>

<style>
    .line-clamp-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
</style>
@endsection
