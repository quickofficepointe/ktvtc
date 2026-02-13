@extends('ktvtc.library.layout.librarylayout')

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

    {{-- Header Section --}}
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 font-serif">Notifications</h1>
            <p class="text-gray-600 mt-2">Manage library notifications and member communications</p>
        </div>
        <div class="flex space-x-3">
            <button onclick="markAllAsRead()"
                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-3 rounded-lg font-medium transition-colors flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                Mark All Read
            </button>
            <button onclick="openCreateModal()"
                class="bg-amber-600 hover:bg-amber-700 text-white px-6 py-3 rounded-lg font-medium transition-all duration-200 transform hover:scale-105 shadow-lg hover:shadow-xl flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                Send Notification
            </button>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-xl shadow-sm border border-amber-200 p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-lg bg-amber-100 text-amber-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total Notifications</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $notifications->total() }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-red-200 p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-lg bg-red-100 text-red-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Unread</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $unreadCount }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-blue-200 p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-lg bg-blue-100 text-blue-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Today</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $todayCount }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-green-200 p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-lg bg-green-100 text-green-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 01118 0z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Overdue Alerts</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $overdueCount }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Quick Filters --}}
    <div class="bg-amber-50 rounded-xl shadow-sm border border-amber-200 p-6 mb-6">
        <div class="flex flex-wrap gap-4">
            <button onclick="filterByStatus('unread')"
                class="bg-white hover:bg-red-100 text-red-700 px-4 py-2 rounded-lg border border-red-300 transition-colors flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                </svg>
                Unread
            </button>
            <button onclick="filterByType('overdue')"
                class="bg-white hover:bg-orange-100 text-orange-700 px-4 py-2 rounded-lg border border-orange-300 transition-colors flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 01118 0z"/>
                </svg>
                Overdue
            </button>
            <button onclick="filterByType('reservation')"
                class="bg-white hover:bg-purple-100 text-purple-700 px-4 py-2 rounded-lg border border-purple-300 transition-colors flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                </svg>
                Reservations
            </button>
            <button onclick="filterByType('membership')"
                class="bg-white hover:bg-blue-100 text-blue-700 px-4 py-2 rounded-lg border border-blue-300 transition-colors flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                </svg>
                Membership
            </button>
            <button onclick="clearFilters()"
                class="bg-amber-600 hover:bg-amber-700 text-white px-4 py-2 rounded-lg transition-colors">
                Clear Filters
            </button>
        </div>
    </div>

    {{-- Notifications List --}}
    <div class="bg-white rounded-xl shadow-sm border border-amber-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-amber-200 bg-amber-50">
            <h2 class="text-lg font-semibold text-gray-900 font-serif">Notification Center</h2>
        </div>

        <div class="divide-y divide-amber-100">
            @forelse($notifications as $notification)
                <div class="px-6 py-4 hover:bg-amber-50 transition-colors duration-150 {{ !$notification->is_read ? 'bg-blue-50' : '' }}">
                    <div class="flex items-start justify-between">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center space-x-3">
                                @php
                                    $iconColors = [
                                        'overdue' => 'text-red-500 bg-red-100',
                                        'reservation' => 'text-purple-500 bg-purple-100',
                                        'membership' => 'text-blue-500 bg-blue-100',
                                        'fine' => 'text-orange-500 bg-orange-100',
                                        'general' => 'text-amber-500 bg-amber-100'
                                    ];
                                    $icon = $iconColors[$notification->type] ?? 'text-gray-500 bg-gray-100';
                                @endphp
                                <div class="flex-shrink-0">
                                    <div class="w-10 h-10 rounded-full flex items-center justify-center {{ $icon }}">
                                        @if($notification->type === 'overdue')
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 01118 0z"/>
                                            </svg>
                                        @elseif($notification->type === 'reservation')
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                                            </svg>
                                        @elseif($notification->type === 'membership')
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                                            </svg>
                                        @elseif($notification->type === 'fine')
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                                            </svg>
                                        @else
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                        @endif
                                    </div>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h3 class="text-lg font-semibold text-gray-900 font-serif">
                                        {{ $notification->title }}
                                        @if(!$notification->is_read)
                                            <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                New
                                            </span>
                                        @endif
                                    </h3>
                                    <p class="text-sm text-gray-600 mt-1">{{ $notification->message }}</p>
                                    <div class="mt-2 flex items-center space-x-4 text-xs text-gray-500">
                                        <span class="flex items-center">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                            </svg>
                                            {{ $notification->member->first_name }} {{ $notification->member->last_name }}
                                        </span>
                                        <span class="flex items-center">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            {{ $notification->created_at->diffForHumans() }}
                                        </span>
                                        @if($notification->sent_at)
                                            <span class="flex items-center">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                                                </svg>
                                                Sent: {{ $notification->sent_at->format('M d, h:i A') }}
                                            </span>
                                        @endif
                                        @if($notification->read_at)
                                            <span class="flex items-center">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                </svg>
                                                Read: {{ $notification->read_at->format('M d, h:i A') }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center space-x-2 ml-4">
                            @if(!$notification->is_read)
                                <button onclick="markAsRead({{ $notification->id }})"
                                    class="inline-flex items-center px-3 py-1 border border-green-300 text-sm leading-4 font-medium rounded-md text-green-700 bg-white hover:bg-green-50 transition-colors">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    Mark Read
                                </button>
                            @endif
                            <button onclick="viewNotification({{ $notification->id }})"
                                class="inline-flex items-center px-3 py-1 border border-blue-300 text-sm leading-4 font-medium rounded-md text-blue-700 bg-white hover:bg-blue-50 transition-colors">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                View
                            </button>
                        </div>
                    </div>
                </div>
            @empty
                <div class="px-6 py-12 text-center">
                    <div class="flex flex-col items-center justify-center text-gray-500">
                        <svg class="w-16 h-16 mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">No notifications found</h3>
                        <p class="text-gray-600 mb-4">All caught up! No notifications to display.</p>
                    </div>
                </div>
            @endforelse
        </div>

        {{-- Pagination --}}
        @if($notifications->hasPages())
            <div class="px-6 py-4 border-t border-amber-200 bg-amber-50">
                {{ $notifications->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Create Notification Modal -->
<div id="createModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 py-6">
        <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity" onclick="closeCreateModal()"></div>

        <div class="relative bg-white rounded-2xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-hidden transform transition-all">
            <!-- Modal Header -->
            <div class="bg-gradient-to-r from-amber-600 to-amber-700 px-6 py-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="p-2 bg-white bg-opacity-20 rounded-lg">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-white font-serif">Send Notification</h3>
                            <p class="text-amber-100 text-sm">Send a notification to library members</p>
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
                <form action="{{ route('notifications.store') }}" method="POST" class="space-y-6">
                    @csrf

                    <div class="grid grid-cols-1 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Notification Type *</label>
                            <select name="type" required
                                    class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-amber-500 focus:border-transparent transition-colors">
                                <option value="general" {{ old('type', 'general') == 'general' ? 'selected' : '' }}>General Announcement</option>
                                <option value="overdue" {{ old('type') == 'overdue' ? 'selected' : '' }}>Overdue Book Alert</option>
                                <option value="reservation" {{ old('type') == 'reservation' ? 'selected' : '' }}>Reservation Update</option>
                                <option value="membership" {{ old('type') == 'membership' ? 'selected' : '' }}>Membership Renewal</option>
                                <option value="fine" {{ old('type') == 'fine' ? 'selected' : '' }}>Fine Notice</option>
                                <option value="event" {{ old('type') == 'event' ? 'selected' : '' }}>Library Event</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Title *</label>
                            <input type="text" name="title" value="{{ old('title') }}" required
                                   class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-amber-500 focus:border-transparent transition-colors"
                                   placeholder="Notification title">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Message *</label>
                            <textarea name="message" rows="4" required
                                      class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-amber-500 focus:border-transparent transition-colors"
                                      placeholder="Enter notification message here...">{{ old('message') }}</textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Recipient *</label>
                            <select name="member_id" required
                                    class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-amber-500 focus:border-transparent transition-colors">
                                <option value="">Select Member</option>
                                <option value="all" {{ old('member_id') == 'all' ? 'selected' : '' }}>All Members</option>
                                @foreach($members as $member)
                                    <option value="{{ $member->id }}" {{ old('member_id') == $member->id ? 'selected' : '' }}>
                                        {{ $member->first_name }} {{ $member->last_name }} ({{ $member->member_id }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Additional Data (JSON)</label>
                            <textarea name="data" rows="3"
                                      class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-amber-500 focus:border-transparent transition-colors font-mono text-sm"
                                      placeholder='{"key": "value"}'>{{ old('data') }}</textarea>
                            <p class="text-xs text-gray-500 mt-1">Optional JSON data for the notification</p>
                        </div>

                        <div class="bg-amber-50 rounded-xl p-6">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h4 class="text-lg font-semibold text-gray-900 mb-1">Send Options</h4>
                                    <p class="text-sm text-gray-600">Configure notification delivery</p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="send_immediately" value="1" checked class="sr-only peer">
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-amber-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-amber-600"></div>
                                    <span class="ml-3 text-sm font-medium text-gray-900">Send Immediately</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Modal Footer -->
                    <div class="flex justify-end space-x-3 pt-6 border-t border-gray-200">
                        <button type="button" onclick="closeCreateModal()"
                                class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 font-medium hover:bg-gray-50 transition-colors">
                            Cancel
                        </button>
                        <button type="submit"
                                class="px-6 py-3 bg-amber-600 text-white rounded-lg font-medium hover:bg-amber-700 transition-colors shadow-lg hover:shadow-xl">
                            Send Notification
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    // Modal Functions
    function openCreateModal() {
        document.getElementById('createModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeCreateModal() {
        document.getElementById('createModal').classList.add('hidden');
        document.body.style.overflow = 'auto';
    }

    function markAsRead(notificationId) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/notifications/${notificationId}/mark-read`;

        const csrf = document.createElement('input');
        csrf.type = 'hidden';
        csrf.name = '_token';
        csrf.value = '{{ csrf_token() }}';

        form.appendChild(csrf);
        document.body.appendChild(form);
        form.submit();
    }

    function markAllAsRead() {
        if(confirm('Mark all notifications as read?')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route("notifications.mark-all-read") }}';

            const csrf = document.createElement('input');
            csrf.type = 'hidden';
            csrf.name = '_token';
            csrf.value = '{{ csrf_token() }}';

            form.appendChild(csrf);
            document.body.appendChild(form);
            form.submit();
        }
    }

    function filterByStatus(status) {
        window.location.href = '{{ route('notifications.index') }}?status=' + status;
    }

    function filterByType(type) {
        window.location.href = '{{ route('notifications.index') }}?type=' + type;
    }

    function clearFilters() {
        window.location.href = '{{ route('notifications.index') }}';
    }

    function viewNotification(notificationId) {
        window.location.href = `/notifications/${notificationId}`;
    }

    // Close modals on Escape key
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closeCreateModal();
        }
    });
</script>
@endsection
