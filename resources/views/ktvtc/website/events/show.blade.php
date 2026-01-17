@extends('layouts.app')
@section('title', $event->title . ' | Kenswed Technical and Vocational Training College')
@section('meta_description', $event->short_description ?: 'Join us for ' . $event->title . ' at Kenswed College. ' . $event->location . ' on ' . \Carbon\Carbon::parse($event->event_start_date)->format('F j, Y'))
@section('meta_keywords', $event->title . ', ' . $event->event_type . ', Kenswed College events, ' . $event->location . ', ' . ($event->department ?: '') . ', technical training events, vocational education Kenya')

<!-- Open Graph Tags -->
@section('og_title', $event->title . ' | KTVTC Events')
@section('og_description', $event->short_description ?: 'Don\'t miss ' . $event->title . ' at Kenswed College. ' . $event->location . ' - ' . \Carbon\Carbon::parse($event->event_start_date)->format('M j, Y'))
@section('og_url', url()->current())
@section('og_image', $event->banner_image ? Storage::url($event->banner_image) : asset('Assets/images/Kenswed_logo.png'))

<!-- Twitter Card -->
@section('twitter_title', $event->title . ' | Kenswed College')
@section('twitter_description', $event->short_description ?: 'Join us for ' . $event->title . ' at Kenswed Technical College')
@section('twitter_image', $event->banner_image ? Storage::url($event->banner_image) : asset('Assets/images/Kenswed_logo.png'))

<!-- Canonical URL -->
@section('canonical', url()->current())

<!-- Structured Data for Event -->
@section('structured_data')

@endsection



@section('content')
<!-- Event Hero Section -->
@if($event->banner_image)
<section class="relative h-96 bg-cover bg-center" style="background-image: url('{{ Storage::url($event->banner_image) }}')">
    <div class="absolute inset-0 bg-black bg-opacity-50"></div>
    <div class="relative container mx-auto px-4 h-full flex items-center">
        <div class="text-white max-w-3xl">
            <span class="inline-flex items-center px-4 py-2 bg-[#B91C1C] text-white text-sm font-semibold rounded-full mb-4">
                {{ ucfirst($event->event_type) }}
            </span>
            <h1 class="text-4xl md:text-5xl font-bold mb-4">{{ $event->title }}</h1>
            @if($event->short_description)
            <p class="text-xl opacity-90">{{ $event->short_description }}</p>
            @endif
        </div>
    </div>
</section>
@else
<section class="bg-gradient-to-r from-[#B91C1C] to-[#BF1F30] py-16 text-white">
    <div class="container mx-auto px-4">
        <div class="text-center max-w-4xl mx-auto">
            <span class="inline-flex items-center px-4 py-2 bg-white bg-opacity-20 text-white text-sm font-semibold rounded-full mb-4">
                {{ ucfirst($event->event_type) }}
            </span>
            <h1 class="text-4xl md:text-5xl font-bold mb-4">{{ $event->title }}</h1>
            @if($event->short_description)
            <p class="text-xl opacity-90">{{ $event->short_description }}</p>
            @endif
        </div>
    </div>
</section>
@endif

