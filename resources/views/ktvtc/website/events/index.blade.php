@extends('ktvtc.website.layout.websitelayout')

@section('content')
<div class="container mx-auto px-4 py-8">

    {{-- Success Message --}}
    @if(session('success'))
        <div class="mb-6 p-4 rounded-lg bg-green-50 border border-green-200">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <span class="text-green-800 font-medium">{{ session('success') }}</span>
            </div>
        </div>
    @endif

    {{-- Validation Errors --}}
    @if($errors->any())
        <div class="mb-6 p-4 rounded-lg bg-red-50 border border-red-200">
            <div class="flex items-center mb-2">
                <svg class="w-5 h-5 text-red-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
                <span class="text-red-800 font-medium">Please fix the following errors:</span>
            </div>
            <ul class="list-disc list-inside text-red-700 text-sm">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Header Section --}}
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Events</h1>
            <p class="text-gray-600 mt-2">Manage all upcoming and past events</p>
        </div>
        <button onclick="openCreateModal()"
            class="bg-primary hover:bg-red-700 text-white px-6 py-3 rounded-lg font-medium transition-all duration-200 transform hover:scale-105 shadow-lg hover:shadow-xl flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
            </svg>
            Add New Event
        </button>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-lg bg-blue-100 text-blue-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total Events</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $events->count() }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-lg bg-green-100 text-green-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Active Events</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $events->where('is_active', true)->count() }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-lg bg-purple-100 text-purple-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Locations</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $events->pluck('location')->unique()->count() }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-lg bg-orange-100 text-orange-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Featured</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $events->where('is_featured', true)->count() }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Events Table --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">All Events</h2>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Event Details</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Date & Time</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Department</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Payment</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Capacity</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($events as $event)
                        <tr class="hover:bg-gray-50 transition-colors duration-150">
                            <td class="px-6 py-4">
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center space-x-2">
                                        <h3 class="text-lg font-semibold text-gray-900">{{ $event->title }}</h3>
                                        @if($event->is_featured)
                                            <span class="px-2 py-1 bg-yellow-100 text-yellow-800 text-xs font-medium rounded-full">Featured</span>
                                        @endif
                                    </div>
                                    <p class="text-sm text-gray-500 mt-1">{{ $event->short_description ?: Str::limit($event->description, 50) }}</p>
                                    <div class="flex items-center mt-2 space-x-2">
                                        <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                            {{ ucfirst($event->event_type) }}
                                        </span>
                                        @if($event->target_audience)
                                            <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-gray-100 text-gray-800">
                                                {{ str_replace('_', ' ', ucfirst($event->target_audience)) }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="space-y-1">
                                    <div class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                        {{ $event->event_start_date->format('M d, Y') }}
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        {{ $event->event_start_date->format('h:i A') }} - {{ $event->event_end_date->format('h:i A') }}
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                @if($event->department)
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                        {{ $event->department }}
                                    </span>
                                @else
                                    <span class="text-gray-400 text-sm">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @if($event->is_paid)
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        KSH {{ number_format($event->price, 2) }}
                                        @if($event->early_bird_price)
                                            <span class="ml-1 text-xs line-through text-green-600">KSH {{ number_format($event->early_bird_price, 2) }}</span>
                                        @endif
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                        Free
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-900">
                                    {{ $event->registered_attendees }} / {{ $event->max_attendees ?: '∞' }}
                                </div>
                                @if($event->max_attendees)
                                    <div class="w-full bg-gray-200 rounded-full h-2 mt-1">
                                        <div class="bg-primary h-2 rounded-full" style="width: {{ min(100, ($event->registered_attendees / $event->max_attendees) * 100) }}%"></div>
                                    </div>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <div class="space-y-1">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium {{ $event->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                        <span class="w-2 h-2 rounded-full mr-2 {{ $event->is_active ? 'bg-green-400' : 'bg-gray-400' }}"></span>
                                        {{ $event->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium {{ $event->is_published ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800' }}">
                                        {{ $event->is_published ? 'Published' : 'Draft' }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center space-x-2">
                                    <!-- FIXED: Pass event data directly without AJAX -->
                                    <button onclick="openEditModal(@json($event))"
                                        class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition-colors">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                        Edit
                                    </button>
                                    <!-- FIXED: Use proper route -->
                                    <button onclick="confirmDelete('/website/events/{{ $event->id }}')"
                                        class="inline-flex items-center px-3 py-2 border border-transparent shadow-sm text-sm leading-4 font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                        Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center justify-center text-gray-500">
                                    <svg class="w-16 h-16 mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    <h3 class="text-lg font-medium text-gray-900 mb-2">No events found</h3>
                                    <p class="text-gray-600 mb-4">Get started by creating your first event.</p>
                                    <button onclick="openCreateModal()"
                                        class="bg-primary hover:bg-red-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                                        Create Event
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Create Event Modal -->
<div id="createModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 py-6">
        <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity" onclick="closeCreateModal()"></div>

        <div class="relative bg-white rounded-2xl shadow-2xl max-w-4xl w-full max-h-[90vh] overflow-hidden transform transition-all">
            <!-- Modal Header -->
            <div class="bg-gradient-to-r from-primary to-red-600 px-6 py-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="p-2 bg-white bg-opacity-20 rounded-lg">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-white">Create New Event</h3>
                            <p class="text-red-100 text-sm">Add a new event to your website</p>
                        </div>
                    </div>
                    <button onclick="closeCreateModal()"
                            class="p-2 hover:bg-white hover:bg-opacity-20 rounded-lg transition-colors">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Modal Body -->
            <div class="p-6 overflow-y-auto max-h-[calc(90vh-80px)]">
                <form action="{{ route('events.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Basic Information -->
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Event Title *</label>
                            <input type="text" name="title" value="{{ old('title') }}" required
                                   class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors"
                                   placeholder="e.g., Web Development Bootcamp">
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Short Description</label>
                            <textarea name="short_description" rows="2"
                                      class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors"
                                      placeholder="Brief description for event cards...">{{ old('short_description') }}</textarea>
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Full Description</label>
                            <textarea name="description" rows="4"
                                      class="summernote w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors"
                                      placeholder="Detailed event description...">{{ old('description') }}</textarea>
                        </div>

                        <!-- Event Type & Department -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Event Type *</label>
                            <select name="event_type" required class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                                <option value="">Select Type</option>
                                <option value="bootcamp">Bootcamp</option>
                                <option value="workshop">Workshop</option>
                                <option value="trip">Field Trip</option>
                                <option value="mentorship">Mentorship</option>
                                <option value="seminar">Seminar</option>
                                <option value="social">Social Event</option>
                                <option value="conference">Conference</option>
                                <option value="other">Other</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Department</label>
                            <select name="department" class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                                <option value="">All Departments</option>
                                @foreach(\App\Models\Department::where('is_active', true)->orderBy('name')->get() as $department)
                                    <option value="{{ $department->name }}" {{ old('department') == $department->name ? 'selected' : '' }}>
                                        {{ $department->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Date & Time Section -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Event Start *</label>
                            <input type="datetime-local" name="event_start_date" value="{{ old('event_start_date') }}" required
                                   class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Event End *</label>
                            <input type="datetime-local" name="event_end_date" value="{{ old('event_end_date') }}" required
                                   class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Registration Start</label>
                            <input type="datetime-local" name="registration_start_date" value="{{ old('registration_start_date') }}"
                                   class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Registration End</label>
                            <input type="datetime-local" name="registration_end_date" value="{{ old('registration_end_date') }}"
                                   class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                        </div>

                        <!-- Location & Audience -->
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Location *</label>
                            <input type="text" name="location" value="{{ old('location') }}" required
                                   class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors"
                                   placeholder="e.g., Main Campus, Room 101">
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Target Audience</label>
                            <select name="target_audience" class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                                <option value="all_students">All Students</option>
                                <option value="freshmen">Freshmen</option>
                                <option value="sophomores">Sophomores</option>
                                <option value="juniors">Juniors</option>
                                <option value="seniors">Seniors</option>
                                <option value="graduate">Graduate Students</option>
                                <option value="faculty">Faculty Only</option>
                                <option value="staff">Staff Only</option>
                            </select>
                        </div>

                        <!-- Payment & Pricing -->
                        <div class="md:col-span-2 bg-gray-50 rounded-xl p-6">
                            <div class="flex items-center justify-between mb-4">
                                <div>
                                    <h4 class="text-lg font-semibold text-gray-900 mb-1">Payment Information</h4>
                                    <p class="text-sm text-gray-600">Configure event pricing</p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="is_paid" value="1" class="sr-only peer">
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                                    <span class="ml-3 text-sm font-medium text-gray-900">Paid Event</span>
                                </label>
                            </div>

                            <div id="priceFields" class="space-y-4 hidden">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Regular Price (KSH)</label>
                                        <input type="number" name="price" value="{{ old('price') }}" min="0" step="0.01"
                                               class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors"
                                               placeholder="0.00">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Early Bird Price (KSH)</label>
                                        <input type="number" name="early_bird_price" value="{{ old('early_bird_price') }}" min="0" step="0.01"
                                               class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors"
                                               placeholder="0.00">
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Early Bird End Date</label>
                                    <input type="datetime-local" name="early_bird_end_date" value="{{ old('early_bird_end_date') }}"
                                           class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                                </div>
                            </div>
                        </div>

                        <!-- Capacity -->
                        <div class="md:col-span-2 bg-gray-50 rounded-xl p-6">
                            <h4 class="text-lg font-semibold text-gray-900 mb-4">Capacity Settings</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Maximum Attendees</label>
                                    <input type="number" name="max_attendees" value="{{ old('max_attendees') }}" min="1"
                                           class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors"
                                           placeholder="Leave empty for unlimited">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Currently Registered</label>
                                    <input type="number" name="registered_attendees" value="{{ old('registered_attendees', 0) }}" min="0"
                                           class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                                </div>
                            </div>
                        </div>

                        <!-- Organizer Information -->
                        <div class="md:col-span-2">
                            <h4 class="text-lg font-semibold text-gray-900 mb-4">Organizer Information</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Organizer Name</label>
                                    <input type="text" name="organizer_name" value="{{ old('organizer_name') }}"
                                           class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors"
                                           placeholder="Faculty or student organizer">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Organizer Email</label>
                                    <input type="email" name="organizer_email" value="{{ old('organizer_email') }}"
                                           class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors"
                                           placeholder="organizer@college.edu">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Organizer Phone</label>
                                    <input type="text" name="organizer_phone" value="{{ old('organizer_phone') }}"
                                           class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors"
                                           placeholder="+254...">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Organizer Website</label>
                                    <input type="url" name="organizer_website" value="{{ old('organizer_website') }}"
                                           class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors"
                                           placeholder="https://...">
                                </div>
                            </div>
                        </div>

                        <!-- Media & Status -->
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Cover Image</label>
                            <input type="file" name="cover_image" accept="image/*"
                                   class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Banner Image</label>
                            <input type="file" name="banner_image" accept="image/*"
                                   class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                        </div>

                        <!-- Status Toggles -->
                        <div class="md:col-span-2 bg-gray-50 rounded-xl p-6">
                            <div class="space-y-4">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <h4 class="text-lg font-semibold text-gray-900 mb-1">Publish Event</h4>
                                        <p class="text-sm text-gray-600">Make this event visible to students</p>
                                    </div>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" name="is_published" value="1" class="sr-only peer">
                                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                                    </label>
                                </div>

                                <div class="flex items-center justify-between">
                                    <div>
                                        <h4 class="text-lg font-semibold text-gray-900 mb-1">Feature Event</h4>
                                        <p class="text-sm text-gray-600">Highlight this event on the homepage</p>
                                    </div>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" name="is_featured" value="1" class="sr-only peer">
                                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                                    </label>
                                </div>

                                <div class="flex items-center justify-between">
                                    <div>
                                        <h4 class="text-lg font-semibold text-gray-900 mb-1">Active Status</h4>
                                        <p class="text-sm text-gray-600">Enable or disable this event</p>
                                    </div>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" name="is_active" value="1" checked class="sr-only peer">
                                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Sort Order -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Sort Order</label>
                            <input type="number" name="sort_order" value="{{ old('sort_order', 0) }}"
                                   class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                        </div>
                    </div>

                    <!-- Modal Footer -->
                    <div class="flex justify-end space-x-3 pt-6 border-t border-gray-200">
                        <button type="button" onclick="closeCreateModal()"
                                class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 font-medium hover:bg-gray-50 transition-colors">
                            Cancel
                        </button>
                        <button type="submit"
                                class="px-6 py-3 bg-primary text-white rounded-lg font-medium hover:bg-red-700 transition-colors shadow-lg hover:shadow-xl transform hover:scale-105">
                            Create Event
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Edit Event Modal -->
<div id="editModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 py-6">
        <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity" onclick="closeEditModal()"></div>

        <div class="relative bg-white rounded-2xl shadow-2xl max-w-4xl w-full max-h-[90vh] overflow-hidden transform transition-all">
            <!-- Modal Header -->
            <div class="bg-gradient-to-r from-primary to-red-600 px-6 py-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="p-2 bg-white bg-opacity-20 rounded-lg">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-white">Edit Event</h3>
                            <p class="text-red-100 text-sm">Update event information</p>
                        </div>
                    </div>
                    <button onclick="closeEditModal()"
                            class="p-2 hover:bg-white hover:bg-opacity-20 rounded-lg transition-colors">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Modal Body -->
            <div class="p-6 overflow-y-auto max-h-[calc(90vh-80px)]">
                <form id="editForm" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Basic Information -->
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Event Title *</label>
                            <input type="text" id="editTitle" name="title"
                                   class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors"
                                   required>
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Short Description</label>
                            <textarea id="editShortDescription" name="short_description" rows="2"
                                      class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors"
                                      placeholder="Brief description for event cards..."></textarea>
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Full Description</label>
                            <textarea id="editDescription" name="description" rows="4"
                                   class="summernote w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors"
                                      placeholder="Detailed event description..."></textarea>
                        </div>

                        <!-- Event Type & Department -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Event Type *</label>
                            <select id="editEventType" name="event_type" required class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                                <option value="">Select Type</option>
                                <option value="bootcamp">Bootcamp</option>
                                <option value="workshop">Workshop</option>
                                <option value="trip">Field Trip</option>
                                <option value="mentorship">Mentorship</option>
                                <option value="seminar">Seminar</option>
                                <option value="social">Social Event</option>
                                <option value="conference">Conference</option>
                                <option value="other">Other</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Department</label>
                            <select id="editDepartment" name="department" class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                                <option value="">All Departments</option>
                                @foreach(\App\Models\Department::where('is_active', true)->orderBy('name')->get() as $department)
                                    <option value="{{ $department->name }}">
                                        {{ $department->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Date & Time Section -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Event Start *</label>
                            <input type="datetime-local" id="editEventStartDate" name="event_start_date"
                                   class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors"
                                   required>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Event End *</label>
                            <input type="datetime-local" id="editEventEndDate" name="event_end_date"
                                   class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors"
                                   required>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Registration Start</label>
                            <input type="datetime-local" id="editRegistrationStartDate" name="registration_start_date"
                                   class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Registration End</label>
                            <input type="datetime-local" id="editRegistrationEndDate" name="registration_end_date"
                                   class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                        </div>

                        <!-- Location & Audience -->
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Location *</label>
                            <input type="text" id="editLocation" name="location"
                                   class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors"
                                   required>
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Target Audience</label>
                            <select id="editTargetAudience" name="target_audience" class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                                <option value="all_students">All Students</option>
                                <option value="freshmen">Freshmen</option>
                                <option value="sophomores">Sophomores</option>
                                <option value="juniors">Juniors</option>
                                <option value="seniors">Seniors</option>
                                <option value="graduate">Graduate Students</option>
                                <option value="faculty">Faculty Only</option>
                                <option value="staff">Staff Only</option>
                            </select>
                        </div>

                        <!-- Payment & Pricing -->
                        <div class="md:col-span-2 bg-gray-50 rounded-xl p-6">
                            <div class="flex items-center justify-between mb-4">
                                <div>
                                    <h4 class="text-lg font-semibold text-gray-900 mb-1">Payment Information</h4>
                                    <p class="text-sm text-gray-600">Configure event pricing</p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" id="editIsPaid" name="is_paid" value="1" class="sr-only peer">
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                                    <span class="ml-3 text-sm font-medium text-gray-900">Paid Event</span>
                                </label>
                            </div>

                            <div id="editPriceFields" class="space-y-4 hidden">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Regular Price (KSH)</label>
                                        <input type="number" id="editPrice" name="price" min="0" step="0.01"
                                               class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors"
                                               placeholder="0.00">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Early Bird Price (KSH)</label>
                                        <input type="number" id="editEarlyBirdPrice" name="early_bird_price" min="0" step="0.01"
                                               class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors"
                                               placeholder="0.00">
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Early Bird End Date</label>
                                    <input type="datetime-local" id="editEarlyBirdEndDate" name="early_bird_end_date"
                                           class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                                </div>
                            </div>
                        </div>

                        <!-- Capacity -->
                        <div class="md:col-span-2 bg-gray-50 rounded-xl p-6">
                            <h4 class="text-lg font-semibold text-gray-900 mb-4">Capacity Settings</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Maximum Attendees</label>
                                    <input type="number" id="editMaxAttendees" name="max_attendees" min="1"
                                           class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors"
                                           placeholder="Leave empty for unlimited">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Currently Registered</label>
                                    <input type="number" id="editRegisteredAttendees" name="registered_attendees" min="0"
                                           class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                                </div>
                            </div>
                        </div>

                        <!-- Organizer Information -->
                        <div class="md:col-span-2">
                            <h4 class="text-lg font-semibold text-gray-900 mb-4">Organizer Information</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Organizer Name</label>
                                    <input type="text" id="editOrganizerName" name="organizer_name"
                                           class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors"
                                           placeholder="Faculty or student organizer">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Organizer Email</label>
                                    <input type="email" id="editOrganizerEmail" name="organizer_email"
                                           class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors"
                                           placeholder="organizer@college.edu">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Organizer Phone</label>
                                    <input type="text" id="editOrganizerPhone" name="organizer_phone"
                                           class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors"
                                           placeholder="+254...">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Organizer Website</label>
                                    <input type="url" id="editOrganizerWebsite" name="organizer_website"
                                           class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors"
                                           placeholder="https://...">
                                </div>
                            </div>
                        </div>

                        <!-- Media & Status -->
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Cover Image</label>
                            <input type="file" name="cover_image" accept="image/*"
                                   class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                            <p class="text-xs text-gray-500 mt-1">Leave empty to keep current image</p>
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Banner Image</label>
                            <input type="file" name="banner_image" accept="image/*"
                                   class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                            <p class="text-xs text-gray-500 mt-1">Leave empty to keep current image</p>
                        </div>

                        <!-- Status Toggles -->
                        <div class="md:col-span-2 bg-gray-50 rounded-xl p-6">
                            <div class="space-y-4">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <h4 class="text-lg font-semibold text-gray-900 mb-1">Publish Event</h4>
                                        <p class="text-sm text-gray-600">Make this event visible to students</p>
                                    </div>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" id="editIsPublished" name="is_published" value="1" class="sr-only peer">
                                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                                    </label>
                                </div>

                                <div class="flex items-center justify-between">
                                    <div>
                                        <h4 class="text-lg font-semibold text-gray-900 mb-1">Feature Event</h4>
                                        <p class="text-sm text-gray-600">Highlight this event on the homepage</p>
                                    </div>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" id="editIsFeatured" name="is_featured" value="1" class="sr-only peer">
                                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                                    </label>
                                </div>

                                <div class="flex items-center justify-between">
                                    <div>
                                        <h4 class="text-lg font-semibold text-gray-900 mb-1">Active Status</h4>
                                        <p class="text-sm text-gray-600">Enable or disable this event</p>
                                    </div>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" id="editIsActive" name="is_active" value="1" class="sr-only peer">
                                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Sort Order -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Sort Order</label>
                            <input type="number" id="editSortOrder" name="sort_order"
                                   class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                        </div>
                    </div>

                    <!-- Modal Footer -->
                    <div class="flex justify-end space-x-3 pt-6 border-t border-gray-200">
                        <button type="button" onclick="closeEditModal()"
                                class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 font-medium hover:bg-gray-50 transition-colors">
                            Cancel
                        </button>
                        <button type="submit"
                                class="px-6 py-3 bg-primary text-white rounded-lg font-medium hover:bg-red-700 transition-colors shadow-lg hover:shadow-xl transform hover:scale-105">
                            Update Event
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    // Create Modal Functions
    function openCreateModal() {
        document.getElementById('createModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeCreateModal() {
        document.getElementById('createModal').classList.add('hidden');
        document.body.style.overflow = 'auto';
    }

    // Edit Modal Functions - FIXED: No AJAX, data passed directly
    function openEditModal(event) {
        const modal = document.getElementById('editModal');
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';

        // Set form action
        document.getElementById('editForm').action = `/website/events/${event.id}`;

        // Fill form fields
        document.getElementById('editTitle').value = event.title || '';
        document.getElementById('editShortDescription').value = event.short_description || '';
        document.getElementById('editDescription').value = event.description || '';
        document.getElementById('editEventType').value = event.event_type || '';
        document.getElementById('editDepartment').value = event.department || '';

        // Format dates for datetime-local input
        document.getElementById('editEventStartDate').value = formatDateForInput(event.event_start_date);
        document.getElementById('editEventEndDate').value = formatDateForInput(event.event_end_date);
        document.getElementById('editRegistrationStartDate').value = formatDateForInput(event.registration_start_date);
        document.getElementById('editRegistrationEndDate').value = formatDateForInput(event.registration_end_date);
        document.getElementById('editEarlyBirdEndDate').value = formatDateForInput(event.early_bird_end_date);

        document.getElementById('editLocation').value = event.location || '';
        document.getElementById('editTargetAudience').value = event.target_audience || 'all_students';
        document.getElementById('editIsPaid').checked = event.is_paid == 1 || event.is_paid === true;
        document.getElementById('editPrice').value = event.price || '';
        document.getElementById('editEarlyBirdPrice').value = event.early_bird_price || '';
        document.getElementById('editMaxAttendees').value = event.max_attendees || '';
        document.getElementById('editRegisteredAttendees').value = event.registered_attendees || 0;
        document.getElementById('editOrganizerName').value = event.organizer_name || '';
        document.getElementById('editOrganizerEmail').value = event.organizer_email || '';
        document.getElementById('editOrganizerPhone').value = event.organizer_phone || '';
        document.getElementById('editOrganizerWebsite').value = event.organizer_website || '';
        document.getElementById('editIsPublished').checked = event.is_published == 1 || event.is_published === true;
        document.getElementById('editIsFeatured').checked = event.is_featured == 1 || event.is_featured === true;
        document.getElementById('editIsActive').checked = event.is_active == 1 || event.is_active === true;
        document.getElementById('editSortOrder').value = event.sort_order || 0;

        // Toggle price fields based on paid status
        const isPaid = event.is_paid == 1 || event.is_paid === true;
        document.getElementById('editPriceFields').classList.toggle('hidden', !isPaid);

        // Initialize Summernote for description
        $('#editDescription').summernote({
            height: 200,
            toolbar: [
                ['style', ['style']],
                ['font', ['bold', 'underline', 'clear']],
                ['color', ['color']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['table', ['table']],
                ['insert', ['link']],
                ['view', ['fullscreen', 'codeview', 'help']]
            ]
        });
        $('#editDescription').summernote('code', event.description || '');
    }

    function closeEditModal() {
        document.getElementById('editModal').classList.add('hidden');
        document.body.style.overflow = 'auto';

        // Destroy Summernote instance when closing
        $('#editDescription').summernote('destroy');
    }

    // Helper function to format date for datetime-local input
    function formatDateForInput(dateString) {
        if (!dateString) return '';

        try {
            // Try to parse the date string
            let date;

            // Check if it's already an ISO string or needs conversion
            if (dateString.includes('T')) {
                // Already in ISO format
                date = new Date(dateString);
            } else if (dateString.includes(' ')) {
                // Format like "2024-01-01 10:00:00"
                date = new Date(dateString.replace(' ', 'T'));
            } else {
                // Try direct parsing
                date = new Date(dateString);
            }

            // Check if date is valid
            if (isNaN(date.getTime())) {
                console.error('Invalid date:', dateString);
                return '';
            }

            // Adjust for timezone offset to get local time
            const timezoneOffset = date.getTimezoneOffset() * 60000;
            const localDate = new Date(date.getTime() - timezoneOffset);

            // Format: YYYY-MM-DDTHH:MM
            return localDate.toISOString().slice(0, 16);
        } catch (error) {
            console.error('Error formatting date:', error, dateString);
            return '';
        }
    }

    // Delete Confirmation - FIXED: Proper function definition
    function confirmDelete(url) {
        if (confirm('Are you sure you want to delete this event? This action cannot be undone.')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = url;

            const csrf = document.createElement('input');
            csrf.type = 'hidden';
            csrf.name = '_token';
            csrf.value = '{{ csrf_token() }}';

            const method = document.createElement('input');
            method.type = 'hidden';
            method.name = '_method';
            method.value = 'DELETE';

            form.appendChild(csrf);
            form.appendChild(method);
            document.body.appendChild(form);
            form.submit();
        }
    }

    // Toggle price fields based on paid event checkbox
    document.addEventListener('DOMContentLoaded', function() {
        // Create modal
        const paidCheckbox = document.querySelector('input[name="is_paid"]');
        if (paidCheckbox) {
            paidCheckbox.addEventListener('change', function() {
                document.getElementById('priceFields').classList.toggle('hidden', !this.checked);
            });
            // Initial state
            document.getElementById('priceFields').classList.toggle('hidden', !paidCheckbox.checked);
        }

        // Edit modal
        const editPaidCheckbox = document.getElementById('editIsPaid');
        if (editPaidCheckbox) {
            editPaidCheckbox.addEventListener('change', function() {
                document.getElementById('editPriceFields').classList.toggle('hidden', !this.checked);
            });
            // Initial state
            const isPaidInitially = editPaidCheckbox.checked;
            document.getElementById('editPriceFields').classList.toggle('hidden', !isPaidInitially);
        }

        // Initialize Summernote for create modal
        if (typeof $ !== 'undefined' && $.fn.summernote) {
            $('.summernote').summernote({
                height: 200,
                toolbar: [
                    ['style', ['style']],
                    ['font', ['bold', 'underline', 'clear']],
                    ['color', ['color']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['table', ['table']],
                    ['insert', ['link', 'picture']],
                    ['view', ['fullscreen', 'codeview', 'help']]
                ]
            });
        } else {
            console.warn('Summernote not loaded. Make sure jQuery and Summernote are included.');
        }
    });

    // Close modals on Escape key
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closeCreateModal();
            closeEditModal();
        }
    });

    // Close modals when clicking on backdrop
    document.addEventListener('click', function(event) {
        const createModal = document.getElementById('createModal');
        const editModal = document.getElementById('editModal');

        if (event.target === createModal || event.target === editModal) {
            closeCreateModal();
            closeEditModal();
        }
    });
</script>

<style>
    .transform {
        transition: transform 0.2s ease-in-out;
    }

    /* Summernote fixes */
    .note-editor {
        border: 1px solid #e5e7eb !important;
        border-radius: 0.5rem !important;
    }
    .note-toolbar {
        background-color: #f9fafb !important;
        border-bottom: 1px solid #e5e7eb !important;
        border-radius: 0.5rem 0.5rem 0 0 !important;
    }
    .note-editable {
        min-height: 150px !important;
        background-color: white !important;
    }
</style>
@endsection
