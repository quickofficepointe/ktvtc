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
            <h1 class="text-3xl font-bold text-gray-900 font-serif">Book Reservations</h1>
            <p class="text-gray-600 mt-2">Manage book reservations and waiting lists</p>
        </div>
        <button onclick="openCreateModal()"
            class="bg-amber-600 hover:bg-amber-700 text-white px-6 py-3 rounded-lg font-medium transition-all duration-200 transform hover:scale-105 shadow-lg hover:shadow-xl flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
            </svg>
            New Reservation
        </button>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-xl shadow-sm border border-amber-200 p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-lg bg-amber-100 text-amber-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Active Reservations</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $activeReservations }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-blue-200 p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-lg bg-blue-100 text-blue-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Fulfilled Today</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $fulfilledToday }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-red-200 p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-lg bg-red-100 text-red-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Expiring Soon</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $expiringSoon }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-purple-200 p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-lg bg-purple-100 text-purple-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Unique Members</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $uniqueMembers }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Quick Status Filters --}}
    <div class="bg-amber-50 rounded-xl shadow-sm border border-amber-200 p-6 mb-6">
        <div class="flex flex-wrap gap-4">
            <button onclick="filterByStatus('active')"
                class="bg-white hover:bg-amber-100 text-amber-700 px-4 py-2 rounded-lg border border-amber-300 transition-colors flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                Active
            </button>
            <button onclick="filterByStatus('fulfilled')"
                class="bg-white hover:bg-green-100 text-green-700 px-4 py-2 rounded-lg border border-green-300 transition-colors flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Fulfilled
            </button>
            <button onclick="filterByStatus('cancelled')"
                class="bg-white hover:bg-red-100 text-red-700 px-4 py-2 rounded-lg border border-red-300 transition-colors flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
                Cancelled
            </button>
            <button onclick="filterByStatus('expired')"
                class="bg-white hover:bg-gray-100 text-gray-700 px-4 py-2 rounded-lg border border-gray-300 transition-colors flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Expired
            </button>
            <button onclick="clearFilters()"
                class="bg-amber-600 hover:bg-amber-700 text-white px-4 py-2 rounded-lg transition-colors">
                Clear Filters
            </button>
        </div>
    </div>

    {{-- Reservations Table --}}
    <div class="bg-white rounded-xl shadow-sm border border-amber-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-amber-200 bg-amber-50">
            <h2 class="text-lg font-semibold text-gray-900 font-serif">Reservation Queue</h2>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-amber-50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-amber-800 uppercase tracking-wider">Reservation Details</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-amber-800 uppercase tracking-wider">Queue Position</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-amber-800 uppercase tracking-wider">Dates</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-amber-800 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-amber-800 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-amber-100">
                    @forelse($reservations as $reservation)
                        <tr class="hover:bg-amber-50 transition-colors duration-150">
                            <td class="px-6 py-4">
                                <div class="flex items-start space-x-4">
                                    <div class="flex-shrink-0">
                                        @if($reservation->book->cover_image)
                                            <img src="{{ asset('storage/' . $reservation->book->cover_image) }}"
                                                 alt="{{ $reservation->book->title }}"
                                                 class="w-12 h-16 object-cover rounded-lg shadow-sm border border-amber-200">
                                        @else
                                            <div class="w-12 h-16 bg-amber-100 rounded-lg flex items-center justify-center border border-amber-200">
                                                <svg class="w-6 h-6 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                                </svg>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <h3 class="text-lg font-semibold text-gray-900 font-serif">
                                            {{ $reservation->book->title }}
                                        </h3>
                                        <p class="text-sm text-gray-600 mt-1">
                                            Reserved by: {{ $reservation->member->first_name }} {{ $reservation->member->last_name }}
                                        </p>
                                        <p class="text-xs text-gray-500 mt-1">
                                            {{ $reservation->member->member_id }} • {{ $reservation->branch->name }}
                                        </p>
                                        @if($reservation->notes)
                                            <p class="text-xs text-gray-400 mt-1 italic">"{{ Str::limit($reservation->notes, 50) }}"</p>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-center">
                                    <span class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-amber-100 text-amber-800 font-bold text-lg border-2 border-amber-300">
                                        {{ $reservation->queue_position }}
                                    </span>
                                    <p class="text-xs text-gray-500 mt-1">in queue</p>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="space-y-1 text-sm">
                                    <div class="flex items-center text-gray-600">
                                        <svg class="w-4 h-4 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                        Reserved: {{ $reservation->reservation_date->format('M d, Y') }}
                                    </div>
                                    <div class="flex items-center text-gray-600">
                                        <svg class="w-4 h-4 mr-2 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        Expires: {{ $reservation->expiry_date->format('M d, Y') }}
                                    </div>
                                    @if($reservation->expiry_date->isPast())
                                        <p class="text-xs text-red-500 font-medium">EXPIRED</p>
                                    @elseif($reservation->expiry_date->diffInDays(now()) <= 2)
                                        <p class="text-xs text-orange-500 font-medium">
                                            Expires in {{ $reservation->expiry_date->diffInDays(now()) }} days
                                        </p>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                @php
                                    $statusColors = [
                                        'active' => 'bg-blue-100 text-blue-800',
                                        'fulfilled' => 'bg-green-100 text-green-800',
                                        'cancelled' => 'bg-red-100 text-red-800',
                                        'expired' => 'bg-gray-100 text-gray-800'
                                    ];
                                @endphp
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium {{ $statusColors[$reservation->status] }}">
                                    <span class="w-2 h-2 rounded-full mr-2
                                        {{ $reservation->status === 'active' ? 'bg-blue-400' :
                                           ($reservation->status === 'fulfilled' ? 'bg-green-400' :
                                           ($reservation->status === 'cancelled' ? 'bg-red-400' : 'bg-gray-400')) }}">
                                    </span>
                                    {{ ucfirst($reservation->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center space-x-2">
                                    @if($reservation->status === 'active')
                                        <button onclick="fulfillReservation({{ $reservation->id }})"
                                            class="inline-flex items-center px-3 py-2 border border-green-300 shadow-sm text-sm leading-4 font-medium rounded-md text-green-700 bg-white hover:bg-green-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                            </svg>
                                            Fulfill
                                        </button>
                                        <button onclick="cancelReservation({{ $reservation->id }})"
                                            class="inline-flex items-center px-3 py-2 border border-red-300 shadow-sm text-sm leading-4 font-medium rounded-md text-red-700 bg-white hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                            Cancel
                                        </button>
                                    @endif
                                    <button onclick="viewReservation({{ $reservation->id }})"
                                        class="inline-flex items-center px-3 py-2 border border-blue-300 shadow-sm text-sm leading-4 font-medium rounded-md text-blue-700 bg-white hover:bg-blue-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                        View
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center justify-center text-gray-500">
                                    <svg class="w-16 h-16 mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                                    </svg>
                                    <h3 class="text-lg font-medium text-gray-900 mb-2">No reservations found</h3>
                                    <p class="text-gray-600 mb-4">Start by creating a new book reservation.</p>
                                    <button onclick="openCreateModal()"
                                        class="bg-amber-600 hover:bg-amber-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                                        Create Reservation
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($reservations->hasPages())
            <div class="px-6 py-4 border-t border-amber-200 bg-amber-50">
                {{ $reservations->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Create Reservation Modal -->
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
                            <h3 class="text-xl font-bold text-white font-serif">New Reservation</h3>
                            <p class="text-amber-100 text-sm">Reserve a book for a member</p>
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
                <form action="{{ route('reservations.store') }}" method="POST" class="space-y-6">
                    @csrf

                    <div class="grid grid-cols-1 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Member *</label>
                            <select name="member_id" required
                                    class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-amber-500 focus:border-transparent transition-colors">
                                <option value="">Select Member</option>
                                @foreach($members as $member)
                                    <option value="{{ $member->id }}" {{ old('member_id') == $member->id ? 'selected' : '' }}>
                                        {{ $member->first_name }} {{ $member->last_name }} ({{ $member->member_id }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Book *</label>
                            <select name="book_id" required
                                    class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-amber-500 focus:border-transparent transition-colors">
                                <option value="">Select Book</option>
                                @foreach($books as $book)
                                    <option value="{{ $book->id }}" {{ old('book_id') == $book->id ? 'selected' : '' }}>
                                        {{ $book->title }}
                                        @if($book->authors->count() > 0)
                                            - by {{ $book->authors->first()->full_name }}
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Branch *</label>
                            <select name="branch_id" required
                                    class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-amber-500 focus:border-transparent transition-colors">
                                <option value="">Select Branch</option>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>
                                        {{ $branch->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Reservation Date *</label>
                            <input type="date" name="reservation_date" value="{{ old('reservation_date', date('Y-m-d')) }}" required
                                   class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-amber-500 focus:border-transparent transition-colors">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Expiry Date *</label>
                            <input type="date" name="expiry_date" value="{{ old('expiry_date', date('Y-m-d', strtotime('+7 days'))) }}" required
                                   class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-amber-500 focus:border-transparent transition-colors">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
                            <textarea name="notes" rows="3"
                                      class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-amber-500 focus:border-transparent transition-colors"
                                      placeholder="Any special notes about this reservation">{{ old('notes') }}</textarea>
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
                            Create Reservation
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

    function filterByStatus(status) {
        window.location.href = '{{ route('reservations.index') }}?status=' + status;
    }

    function clearFilters() {
        window.location.href = '{{ route('reservations.index') }}';
    }

    function fulfillReservation(reservationId) {
        if(confirm('Mark this reservation as fulfilled?')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/reservations/${reservationId}/fulfill`;

            const csrf = document.createElement('input');
            csrf.type = 'hidden';
            csrf.name = '_token';
            csrf.value = '{{ csrf_token() }}';

            form.appendChild(csrf);
            document.body.appendChild(form);
            form.submit();
        }
    }

    function cancelReservation(reservationId) {
        if(confirm('Cancel this reservation?')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/reservations/${reservationId}/cancel`;

            const csrf = document.createElement('input');
            csrf.type = 'hidden';
            csrf.name = '_token';
            csrf.value = '{{ csrf_token() }}';

            form.appendChild(csrf);
            document.body.appendChild(form);
            form.submit();
        }
    }

    function viewReservation(reservationId) {
        window.location.href = `/reservations/${reservationId}`;
    }

    // Close modals on Escape key
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closeCreateModal();
        }
    });
</script>
@endsection
