@extends('layouts.app')

@section('title', 'Upcoming Events & Workshops | Kenswed Technical College')
@section('meta_description', 'Discover upcoming events, workshops, bootcamps, and seminars at Kenswed Technical and Vocational Training College. Join our technical training events in Ngong, Kenya.')
@section('meta_keywords', 'college events, technical workshops, vocational training seminars, bootcamps Kenya, student activities, Kenswed events, Ngong events, technical education workshops')

<!-- Open Graph Tags -->
@section('og_title', 'Upcoming Events & Workshops | Kenswed Technical College')
@section('og_description', 'Join technical workshops, bootcamps, and seminars at Kenswed College. Enhance your skills with our vocational training events in Ngong.')
@section('og_url', url()->current())
@section('og_image', $events->count() > 0 && $events->first()->cover_image ? Storage::url($events->first()->cover_image) : asset('Assets/images/Kenswed_logo.png'))

<!-- Twitter Card -->
@section('twitter_title', 'Events & Workshops | Kenswed College')
@section('twitter_description', 'Technical workshops, bootcamps, and vocational training events at Kenswed Technical College in Ngong, Kenya.')
@section('twitter_image', $events->count() > 0 && $events->first()->cover_image ? Storage::url($events->first()->cover_image) : asset('Assets/images/Kenswed_logo.png'))

<!-- Canonical URL -->
@section('canonical', url()->current())
@section('content')
<!-- Events Hero Section -->
<section class="bg-gradient-to-r from-[#B91C1C] to-[#BF1F30] py-16 text-white">
    <div class="container mx-auto px-4">
        <div class="text-center">
            <h1 class="text-4xl md:text-5xl font-bold mb-4">College Events</h1>
            <p class="text-xl opacity-90 max-w-2xl mx-auto">Discover upcoming events, workshops, and activities at Kenswed College</p>
        </div>
    </div>
</section>

