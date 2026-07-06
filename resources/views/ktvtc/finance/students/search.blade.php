@extends('ktvtc.finance.layouts.app')

@section('title', 'Search Students')
@section('subtitle', 'Search and view student financial information')

@section('breadcrumb')
<li>
    <span class="mx-2">/</span>
    <span class="text-gray-400">Search Students</span>
</li>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Search Form -->
    <div class="finance-card p-6">
        <form method="GET" action="{{ route('finance.students.search') }}" class="flex flex-wrap gap-4">
            <div class="flex-1 min-w-[200px]">
                <label class="text-xs font-semibold text-gray-600">Search</label>
                <div class="flex">
                    <input type="text" name="search" placeholder="Name, student number, email..." value="{{ request('search') }}" class="w-full px-4 py-3 border rounded-l-lg focus:outline-none focus:ring-2 focus:ring-primary">
                    <button type="submit" class="px-6 py-3 bg-primary text-white rounded-r-lg hover:bg-primary-dark transition">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>
            <div>
                <label class="text-xs font-semibold text-gray-600">Campus</label>
                <select name="campus_id" class="px-4 py-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                    <option value="">All Campuses</option>
                    @foreach($campuses ?? [] as $campus)
                        <option value="{{ $campus->id }}" {{ request('campus_id') == $campus->id ? 'selected' : '' }}>
                            {{ $campus->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="text-xs font-semibold text-gray-600">Status</label>
                <select name="status" class="px-4 py-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                    <option value="">All Status</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="graduated" {{ request('status') == 'graduated' ? 'selected' : '' }}>Graduated</option>
                    <option value="suspended" {{ request('status') == 'suspended' ? 'selected' : '' }}>Suspended</option>
                    <option value="dropped" {{ request('status') == 'dropped' ? 'selected' : '' }}>Dropped</option>
                </select>
            </div>
            <div>
                <label class="text-xs font-semibold text-gray-600">Balance</label>
                <select name="balance_filter" class="px-4 py-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                    <option value="">All</option>
                    <option value="has_balance" {{ request('balance_filter') == 'has_balance' ? 'selected' : '' }}>Has Balance</option>
                    <option value="fully_paid" {{ request('balance_filter') == 'fully_paid' ? 'selected' : '' }}>Fully Paid</option>
                    <option value="overdue" {{ request('balance_filter') == 'overdue' ? 'selected' : '' }}>Overdue</option>
                </select>
            </div>
            <div class="flex items-end">
                <button type="submit" class="px-6 py-3 bg-primary text-white rounded-lg hover:bg-primary-dark transition font-semibold">
                    <i class="fas fa-search mr-2"></i> Search
                </button>
                @if(request('search') || request('campus_id') || request('status') || request('balance_filter'))
                    <a href="{{ route('finance.students.search') }}" class="ml-2 px-4 py-3 border rounded-lg hover:bg-gray-50 transition">
                        <i class="fas fa-times"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Results Count -->
    @if(isset($students))
        <div class="text-sm text-gray-500">
            Found {{ $students->total() }} student(s)
        </div>
    @endif

    <!-- Students Table -->
    <div class="finance-card p-4">
        <div class="overflow-x-auto">
            <table class="w-full finance-table">
                <thead>
                    <tr>
                        <th class="text-left py-3 px-4">Student</th>
                        <th class="text-left py-3 px-4">Student #</th>
                        <th class="text-left py-3 px-4">Campus</th>
                        <th class="text-right py-3 px-4">Total Fees</th>
                        <th class="text-right py-3 px-4">Total Paid</th>
                        <th class="text-right py-3 px-4">Balance</th>
                        <th class="text-left py-3 px-4">Status</th>
                        <th class="text-center py-3 px-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($students ?? [] as $student)
                        @php
                            $totalFees = \App\Models\Enrollment::where('student_id', $student->id)->sum('total_fees');
                            $totalPaid = \App\Models\FeePayment::where('student_id', $student->id)->where('status', 'completed')->sum('amount');
                            $balance = $totalFees - $totalPaid;
                        @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="py-3 px-4">
                                <span class="font-medium">{{ $student->full_name ?? 'N/A' }}</span>
                                <span class="text-xs text-gray-500 block">{{ $student->email ?? '' }}</span>
                            </td>
                            <td class="py-3 px-4">{{ $student->student_number ?? 'N/A' }}</td>
                            <td class="py-3 px-4">{{ $student->campus->name ?? 'N/A' }}</td>
                            <td class="py-3 px-4 text-right font-medium">KES {{ number_format($totalFees, 2) }}</td>
                            <td class="py-3 px-4 text-right text-green-600 font-medium">KES {{ number_format($totalPaid, 2) }}</td>
                            <td class="py-3 px-4 text-right font-bold {{ $balance > 0 ? 'text-red-600' : 'text-green-600' }}">
                                KES {{ number_format(abs($balance), 2) }}
                            </td>
                            <td class="py-3 px-4">
                                <span class="status-badge status-{{ $student->status }}">{{ ucfirst($student->status ?? 'N/A') }}</span>
                            </td>
                            <td class="py-3 px-4 text-center">
                                <div class="flex items-center justify-center space-x-2">
                                    <a href="{{ route('finance.students.financial', $student) }}" class="text-primary hover:text-primary-dark" title="Financial Details">
                                        <i class="fas fa-coins"></i>
                                    </a>
                                    <a href="{{ route('finance.students.transactions', $student) }}" class="text-blue-600 hover:text-blue-800" title="Transactions">
                                        <i class="fas fa-list"></i>
                                    </a>
                                    <a href="{{ route('finance.students.statement', $student) }}" class="text-gray-500 hover:text-gray-700" title="Statement" target="_blank">
                                        <i class="fas fa-file-invoice"></i>
                                    </a>
                                    <a href="{{ route('finance.student-fees.create', ['student_id' => $student->id]) }}" class="text-green-600 hover:text-green-800" title="Record Payment">
                                        <i class="fas fa-credit-card"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-8 text-center text-gray-500">
                                <i class="fas fa-search text-4xl text-gray-300 mb-2 block"></i>
                                @if(request('search'))
                                    No students found matching your search
                                @else
                                    Search for students to view their financial information
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">
            {{ $students->appends(request()->query())->links() }}
        </div>
    </div>

    <!-- Quick Stats -->
    @if(isset($students) && $students->count() > 0)
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-white p-4 rounded-lg border border-gray-200 text-center">
                <p class="text-sm text-gray-500">Total Students</p>
                <p class="text-2xl font-bold text-gray-800">{{ $students->total() }}</p>
            </div>
            <div class="bg-white p-4 rounded-lg border border-gray-200 text-center">
                <p class="text-sm text-gray-500">With Balance</p>
                @php
                    $withBalance = 0;
                    foreach($students as $s) {
                        $tf = \App\Models\Enrollment::where('student_id', $s->id)->sum('total_fees');
                        $tp = \App\Models\FeePayment::where('student_id', $s->id)->where('status', 'completed')->sum('amount');
                        if ($tf - $tp > 0) $withBalance++;
                    }
                @endphp
                <p class="text-2xl font-bold text-yellow-600">{{ $withBalance }}</p>
            </div>
            <div class="bg-white p-4 rounded-lg border border-gray-200 text-center">
                <p class="text-sm text-gray-500">Fully Paid</p>
                <p class="text-2xl font-bold text-green-600">{{ $students->total() - $withBalance }}</p>
            </div>
            <div class="bg-white p-4 rounded-lg border border-gray-200 text-center">
                <p class="text-sm text-gray-500">Active Students</p>
                @php
                    $active = 0;
                    foreach($students as $s) {
                        if ($s->status === 'active') $active++;
                    }
                @endphp
                <p class="text-2xl font-bold text-blue-600">{{ $active }}</p>
            </div>
        </div>
    @endif
</div>
@endpush
