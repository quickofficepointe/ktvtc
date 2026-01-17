@extends('layouts.app')

@section('title', 'Frequently Asked Questions | Kenswed Technical College')
@section('meta_description', 'Get answers to common questions about admissions, courses, fees, student life, and more at Kenswed Technical and Vocational Training College in Ngong, Kenya.')
@section('meta_keywords', 'Kenswed College FAQ, admissions questions, course information, tuition fees, student life, technical college Kenya, vocational training questions, college application process')

<!-- Open Graph Tags -->
@section('og_title', 'FAQ - Kenswed Technical College')
@section('og_description', 'Find answers to frequently asked questions about admissions, courses, and student life at Kenswed College in Ngong.')
@section('og_url', url()->current())
@section('og_image', asset('Assets/images/Kenswed_logo.png'))

<!-- Twitter Card -->
@section('twitter_title', 'FAQ - Kenswed College')
@section('twitter_description', 'Common questions about admissions, courses, and student life at Kenswed Technical College')
@section('twitter_image', asset('Assets/images/Kenswed_logo.png'))

<!-- Canonical URL -->
@section('canonical', url()->current())
@section('content')
<!-- FAQ Hero Section -->
<section class="bg-gradient-to-r from-[#B91C1C] to-[#BF1F30] py-16 text-white">
    <div class="container mx-auto px-4">
        <div class="text-center">
            <h1 class="text-4xl md:text-5xl font-bold mb-4">Frequently Asked Questions</h1>
            <p class="text-xl opacity-90 max-w-2xl mx-auto">Find answers to common questions about admissions, courses, student life, and more at Kenswed College</p>
        </div>
    </div>
</section>