<!-- Events Content Section -->
<section class="py-16 bg-gray-50">
    <div class="container mx-auto px-4">
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
            <!-- Main Content -->
            <div class="lg:col-span-3">
                <!-- Event Type Filter -->
                <div class="flex flex-wrap gap-3 mb-8">
                    <a href="{{ route('events.index') }}"
                       class="px-5 py-2.5 rounded-full text-sm font-semibold transition-all duration-200
                              {{ request()->is('events') ? 'bg-[#B91C1C] text-white shadow-lg' : 'bg-white text-gray-700 border border-gray-300 hover:border-[#B91C1C] hover:text-[#B91C1C]' }}">
                        All Events
                    </a>
                    @php
                        $eventTypes = ['bootcamp', 'workshop', 'seminar', 'conference', 'trip', 'mentorship', 'social', 'other'];
                    @endphp
                    @foreach($eventTypes as $type)
                    <a href="{{ route('events.by-type', $type) }}"
                       class="px-5 py-2.5 rounded-full text-sm font-semibold transition-all duration-200
                              {{ request()->is('events/type/'.$type) ? 'bg-[#B91C1C] text-white shadow-lg' : 'bg-white text-gray-700 border border-gray-300 hover:border-[#B91C1C] hover:text-[#B91C1C]' }}">
                        {{ ucfirst($type) }}
                    </a>
                    @endforeach
                </div>

                @if($events->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-8">
                    @foreach($events as $event)
                    <div class="bg-white rounded-xl shadow-lg overflow-hidden hover:shadow-2xl transition-all duration-300 border border-gray-200 hover:border-[#B91C1C]/20 group">
                        <!-- Event Image -->
                        @if($event->cover_image)
                            <img src="{{ Storage::url($event->cover_image) }}" alt="{{ $event->title }}" class="w-full h-48 object-cover group-hover:scale-105 transition-transform duration-300">
                        @else
                            <div class="w-full h-48 bg-gradient-to-br from-[#B91C1C] to-[#BF1F30] flex items-center justify-center">
                                <svg class="w-16 h-16 text-white opacity-90" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </div>
                        @endif

                        <div class="p-6">
                            <!-- Event Type Badge -->
                            <div class="mb-3">
                                <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-semibold bg-red-50 text-[#B91C1C] border border-red-200">
                                    {{ ucfirst($event->event_type) }}
                                </span>
                            </div>

                            <h3 class="text-xl font-bold text-gray-800 mb-3 leading-tight group-hover:text-[#B91C1C] transition-colors">{{ $event->title }}</h3>

                            <!-- Event Details -->
                            <div class="space-y-2 mb-4">
                                <div class="flex items-center text-sm text-gray-600">
                                    <svg class="w-4 h-4 mr-2 text-[#B91C1C]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                    {{ $event->location }}
                                </div>

                                <div class="flex items-center text-sm text-gray-600">
                                    <svg class="w-4 h-4 mr-2 text-[#B91C1C]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    {{ \Carbon\Carbon::parse($event->event_start_date)->format('M j, Y') }}
                                    @if($event->event_end_date && $event->event_end_date != $event->event_start_date)
                                        - {{ \Carbon\Carbon::parse($event->event_end_date)->format('M j, Y') }}
                                    @endif
                                </div>

                                @if($event->is_paid)
                                <div class="flex items-center text-sm text-gray-600">
                                    <svg class="w-4 h-4 mr-2 text-[#B91C1C]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                                    </svg>
                                    KSh {{ number_format($event->price) }}
                                    @if($event->early_bird_price && $event->early_bird_end_date > now())
                                        <span class="ml-2 px-2 py-1 bg-green-100 text-green-800 text-xs rounded-full">Early Bird: KSh {{ number_format($event->early_bird_price) }}</span>
                                    @endif
                                </div>
                                @else
                                <div class="flex items-center text-sm text-green-600 font-semibold">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    Free Event
                                </div>
                                @endif
                            </div>

                            <!-- Short Description -->
                            @if($event->short_description)
                            <p class="text-gray-600 text-sm leading-relaxed mb-4 line-clamp-2">
                                {{ $event->short_description }}
                            </p>
                            @endif

                            <!-- Event Status & Actions -->
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-4">
                                    @if($event->registration_start_date && $event->registration_end_date)
                                        @if(now()->between($event->registration_start_date, $event->registration_end_date))
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800">
                                                Registration Open
                                            </span>
                                        @elseif(now() < $event->registration_start_date)
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-800">
                                                Registration Opens Soon
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-800">
                                                Registration Closed
                                            </span>
                                        @endif
                                    @endif

                                    @if($event->max_attendees)
                                    <span class="text-xs text-gray-500">
                                        {{ $event->registered_attendees ?? 0 }}/{{ $event->max_attendees }} attendees
                                    </span>
                                    @endif
                                </div>

                                <!-- View Details Button -->
                                <a href="{{ route('events.show', $event->slug) }}"
                                   class="inline-flex items-center text-[#B91C1C] font-semibold text-sm hover:underline group-hover:text-[#991B1B] transition-colors">
                                    View Details
                                    <svg class="w-4 h-4 ml-1 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>

                <!-- Pagination -->
                @if($events->hasPages())
                <div class="mt-12 flex justify-center">
                    <div class="flex space-x-2">
                        @if($events->onFirstPage())
                        <span class="px-4 py-2 bg-gray-200 text-gray-500 rounded-lg cursor-not-allowed">Previous</span>
                        @else
                        <a href="{{ $events->previousPageUrl() }}" class="px-4 py-2 bg-white text-[#B91C1C] border border-[#B91C1C] rounded-lg hover:bg-red-50 transition-colors">Previous</a>
                        @endif

                        @foreach($events->getUrlRange(1, $events->lastPage()) as $page => $url)
                        <a href="{{ $url }}" class="px-4 py-2 rounded-lg transition-colors {{ $events->currentPage() == $page ? 'bg-[#B91C1C] text-white' : 'bg-white text-[#B91C1C] border border-[#B91C1C] hover:bg-red-50' }}">
                            {{ $page }}
                        </a>
                        @endforeach

                        @if($events->hasMorePages())
                        <a href="{{ $events->nextPageUrl() }}" class="px-4 py-2 bg-white text-[#B91C1C] border border-[#B91C1C] rounded-lg hover:bg-red-50 transition-colors">Next</a>
                        @else
                        <span class="px-4 py-2 bg-gray-200 text-gray-500 rounded-lg cursor-not-allowed">Next</span>
                        @endif
                    </div>
                </div>
                @endif

                @else
                <div class="text-center py-16 bg-white rounded-xl shadow-lg border border-gray-200">
                    <svg class="w-20 h-20 mx-auto text-gray-400 mb-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <h3 class="text-2xl font-bold text-gray-800 mb-3">No Upcoming Events</h3>
                    <p class="text-gray-600 max-w-md mx-auto mb-6">There are currently no upcoming events scheduled. Please check back later for new events and activities.</p>
                    <a href="/" class="inline-flex items-center px-6 py-3 bg-[#B91C1C] text-white font-semibold rounded-lg hover:bg-[#991B1B] transition-colors">
                        Return to Homepage
                    </a>
                </div>
                @endif
            </div>

            <!-- Sidebar -->
            <div class="lg:col-span-1">
                <!-- Featured Events -->
                @if($featuredEvents->count() > 0)
                <div class="bg-white rounded-xl p-6 shadow-lg border border-gray-200 mb-8">
                    <h3 class="text-xl font-bold text-gray-800 mb-4 border-b border-gray-200 pb-2">Featured Events</h3>
                    <div class="space-y-4">
                        @foreach($featuredEvents as $featuredEvent)
                        <a href="{{ route('events.show', $featuredEvent->slug) }}" class="block group">
                            <div class="flex items-start space-x-3">
                                @if($featuredEvent->cover_image)
                                <img src="{{ Storage::url($featuredEvent->cover_image) }}" alt="{{ $featuredEvent->title }}" class="w-16 h-16 object-cover rounded-lg flex-shrink-0">
                                @else
                                <div class="w-16 h-16 bg-gradient-to-br from-[#B91C1C] to-[#BF1F30] rounded-lg flex items-center justify-center flex-shrink-0">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                                @endif
                                <div class="flex-1 min-w-0">
                                    <h4 class="text-sm font-semibold text-gray-800 group-hover:text-[#B91C1C] transition-colors line-clamp-2">{{ $featuredEvent->title }}</h4>
                                    <p class="text-xs text-gray-600 mt-1">{{ \Carbon\Carbon::parse($featuredEvent->event_start_date)->format('M j, Y') }}</p>
                                    @if($featuredEvent->is_paid)
                                    <p class="text-xs text-[#B91C1C] font-semibold">KSh {{ number_format($featuredEvent->price) }}</p>
                                    @else
                                    <p class="text-xs text-green-600 font-semibold">Free</p>
                                    @endif
                                </div>
                            </div>
                        </a>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- Event Types -->
                <div class="bg-white rounded-xl p-6 shadow-lg border border-gray-200 mb-8">
                    <h3 class="text-xl font-bold text-gray-800 mb-4 border-b border-gray-200 pb-2">Event Types</h3>
                    <div class="space-y-2">
                        @foreach($eventTypes as $type)
                        <a href="{{ route('events.by-type', $type) }}" class="flex items-center justify-between py-2 px-3 rounded-lg hover:bg-red-50 transition-colors group">
                            <span class="text-gray-700 group-hover:text-[#B91C1C] font-medium capitalize">{{ $type }}</span>
                            <span class="bg-red-100 text-[#B91C1C] text-xs font-semibold px-2 py-1 rounded-full">
                                {{ $events->where('event_type', $type)->count() }}
                            </span>
                        </a>
                        @endforeach
                    </div>
                </div>

                <!-- Quick Info -->
                <div class="bg-gradient-to-br from-[#B91C1C] to-[#BF1F30] rounded-xl p-6 text-white">
                    <h3 class="text-xl font-bold mb-4">Need Help?</h3>
                    <p class="text-sm opacity-90 mb-4">Have questions about our events or need assistance with registration?</p>
                    <div class="space-y-2 text-sm">
                        <div class="flex items-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                            </svg>
                            Contact: {{ $event->organizer_phone ?? '254 790 148 509
' }}
                        </div>
                        <div class="flex items-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                            Email: {{ $event->organizer_email ?? 'events@ktvtc.ac.ke' }}
                        </div>
                    </div>
                </div>
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
