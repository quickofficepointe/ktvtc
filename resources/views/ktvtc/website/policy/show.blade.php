@extends('layouts.app')

@section('seo')
    <meta name="description" content="{{ Str::limit(strip_tags($policy->content), 160) }}">
    <meta name="keywords" content="{{ $policy->title }}, policy, terms, Kenswed College">
    <meta property="og:title" content="{{ $policy->title }} - Kenswed Technical College">
    <meta property="og:description" content="{{ Str::limit(strip_tags($policy->content), 160) }}">
    <meta property="og:image" content="{{ asset('Assets/images/Kenswed_logo.png') }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:type" content="article">
    <meta name="twitter:card" content="summary_large_image">
@endsection

@section('title', $policy->title . ' - Kenswed Technical College')

@section('content')
<!-- Policy Hero Section -->
<section class="bg-gradient-to-r from-[#B91C1C] to-[#BF1F30] py-16 text-white">
    <div class="container mx-auto px-4">
        <div class="max-w-4xl mx-auto">
            <nav class="flex mb-4" aria-label="Breadcrumb">
                <ol class="flex items-center space-x-2 text-sm">
                    <li><a href="{{ route('home') }}" class="hover:underline">Home</a></li>
                    <li><span class="text-white">/</span></li>
                    <li><a href="{{ route('policies.index') }}" class="hover:underline">Policies</a></li>
                    <li><span class="text-white">/</span></li>
                    <li class="text-white font-semibold">{{ Str::limit($policy->title, 40) }}</li>
                </ol>
            </nav>

            <h1 class="text-3xl md:text-4xl font-bold mb-4">{{ $policy->title }}</h1>
            <div class="flex flex-wrap items-center gap-4 text-sm">
                <span class="inline-flex items-center px-3 py-1 bg-white bg-opacity-20 rounded-full">
                    <span class="w-2 h-2 rounded-full mr-2 {{ $policy->is_active ? 'bg-green-400' : 'bg-gray-400' }}"></span>
                    {{ $policy->is_active ? 'Active Policy' : 'Inactive Policy' }}
                </span>
                <span class="inline-flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Last updated: {{ $policy->updated_at->format('F j, Y') }}
                </span>
            </div>
        </div>
    </div>
</section>

<!-- Policy Content Section -->
<section class="py-16 bg-white">
    <div class="container mx-auto px-4">
        <div class="max-w-4xl mx-auto">
            <!-- Policy Content -->
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <!-- Content Header -->
                <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <div class="p-2 bg-red-100 rounded-lg">
                                <svg class="w-6 h-6 text-[#B91C1C]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-lg font-semibold text-gray-900">Policy Document</h2>
                                <p class="text-sm text-gray-600">Effective as of {{ $policy->updated_at->format('F j, Y') }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Policy Content -->
                <div class="p-6 md:p-8">
                    <article class="prose max-w-none policy-content">
                        {!! $policy->content !!}
                    </article>

                    <!-- Last Updated & Contact -->
                    <div class="mt-12 pt-8 border-t border-gray-200">
                        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                            <div class="text-sm text-gray-600">
                                <p><strong>Last Updated:</strong> {{ $policy->updated_at->format('F j, Y \\a\\t g:i A') }}</p>
                                @if($policy->created_at != $policy->updated_at)
                                <p><strong>Originally Published:</strong> {{ $policy->created_at->format('F j, Y') }}</p>
                                @endif
                            </div>
                            <a href="{{ route('contact') }}"
                               class="inline-flex items-center px-4 py-2 border border-[#B91C1C] text-[#B91C1C] rounded-lg hover:bg-red-50 transition-colors font-semibold text-sm">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                </svg>
                                Questions? Contact Us
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Navigation Buttons -->
            <div class="flex flex-col sm:flex-row gap-4 justify-between mt-8">
                <a href="{{ route('policies.index') }}"
                   class="inline-flex items-center px-6 py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors font-semibold">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Back to All Policies
                </a>

                <div class="flex space-x-3">
                    <!-- Print Button -->
                    <button onclick="window.print()"
                           class="inline-flex items-center px-4 py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors font-semibold">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                        </svg>
                        Print
                    </button>

                    <!-- Download as PDF (you can implement this functionality) -->
                    <button onclick="downloadAsPDF()"
                           class="inline-flex items-center px-4 py-3 bg-[#B91C1C] text-white rounded-lg hover:bg-[#991B1B] transition-colors font-semibold">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Download PDF
                    </button>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
    function downloadAsPDF() {
        // You can implement PDF download functionality here
        // For now, we'll show an alert
        alert('PDF download functionality can be implemented here. This would generate a PDF version of the policy.');

        // Example implementation could use libraries like:
        // - jsPDF
        // - Window print with CSS for PDF
        // - Server-side PDF generation
    }

    // Add print styles
    const style = document.createElement('style');
    style.textContent = `
        @media print {
            .bg-gradient-to-r { background: #B91C1C !important; }
            .text-white { color: black !important; }
            .bg-white { background: white !important; }
            .shadow-lg { box-shadow: none !important; }
            .border { border: 1px solid #ccc !important; }
            .hidden { display: none !important; }
            .no-print { display: none !important; }
            .policy-content { font-size: 12pt; line-height: 1.6; }
        }
    `;
    document.head.appendChild(style);
</script>

<style>
    .policy-content {
        line-height: 1.8;
        color: #374151;
        font-size: 16px;
    }

    .policy-content h2 {
        font-size: 1.5rem;
        font-weight: bold;
        color: #1f2937;
        margin-top: 2rem;
        margin-bottom: 1rem;
        border-left: 4px solid #B91C1C;
        padding-left: 1rem;
    }

    .policy-content h3 {
        font-size: 1.25rem;
        font-weight: bold;
        color: #374151;
        margin-top: 1.5rem;
        margin-bottom: 0.75rem;
    }

    .policy-content p {
        margin-bottom: 1.5rem;
    }

    .policy-content ul, .policy-content ol {
        margin-bottom: 1.5rem;
        padding-left: 1.5rem;
    }

    .policy-content li {
        margin-bottom: 0.75rem;
        line-height: 1.6;
    }

    .policy-content strong {
        font-weight: 600;
        color: #374151;
    }

    .policy-content blockquote {
        border-left: 4px solid #B91C1C;
        padding-left: 1rem;
        margin: 1.5rem 0;
        font-style: italic;
        color: #6b7280;
    }

    .policy-content table {
        width: 100%;
        border-collapse: collapse;
        margin: 1.5rem 0;
    }

    .policy-content th,
    .policy-content td {
        border: 1px solid #e5e7eb;
        padding: 0.75rem;
        text-align: left;
    }

    .policy-content th {
        background-color: #f9fafb;
        font-weight: 600;
    }
</style>
@endsection