<!-- FAQ Content Section -->
<section class="py-16 bg-gray-50">
    <div class="container mx-auto px-4">
        @if($faqs->count() > 0)
        <div class="max-w-4xl mx-auto">
            <!-- Search Box -->
            <div class="mb-8">
                <div class="relative">
                    <input type="text" id="faqSearch" placeholder="Search questions..."
                           class="w-full px-6 py-4 pl-12 rounded-xl border border-gray-300 focus:ring-2 focus:ring-[#B91C1C] focus:border-transparent transition-all duration-200 shadow-sm">
                    <svg class="absolute left-4 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
            </div>

            <!-- FAQ Accordion -->
            <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden">
                @foreach($faqs->where('is_active', true) as $index => $faq)
                <div class="faq-item border-b border-gray-200 last:border-b-0" data-search="{{ strtolower($faq->question . ' ' . $faq->answer) }}">
                    <button class="faq-question w-full px-6 py-6 text-left hover:bg-gray-50 transition-colors duration-200 flex items-center justify-between group"
                            onclick="toggleFaq({{ $index }})">
                        <div class="flex items-start space-x-4">
                            <div class="flex-shrink-0 w-8 h-8 bg-red-100 rounded-full flex items-center justify-center mt-1">
                                <span class="text-sm font-semibold text-[#B91C1C]">Q</span>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 group-hover:text-[#B91C1C] transition-colors">{{ $faq->question }}</h3>
                                <p class="text-sm text-gray-500 mt-1">Click to view answer</p>
                            </div>
                        </div>
                        <svg class="w-6 h-6 text-gray-400 transform transition-transform duration-200 faq-arrow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>

                    <div class="faq-answer px-6 pb-6 hidden" id="faq-answer-{{ $index }}">
                        <div class="flex items-start space-x-4">
                            <div class="flex-shrink-0 w-8 h-8 bg-green-100 rounded-full flex items-center justify-center mt-1">
                                <span class="text-sm font-semibold text-green-600">A</span>
                            </div>
                            <div class="flex-1">
                                <div class="prose max-w-none text-gray-700 leading-relaxed">
                                    {!! $faq->answer !!}
                                </div>
                                <div class="mt-4 flex items-center justify-between">
                                    <a href="{{ route('faq.show', $faq->slug) }}"
                                       class="inline-flex items-center text-sm text-[#B91C1C] hover:underline font-medium">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/>
                                        </svg>
                                        Share this answer
                                    </a>
                                    <span class="text-xs text-gray-500">Last updated: {{ $faq->updated_at->format('M j, Y') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            <!-- No Results Message -->
            <div id="noResults" class="hidden text-center py-12 bg-white rounded-xl shadow-lg border border-gray-200 mt-6">
                <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <h3 class="text-xl font-bold text-gray-800 mb-2">No matching questions found</h3>
                <p class="text-gray-600 mb-4">Try searching with different keywords or browse all questions.</p>
                <button onclick="clearSearch()" class="text-[#B91C1C] hover:underline font-semibold">
                    Clear search and show all questions
                </button>
            </div>

            <!-- Contact CTA -->
            <div class="mt-12 text-center">
                <div class="bg-gradient-to-r from-[#B91C1C] to-[#BF1F30] rounded-2xl p-8 text-white">
                    <h3 class="text-2xl font-bold mb-4">Still have questions?</h3>
                    <p class="text-red-100 mb-6 max-w-md mx-auto">Can't find the answer you're looking for? Our team is here to help you.</p>
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
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <h3 class="text-2xl font-bold text-gray-800 mb-3">No FAQs Available</h3>
            <p class="text-gray-600 max-w-md mx-auto mb-6">We're currently updating our frequently asked questions. Please check back later or contact us for assistance.</p>
            <a href="{{ route('contact') }}" class="inline-flex items-center px-6 py-3 bg-[#B91C1C] text-white font-semibold rounded-lg hover:bg-[#991B1B] transition-colors">
                Contact Support
            </a>
        </div>
        @endif
    </div>
</section>

<script>
    // FAQ Toggle Function
    function toggleFaq(index) {
        const answer = document.getElementById(`faq-answer-${index}`);
        const arrow = document.querySelectorAll('.faq-arrow')[index];

        if (answer.classList.contains('hidden')) {
            answer.classList.remove('hidden');
            arrow.classList.add('rotate-180');
        } else {
            answer.classList.add('hidden');
            arrow.classList.remove('rotate-180');
        }
    }

    // Search Functionality
    document.getElementById('faqSearch').addEventListener('input', function(e) {
        const searchTerm = e.target.value.toLowerCase();
        const faqItems = document.querySelectorAll('.faq-item');
        const noResults = document.getElementById('noResults');
        let visibleCount = 0;

        faqItems.forEach(item => {
            const searchData = item.getAttribute('data-search');
            if (searchData.includes(searchTerm)) {
                item.style.display = 'block';
                visibleCount++;
            } else {
                item.style.display = 'none';
            }
        });

        if (visibleCount === 0 && searchTerm.length > 0) {
            noResults.classList.remove('hidden');
        } else {
            noResults.classList.add('hidden');
        }
    });

    // Clear Search
    function clearSearch() {
        document.getElementById('faqSearch').value = '';
        document.querySelectorAll('.faq-item').forEach(item => {
            item.style.display = 'block';
        });
        document.getElementById('noResults').classList.add('hidden');
    }

    // Open specific FAQ from URL hash
    document.addEventListener('DOMContentLoaded', function() {
        const hash = window.location.hash;
        if (hash) {
            const faqSlug = hash.substring(1);
            // You could implement logic to find and open the specific FAQ by slug
        }
    });
</script>

<style>
    .rotate-180 {
        transform: rotate(180deg);
    }

    .prose {
        max-width: none;
    }

    .prose p {
        margin-bottom: 1rem;
    }

    .prose ul, .prose ol {
        margin-bottom: 1rem;
        padding-left: 1.5rem;
    }

    .prose li {
        margin-bottom: 0.5rem;
    }
</style>
@endsection
