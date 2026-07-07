@extends('ktvtc.finance.layouts.app')

@section('title', 'High School Card Dashboard')
@section('subtitle', 'Overview of high school cafeteria card system')

@section('content')
<div class="space-y-6">
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="finance-card p-4 border-l-4 border-blue-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Total Students</p>
                    <p class="text-2xl font-bold text-gray-800">{{ number_format($totalStudents ?? 0) }}</p>
                    <p class="text-xs text-gray-500">{{ number_format($activeStudents ?? 0) }} Active</p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-user-graduate text-blue-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="finance-card p-4 border-l-4 border-green-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Total Cards</p>
                    <p class="text-2xl font-bold text-gray-800">{{ number_format($totalCards ?? 0) }}</p>
                    <p class="text-xs text-gray-500">{{ number_format($activeCards ?? 0) }} Active</p>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-credit-card text-green-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="finance-card p-4 border-l-4 border-yellow-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Total Balance</p>
                    <p class="text-2xl font-bold text-yellow-600">KES {{ number_format($totalBalance ?? 0, 2) }}</p>
                    <p class="text-xs text-gray-500">Across all cards</p>
                </div>
                <div class="w-12 h-12 bg-yellow-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-wallet text-yellow-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="finance-card p-4 border-l-4 border-red-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">No Card Issued</p>
                    <p class="text-2xl font-bold text-red-600">{{ number_format($studentsWithoutCards ?? 0) }}</p>
                    <p class="text-xs text-gray-500">Need card issuance</p>
                </div>
                <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions & Recent Activity -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent Funding -->
        <div class="finance-card p-5">
            <div class="flex justify-between items-center mb-4">
                <h3 class="font-bold text-gray-800">Recent Funding Requests</h3>
                <a href="{{ route('finance.funding.index') }}" class="text-sm text-primary hover:underline">View All →</a>
            </div>
            <div class="space-y-3 max-h-80 overflow-y-auto">
                @forelse($recentFunding ?? [] as $funding)
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition">
                        <div>
                            <p class="font-medium text-gray-800">{{ $funding->student_name ?? 'N/A' }}</p>
                            <p class="text-sm text-gray-500">KES {{ number_format($funding->amount, 2) }}</p>
                            <p class="text-xs text-gray-400">{{ $funding->created_at->diffForHumans() }}</p>
                        </div>
                        <div class="text-right">
                            <span class="text-xs px-2 py-1 rounded
                                @if($funding->status === 'completed') bg-green-100 text-green-600
                                @elseif($funding->status === 'pending' || $funding->status === 'processing') bg-yellow-100 text-yellow-600
                                @else bg-red-100 text-red-600 @endif">
                                {{ ucfirst($funding->status) }}
                            </span>
                        </div>
                    </div>
                @empty
                    <p class="text-gray-500 text-center py-6">No recent funding requests</p>
                @endforelse
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="finance-card p-5">
            <h3 class="font-bold text-gray-800 mb-4">Quick Actions</h3>
            <div class="grid grid-cols-2 gap-3">
                <a href="{{ route('finance.hs-students.create') }}" class="p-4 bg-green-50 rounded-lg hover:bg-green-100 transition text-center">
                    <i class="fas fa-user-plus text-green-600 text-2xl mb-2 block"></i>
                    <span class="text-sm font-medium text-gray-700">Add Student</span>
                </a>
                <a href="{{ route('finance.hs-students.import') }}" class="p-4 bg-blue-50 rounded-lg hover:bg-blue-100 transition text-center">
                    <i class="fas fa-file-import text-blue-600 text-2xl mb-2 block"></i>
                    <span class="text-sm font-medium text-gray-700">Import Students</span>
                </a>
                <a href="{{ route('finance.cards.index') }}" class="p-4 bg-purple-50 rounded-lg hover:bg-purple-100 transition text-center">
                    <i class="fas fa-credit-card text-purple-600 text-2xl mb-2 block"></i>
                    <span class="text-sm font-medium text-gray-700">Manage Cards</span>
                </a>
                <a href="{{ route('finance.card-reports.index') }}" class="p-4 bg-yellow-50 rounded-lg hover:bg-yellow-100 transition text-center">
                    <i class="fas fa-chart-bar text-yellow-600 text-2xl mb-2 block"></i>
                    <span class="text-sm font-medium text-gray-700">View Reports</span>
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
