@extends('ktvtc.library.layout.librarylayout')

@section('title', 'Library Dashboard')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Page Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-dark mb-2">Library Dashboard</h1>
            <p class="text-gray-600">Welcome to KTVTC Library Management System</p>
        </div>
        <div class="flex items-center space-x-4 mt-4 md:mt-0">
            <span class="text-sm text-gray-500">
                <i class="fas fa-calendar-alt mr-2"></i>
                {{ now()->format('F d, Y') }}
            </span>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Total Books -->
        <div class="bg-white rounded-xl shadow-md p-6 stat-card">
            <div class="flex items-center">
                <div class="p-3 rounded-lg bg-red-50 mr-4">
                    <i class="fas fa-book text-primary text-2xl"></i>
                </div>
                <div>
                    <p class="text-gray-500 text-sm mb-1">Total Books</p>
                    <h3 class="text-2xl font-bold text-dark">{{ number_format($stats['total_books']) }}</h3>
                </div>
            </div>
            <div class="mt-4 pt-4 border-t">
                <span class="text-sm text-gray-500">
                    <i class="fas fa-info-circle mr-1"></i>
                    Includes all cataloged items
                </span>
            </div>
        </div>

        <!-- Total Members -->
        <div class="bg-white rounded-xl shadow-md p-6 stat-card">
            <div class="flex items-center">
                <div class="p-3 rounded-lg bg-blue-50 mr-4">
                    <i class="fas fa-users text-dark text-2xl"></i>
                </div>
                <div>
                    <p class="text-gray-500 text-sm mb-1">Total Members</p>
                    <h3 class="text-2xl font-bold text-dark">{{ number_format($stats['total_members']) }}</h3>
                </div>
            </div>
            <div class="mt-4 pt-4 border-t">
                <span class="text-sm text-gray-500">
                    <i class="fas fa-user-check mr-1"></i>
                    Active library members
                </span>
            </div>
        </div>

        <!-- Active Borrows -->
        <div class="bg-white rounded-xl shadow-md p-6 stat-card">
            <div class="flex items-center">
                <div class="p-3 rounded-lg bg-green-50 mr-4">
                    <i class="fas fa-exchange-alt text-green-600 text-2xl"></i>
                </div>
                <div>
                    <p class="text-gray-500 text-sm mb-1">Active Borrows</p>
                    <h3 class="text-2xl font-bold text-dark">{{ number_format($stats['active_borrows']) }}</h3>
                </div>
            </div>
            <div class="mt-4 pt-4 border-t">
                <span class="text-sm text-gray-500">
                    <i class="fas fa-clock mr-1"></i>
                    {{ $stats['overdue_books'] }} overdue
                </span>
            </div>
        </div>

        <!-- Pending Requests -->
        <div class="bg-white rounded-xl shadow-md p-6 stat-card">
            <div class="flex items-center">
                <div class="p-3 rounded-lg bg-yellow-50 mr-4">
                    <i class="fas fa-tasks text-yellow-600 text-2xl"></i>
                </div>
                <div>
                    <p class="text-gray-500 text-sm mb-1">Pending Actions</p>
                    <h3 class="text-2xl font-bold text-dark">
                        {{ $stats['pending_reservations'] + $stats['pending_acquisitions'] }}
                    </h3>
                </div>
            </div>
            <div class="mt-4 pt-4 border-t">
                <span class="text-sm text-gray-500">
                    <i class="fas fa-bell mr-1"></i>
                    Reservations & Acquisitions
                </span>
            </div>
        </div>
    </div>

    <!-- Additional Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <!-- Overdue Books -->
        <div class="bg-white rounded-xl shadow-md p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-dark">Overdue Books</h3>
                <span class="px-3 py-1 bg-red-100 text-red-600 rounded-full text-sm">
                    {{ $stats['overdue_books'] }} items
                </span>
            </div>
            <div class="flex items-center justify-center py-4">
                <div class="text-center">
                    <div class="text-3xl font-bold text-red-600 mb-2">{{ $stats['overdue_books'] }}</div>
                    <p class="text-gray-600 text-sm">Books overdue for return</p>
                </div>
            </div>
            <a href="{{ route('transactions.index') }}" class="block text-center text-primary hover:underline mt-4">
                View all transactions <i class="fas fa-arrow-right ml-1"></i>
            </a>
        </div>

        <!-- Total Fines -->
        <div class="bg-white rounded-xl shadow-md p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-dark">Total Fines</h3>
                <span class="px-3 py-1 bg-yellow-100 text-yellow-600 rounded-full text-sm">
                    Uncollected
                </span>
            </div>
            <div class="flex items-center justify-center py-4">
                <div class="text-center">
                    <div class="text-3xl font-bold text-yellow-600 mb-2">
                        ${{ number_format($stats['total_fines'], 2) }}
                    </div>
                    <p class="text-gray-600 text-sm">Outstanding fine amount</p>
                </div>
            </div>
            <a href="{{ route('fine-rules.index') }}" class="block text-center text-primary hover:underline mt-4">
                Manage fine rules <i class="fas fa-cog ml-1"></i>
            </a>
        </div>

        <!-- Popular Books -->
        <div class="bg-white rounded-xl shadow-md p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-dark">Top Books</h3>
                <span class="px-3 py-1 bg-blue-100 text-dark rounded-full text-sm">
                    This month
                </span>
            </div>
            <div class="space-y-3">
                @foreach($popular_books->take(3) as $book)
                <div class="flex items-center justify-between p-2 hover:bg-gray-50 rounded">
                    <div class="flex items-center">
                        <div class="w-8 h-8 bg-accent rounded flex items-center justify-center mr-3">
                            <i class="fas fa-book text-dark text-sm"></i>
                        </div>
                        <div>
                            <p class="font-medium text-sm truncate" title="{{ $book->title }}">
                                {{ Str::limit($book->title, 25) }}
                            </p>
                        </div>
                    </div>
                    <span class="bg-primary text-white text-xs px-2 py-1 rounded">
                        {{ $book->transactions_count }} loans
                    </span>
                </div>
                @endforeach
            </div>
            <a href="{{ route('book-popularities.index') }}" class="block text-center text-primary hover:underline mt-4">
                View full report <i class="fas fa-chart-line ml-1"></i>
            </a>
        </div>
    </div>

    <!-- Recent Activity Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Recent Transactions -->
        <div class="bg-white rounded-xl shadow-md">
            <div class="p-6 border-b">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-dark">Recent Transactions</h3>
                    <a href="{{ route('transactions.index') }}" class="text-primary hover:underline text-sm">
                        View all <i class="fas fa-external-link-alt ml-1"></i>
                    </a>
                </div>
            </div>
            <div class="p-6">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="text-left text-gray-500 text-sm border-b">
                                <th class="pb-3 font-medium">Member</th>
                                <th class="pb-3 font-medium">Book</th>
                                <th class="pb-3 font-medium">Status</th>
                                <th class="pb-3 font-medium">Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recent_transactions as $transaction)
                            <tr class="border-b hover:bg-gray-50">
                                <td class="py-3">
                                    <div class="flex items-center">
                                        <div class="w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center mr-2">
                                            <i class="fas fa-user text-gray-600"></i>
                                        </div>
                                        <span class="text-sm">{{ $transaction->member->name ?? 'N/A' }}</span>
                                    </div>
                                </td>
                                <td class="py-3 text-sm">
                                    {{ Str::limit($transaction->item->book->title ?? 'Unknown', 20) }}
                                </td>
                                <td class="py-3">
                                    <span class="px-2 py-1 rounded-full text-xs
                                        {{ $transaction->status == 'borrowed' ? 'bg-green-100 text-green-600' : 'bg-blue-100 text-dark' }}">
                                        {{ ucfirst($transaction->status) }}
                                    </span>
                                </td>
                                <td class="py-3 text-sm text-gray-500">
                                    {{ $transaction->created_at->format('M d') }}
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="py-4 text-center text-gray-500">
                                    No recent transactions
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Recent Members & Quick Actions -->
        <div class="space-y-8">
            <!-- Recent Members -->
            <div class="bg-white rounded-xl shadow-md">
                <div class="p-6 border-b">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-dark">Recent Members</h3>
                        <a href="{{ route('members.index') }}" class="text-primary hover:underline text-sm">
                            View all <i class="fas fa-external-link-alt ml-1"></i>
                        </a>
                    </div>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        @forelse($recent_members as $member)
                        <div class="flex items-center justify-between p-3 hover:bg-gray-50 rounded-lg">
                            <div class="flex items-center">
                                <div class="w-10 h-10 rounded-full bg-primary bg-opacity-10 flex items-center justify-center mr-3">
                                    <i class="fas fa-user text-primary"></i>
                                </div>
                                <div>
                                    <p class="font-medium">{{ $member->name }}</p>
                                    <p class="text-sm text-gray-500">{{ $member->email }}</p>
                                </div>
                            </div>
                            <span class="text-xs text-gray-500">
                                Joined {{ $member->created_at->diffForHumans() }}
                            </span>
                        </div>
                        @empty
                        <p class="text-center text-gray-500 py-4">No recent members</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="bg-white rounded-xl shadow-md">
                <div class="p-6 border-b">
                    <h3 class="text-lg font-semibold text-dark">Quick Actions</h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-2 gap-4">
                        <a href="{{ route('transactions.index') }}"
                           class="flex flex-col items-center justify-center p-4 border rounded-lg hover:bg-gray-50 transition">
                            <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mb-3">
                                <i class="fas fa-plus text-green-600"></i>
                            </div>
                            <span class="font-medium text-sm">New Loan</span>
                        </a>
                        <a href="{{ route('members.index') }}"
                           class="flex flex-col items-center justify-center p-4 border rounded-lg hover:bg-gray-50 transition">
                            <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center mb-3">
                                <i class="fas fa-user-plus text-dark"></i>
                            </div>
                            <span class="font-medium text-sm">Add Member</span>
                        </a>
                        <a href="{{ route('books.index') }}"
                           class="flex flex-col items-center justify-center p-4 border rounded-lg hover:bg-gray-50 transition">
                            <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center mb-3">
                                <i class="fas fa-book-medical text-purple-600"></i>
                            </div>
                            <span class="font-medium text-sm">Add Book</span>
                        </a>
                        <a href="{{ route('acquisition-requests.index') }}"
                           class="flex flex-col items-center justify-center p-4 border rounded-lg hover:bg-gray-50 transition">
                            <div class="w-12 h-12 bg-yellow-100 rounded-full flex items-center justify-center mb-3">
                                <i class="fas fa-shopping-cart text-yellow-600"></i>
                            </div>
                            <span class="font-medium text-sm">Request Book</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer Stats -->
    <div class="mt-8 p-6 bg-dark text-white rounded-xl shadow-md">
        <div class="flex flex-col md:flex-row items-center justify-between">
            <div class="text-center md:text-left mb-4 md:mb-0">
                <h4 class="font-semibold mb-2">Library Statistics Summary</h4>
                <p class="text-accent text-sm">Last updated: {{ now()->format('g:i A') }}</p>
            </div>
            <div class="flex space-x-6">
                <div class="text-center">
                    <div class="text-2xl font-bold">{{ $stats['pending_reservations'] }}</div>
                    <p class="text-accent text-sm">Pending Reservations</p>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold">{{ $stats['pending_acquisitions'] }}</div>
                    <p class="text-accent text-sm">Acquisition Requests</p>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold">{{ $recent_transactions->count() }}</div>
                    <p class="text-accent text-sm">Today's Transactions</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Auto-refresh dashboard every 5 minutes
    setTimeout(function() {
        window.location.reload();
    }, 300000); // 5 minutes

    // Initialize tooltips
    document.addEventListener('DOMContentLoaded', function() {
        // Add hover effects to stat cards
        const statCards = document.querySelectorAll('.stat-card');
        statCards.forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-5px)';
            });
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
            });
        });
    });
</script>
@endsection