<!-- Event Details Section -->
<section class="py-16 bg-white">
    <div class="container mx-auto px-4">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Main Content -->
            <div class="lg:col-span-2">
                <!-- Event Description -->
                <div class="prose max-w-none mb-8">
                    @if($event->description)
                        {!! $event->description !!}
                    @else
                        <p class="text-gray-600 text-lg">More details about this event will be available soon. Please check back later or contact the organizers for more information.</p>
                    @endif
                </div>

                <!-- Event Highlights -->
                <div class="bg-gray-50 rounded-xl p-6 mb-8">
                    <h3 class="text-xl font-bold text-gray-800 mb-4">Event Highlights</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 text-[#B91C1C] mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            <div>
                                <p class="text-sm text-gray-600">Location</p>
                                <p class="font-semibold text-gray-800">{{ $event->location }}</p>
                            </div>
                        </div>

                        <div class="flex items-center">
                            <svg class="w-5 h-5 text-[#B91C1C] mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <div>
                                <p class="text-sm text-gray-600">Date & Time</p>
                                <p class="font-semibold text-gray-800">
                                    {{ \Carbon\Carbon::parse($event->event_start_date)->format('l, F j, Y') }}
                                    @if($event->event_start_date->format('H:i') != '00:00')
                                        at {{ $event->event_start_date->format('g:i A') }}
                                    @endif
                                </p>
                                @if($event->event_end_date && $event->event_end_date != $event->event_start_date)
                                <p class="text-sm text-gray-600">
                                    to {{ \Carbon\Carbon::parse($event->event_end_date)->format('l, F j, Y') }}
                                    @if($event->event_end_date->format('H:i') != '00:00')
                                        at {{ $event->event_end_date->format('g:i A') }}
                                    @endif
                                </p>
                                @endif
                            </div>
                        </div>

                        @if($event->target_audience)
                        <div class="flex items-center">
                            <svg class="w-5 h-5 text-[#B91C1C] mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
                            </svg>
                            <div>
                                <p class="text-sm text-gray-600">Target Audience</p>
                                <p class="font-semibold text-gray-800">{{ str_replace('_', ' ', ucfirst($event->target_audience)) }}</p>
                            </div>
                        </div>
                        @endif

                        @if($event->department)
                        <div class="flex items-center">
                            <svg class="w-5 h-5 text-[#B91C1C] mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                            <div>
                                <p class="text-sm text-gray-600">Department</p>
                                <p class="font-semibold text-gray-800">{{ $event->department }}</p>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Registration Information -->
                @if($event->registration_start_date && $event->registration_end_date)
                <div class="bg-green-50 border border-green-200 rounded-xl p-6 mb-8">
                    <h3 class="text-xl font-bold text-gray-800 mb-4">Registration Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <p class="text-sm text-gray-600">Registration Opens</p>
                            <p class="font-semibold text-gray-800">{{ \Carbon\Carbon::parse($event->registration_start_date)->format('M j, Y g:i A') }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Registration Closes</p>
                            <p class="font-semibold text-gray-800">{{ \Carbon\Carbon::parse($event->registration_end_date)->format('M j, Y g:i A') }}</p>
                        </div>
                    </div>

                    @php
                        $now = now();
                        $registrationOpen = $now->between($event->registration_start_date, $event->registration_end_date);
                        $registrationNotStarted = $now < $event->registration_start_date;
                        $registrationClosed = $now > $event->registration_end_date;
                    @endphp

                    <div class="flex items-center justify-between">
                        <div>
                            @if($registrationOpen)
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-green-100 text-green-800">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                Registration Open
                            </span>
                            @elseif($registrationNotStarted)
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-blue-100 text-blue-800">
                                Registration Opens Soon
                            </span>
                            @else
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-red-100 text-red-800">
                                Registration Closed
                            </span>
                            @endif
                        </div>

                       @if($registrationOpen)
    <a href="{{ route('event-applications.create', $event) }}"
       class="bg-[#B91C1C] text-white px-6 py-3 rounded-lg font-semibold hover:bg-[#991B1B] transition-colors rounded-lg inline-block">
        Register Now
    </a>
@endif

                    </div>
                </div>
                @endif
            </div>

            <!-- Sidebar -->
            <div class="lg:col-span-1">
                <!-- Event Summary Card -->
                <div class="bg-white rounded-xl shadow-lg border border-gray-200 sticky top-8">
                    @if($event->cover_image)
                    <img src="{{ Storage::url($event->cover_image) }}" alt="{{ $event->title }}" class="w-full h-48 object-cover rounded-t-xl">
                    @endif

                    <div class="p-6">
                        <!-- Pricing Information -->
                        <div class="mb-6">
                            @if($event->is_paid)
                            <div class="text-center mb-4">
                                <span class="text-3xl font-bold text-gray-800">KSh {{ number_format($event->price) }}</span>
                                <p class="text-sm text-gray-600">Regular Price</p>
                            </div>

                            @if($event->early_bird_price && $event->early_bird_end_date > now())
                            <div class="text-center mb-4 p-4 bg-green-50 rounded-lg">
                                <span class="text-2xl font-bold text-green-800">KSh {{ number_format($event->early_bird_price) }}</span>
                                <p class="text-sm text-green-700">Early Bird Price</p>
                                <p class="text-xs text-green-600 mt-1">Ends {{ $event->early_bird_end_date->format('M j, Y') }}</p>
                            </div>
                            @endif
                            @else
                            <div class="text-center mb-4 p-4 bg-green-50 rounded-lg">
                                <span class="text-2xl font-bold text-green-800">Free</span>
                                <p class="text-sm text-green-700">No registration fee</p>
                            </div>
                            @endif
                        </div>

                        <!-- Event Details -->
                        <div class="space-y-4 mb-6">
                            <div class="flex justify-between items-center py-2 border-b border-gray-200">
                                <span class="text-gray-600">Event Type</span>
                                <span class="font-semibold text-gray-800 capitalize">{{ $event->event_type }}</span>
                            </div>

                            <div class="flex justify-between items-center py-2 border-b border-gray-200">
                                <span class="text-gray-600">Available Spots</span>
                                <span class="font-semibold text-gray-800">
                                    @if($event->max_attendees)
                                    {{ ($event->max_attendees - ($event->registered_attendees ?? 0)) }} of {{ $event->max_attendees }} left
                                    @else
                                    Unlimited
                                    @endif
                                </span>
                            </div>

                            <div class="flex justify-between items-center py-2 border-b border-gray-200">
                                <span class="text-gray-600">Views</span>
                                <span class="font-semibold text-gray-800">{{ $event->view_count ?? 0 }}</span>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="space-y-3">
                          @if($registrationOpen)
<a href="{{ route('event-applications.create', $event) }}"
   class="bg-[#B91C1C] text-white px-6 py-3 rounded-lg font-semibold hover:bg-[#991B1B] transition-colors inline-block">
    Register Now
</a>
@endif

                            <button class="w-full border border-[#B91C1C] text-[#B91C1C] py-3 rounded-lg font-semibold hover:bg-red-50 transition-colors">
                                Add to Calendar
                            </button>

                            <div class="flex space-x-2">
                                <!-- Social sharing buttons -->
                                <button class="flex-1 border border-gray-300 text-gray-600 py-2 rounded-lg hover:bg-gray-50 transition-colors text-sm">
                                    Share
                                </button>
                                <button class="flex-1 border border-gray-300 text-gray-600 py-2 rounded-lg hover:bg-gray-50 transition-colors text-sm">
                                    Save
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Organizer Information -->
                @if($event->organizer_name || $event->organizer_email)
                <div class="bg-white rounded-xl shadow-lg border border-gray-200 mt-8 p-6">
                    <h3 class="text-xl font-bold text-gray-800 mb-4">Event Organizer</h3>
                    <div class="space-y-3">
                        @if($event->organizer_name)
                        <div class="flex items-center">
                            <svg class="w-4 h-4 text-[#B91C1C] mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            <span class="text-gray-700">{{ $event->organizer_name }}</span>
                        </div>
                        @endif

                        @if($event->organizer_email)
                        <div class="flex items-center">
                            <svg class="w-4 h-4 text-[#B91C1C] mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                            <a href="mailto:{{ $event->organizer_email }}" class="text-gray-700 hover:text-[#B91C1C] transition-colors">
                                {{ $event->organizer_email }}
                            </a>
                        </div>
                        @endif

                        @if($event->organizer_phone)
                        <div class="flex items-center">
                            <svg class="w-4 h-4 text-[#B91C1C] mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                            </svg>
                            <a href="tel:{{ $event->organizer_phone }}" class="text-gray-700 hover:text-[#B91C1C] transition-colors">
                                {{ $event->organizer_phone }}
                            </a>
                        </div>
                        @endif

                        @if($event->organizer_website)
                        <div class="flex items-center">
                            <svg class="w-4 h-4 text-[#B91C1C] mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9v-9m0-9v9"/>
                            </svg>
                            <a href="{{ $event->organizer_website }}" target="_blank" class="text-gray-700 hover:text-[#B91C1C] transition-colors">
                                Visit Website
                            </a>
                        </div>
                        @endif
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</section>

<!-- Related Events Section -->
@if($relatedEvents->count() > 0)
<section class="py-16 bg-gray-50">
    <div class="container mx-auto px-4">
        <h2 class="text-3xl font-bold text-gray-800 mb-8 text-center">Related Events</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            @foreach($relatedEvents as $relatedEvent)
            <div class="bg-white rounded-xl shadow-lg overflow-hidden hover:shadow-xl transition-all duration-300 border border-gray-200">
                @if($relatedEvent->cover_image)
                <img src="{{ Storage::url($relatedEvent->cover_image) }}" alt="{{ $relatedEvent->title }}" class="w-full h-48 object-cover">
                @else
                <div class="w-full h-48 bg-gradient-to-br from-[#B91C1C] to-[#BF1F30] flex items-center justify-center">
                    <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                @endif

                <div class="p-6">
                    <span class="inline-flex items-center px-2 py-1 bg-red-100 text-[#B91C1C] text-xs font-semibold rounded-full mb-3">
                        {{ ucfirst($relatedEvent->event_type) }}
                    </span>
                    <h3 class="text-lg font-bold text-gray-800 mb-2">{{ $relatedEvent->title }}</h3>
                    <p class="text-sm text-gray-600 mb-3">
                        {{ \Carbon\Carbon::parse($relatedEvent->event_start_date)->format('M j, Y') }}
                    </p>
                    <a href="{{ route('events.show', $relatedEvent->slug) }}" class="text-[#B91C1C] font-semibold text-sm hover:underline">
                        View Details
                    </a>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>
@endif

<!-- Back to Events -->
<section class="py-8 bg-white border-t border-gray-200">
    <div class="container mx-auto px-4">
        <a href="{{ route('event.index') }}" class="inline-flex items-center text-[#B91C1C] font-semibold hover:underline">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Back to All Events
        </a>
    </div>
</section>

<style>
.prose {
    max-width: none;
    line-height: 1.75;
}
.prose p {
    margin-bottom: 1rem;
}
.prose h2 {
    font-size: 1.5rem;
    font-weight: bold;
    margin-top: 2rem;
    margin-bottom: 1rem;
    color: #1f2937;
}
.prose h3 {
    font-size: 1.25rem;
    font-weight: bold;
    margin-top: 1.5rem;
    margin-bottom: 0.75rem;
    color: #374151;
}
.prose ul {
    list-style-type: disc;
    margin-left: 1.5rem;
    margin-bottom: 1rem;
}
.prose ol {
    list-style-type: decimal;
    margin-left: 1.5rem;
    margin-bottom: 1rem;
}
.prose li {
    margin-bottom: 0.5rem;
}
</style>
@endsection
