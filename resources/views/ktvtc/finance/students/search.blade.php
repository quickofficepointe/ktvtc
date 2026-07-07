@extends('ktvtc.finance.layouts.app')

@section('title', 'Search Students')
@section('subtitle', 'Search and view student financial information')

@section('breadcrumb')
    <li><span class="mx-2">/</span></li>
    <li class="text-primary font-medium whitespace-nowrap">Search Students</li>
@endsection

@section('content')
<div class="space-y-6">
    <div class="finance-card relative overflow-hidden p-4 sm:p-6">
        <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-primary via-red-400 to-primary"></div>

        <form method="GET" action="{{ route('finance.students.search') }}" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-5 gap-4">
            <div class="xl:col-span-2">
                <label class="text-xs font-semibold text-gray-600 block mb-1">
                    Search
                </label>

                <div class="flex">
                    <input type="text"
                           name="search"
                           placeholder="Name, student number, email..."
                           value="{{ request('search') }}"
                           class="w-full px-4 py-3 border border-gray-300 rounded-l-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">

                    <button type="submit"
                            class="px-5 py-3 bg-primary text-white rounded-r-lg hover:bg-primary-dark transition">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>

            <div>
                <label class="text-xs font-semibold text-gray-600 block mb-1">
                    Campus
                </label>

                <select name="campus_id"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                    <option value="">All Campuses</option>
                    @foreach($campuses ?? [] as $campus)
                        <option value="{{ $campus->id }}" {{ request('campus_id') == $campus->id ? 'selected' : '' }}>
                            {{ $campus->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="text-xs font-semibold text-gray-600 block mb-1">
                    Status
                </label>

                <select name="status"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                    <option value="">All Status</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="graduated" {{ request('status') == 'graduated' ? 'selected' : '' }}>Graduated</option>
                    <option value="suspended" {{ request('status') == 'suspended' ? 'selected' : '' }}>Suspended</option>
                    <option value="dropped" {{ request('status') == 'dropped' ? 'selected' : '' }}>Dropped</option>
                </select>
            </div>

            <div>
                <label class="text-xs font-semibold text-gray-600 block mb-1">
                    Balance
                </label>

                <select name="balance_filter"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                    <option value="">All</option>
                    <option value="has_balance" {{ request('balance_filter') == 'has_balance' ? 'selected' : '' }}>Has Balance</option>
                    <option value="fully_paid" {{ request('balance_filter') == 'fully_paid' ? 'selected' : '' }}>Fully Paid</option>
                    <option value="overdue" {{ request('balance_filter') == 'overdue' ? 'selected' : '' }}>Overdue</option>
                </select>
            </div>

            <div class="md:col-span-2 xl:col-span-5 flex flex-col sm:flex-row gap-2 justify-end">
                @if(request('search') || request('campus_id') || request('status') || request('balance_filter'))
                    <a href="{{ route('finance.students.search') }}"
                       class="inline-flex items-center justify-center gap-2 px-5 py-3 border border-gray-300 rounded-lg hover:bg-gray-50 transition font-medium">
                        <i class="fas fa-times"></i>
                        Clear
                    </a>
                @endif

                <button type="submit"
                        class="inline-flex items-center justify-center gap-2 px-6 py-3 bg-primary text-white rounded-lg hover:bg-primary-dark transition font-semibold">
                    <i class="fas fa-search"></i>
                    Search
                </button>
            </div>
        </form>
    </div>

    @if(isset($students))
        <div class="text-sm text-gray-500">
            Found {{ method_exists($students, 'total') ? $students->total() : collect($students)->count() }} student(s)
        </div>
    @endif

    <div class="finance-card relative overflow-hidden p-4 sm:p-6">
        <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-primary via-red-400 to-primary"></div>

        <div class="table-responsive">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-gray-500 border-b">
                        <th class="py-3 px-4 font-semibold">Student</th>
                        <th class="py-3 px-4 font-semibold">Student #</th>
                        <th class="py-3 px-4 font-semibold">Campus</th>
                        <th class="py-3 px-4 font-semibold text-right">Total Fees</th>
                        <th class="py-3 px-4 font-semibold text-right">Total Paid</th>
                        <th class="py-3 px-4 font-semibold text-right">Balance</th>
                        <th class="py-3 px-4 font-semibold">Status</th>
                        <th class="py-3 px-4 font-semibold text-center">Actions</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($students ?? [] as $student)
                        @php
                            $totalFees = $student->total_fees ?? \App\Models\Enrollment::where('student_id', $student->id)->sum('total_fees');
                            $totalPaid = $student->total_paid ?? \App\Models\FeePayment::where('student_id', $student->id)->where('status', 'completed')->sum('amount');
                            $balance = $totalFees - $totalPaid;

                            $studentStatus = strtolower($student->status ?? '');

                            $studentBadge = match($studentStatus) {
                                'active' => 'status-active',
                                'graduated' => 'status-success',
                                'suspended' => 'status-warning',
                                'dropped' => 'status-inactive',
                                default => 'status-inactive',
                            };
                        @endphp

                        <tr class="border-b border-gray-100 hover:bg-gray-50">
                            <td class="py-3 px-4">
                                <span class="font-medium text-gray-800">
                                    {{ $student->full_name ?? 'N/A' }}
                                </span>
                                <span class="text-xs text-gray-500 block break-all">
                                    {{ $student->email ?? '' }}
                                </span>
                            </td>

                            <td class="py-3 px-4">
                                {{ $student->student_number ?? 'N/A' }}
                            </td>

                            <td class="py-3 px-4">
                                {{ $student->campus->name ?? 'N/A' }}
                            </td>

                            <td class="py-3 px-4 text-right font-medium">
                                KES {{ number_format($totalFees, 2) }}
                            </td>

                            <td class="py-3 px-4 text-right text-green-600 font-medium">
                                KES {{ number_format($totalPaid, 2) }}
                            </td>

                            <td class="py-3 px-4 text-right font-bold {{ $balance > 0 ? 'text-red-600' : 'text-green-600' }}">
                                KES {{ number_format(abs($balance), 2) }}
                            </td>

                            <td class="py-3 px-4">
                                <span class="status-badge {{ $studentBadge }}">
                                    {{ ucfirst($studentStatus ?: 'N/A') }}
                                </span>
                            </td>

                            <td class="py-3 px-4 text-center">
                                <div class="flex items-center justify-center gap-3">
                                    <a href="{{ route('finance.students.financial', $student) }}"
                                       class="text-primary hover:text-primary-dark"
                                       title="Financial Details">
                                        <i class="fas fa-coins"></i>
                                    </a>

                                    <a href="{{ route('finance.students.transactions', $student) }}"
                                       class="text-blue-600 hover:text-blue-800"
                                       title="Transactions">
                                        <i class="fas fa-list"></i>
                                    </a>

                                    <a href="{{ route('finance.students.statement', $student) }}"
                                       target="_blank"
                                       class="text-gray-500 hover:text-gray-700"
                                       title="Statement">
                                        <i class="fas fa-file-invoice"></i>
                                    </a>

                                    <a href="{{ route('finance.student-fees.create', ['student_id' => $student->id]) }}"
                                       class="text-green-600 hover:text-green-800"
                                       title="Record Payment">
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

        @if(isset($students) && method_exists($students, 'appends'))
            <div class="mt-4">
                {{ $students->appends(request()->query())->links() }}
            </div>
        @endif
    </div>

    @if(isset($students) && collect($students)->count() > 0)
        @php
            $withBalance = 0;
            $active = 0;

            foreach ($students as $s) {
                $tf = $s->total_fees ?? \App\Models\Enrollment::where('student_id', $s->id)->sum('total_fees');
                $tp = $s->total_paid ?? \App\Models\FeePayment::where('student_id', $s->id)->where('status', 'completed')->sum('amount');

                if (($tf - $tp) > 0) {
                    $withBalance++;
                }

                if (($s->status ?? '') === 'active') {
                    $active++;
                }
            }

            $studentCount = method_exists($students, 'total') ? $students->total() : collect($students)->count();
        @endphp

        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
            <div class="finance-card p-4 text-center">
                <p class="text-sm text-gray-500">Total Students</p>
                <p class="text-2xl font-bold text-gray-800">{{ $studentCount }}</p>
            </div>

            <div class="finance-card p-4 text-center bg-yellow-50 border-yellow-200">
                <p class="text-sm text-gray-500">With Balance</p>
                <p class="text-2xl font-bold text-yellow-600">{{ $withBalance }}</p>
            </div>

            <div class="finance-card p-4 text-center bg-green-50 border-green-200">
                <p class="text-sm text-gray-500">Fully Paid</p>
                <p class="text-2xl font-bold text-green-600">
                    {{ max($studentCount - $withBalance, 0) }}
                </p>
            </div>

            <div class="finance-card p-4 text-center bg-blue-50 border-blue-200">
                <p class="text-sm text-gray-500">Active Students</p>
                <p class="text-2xl font-bold text-blue-600">{{ $active }}</p>
            </div>
        </div>
    @endif
</div>
@endsection
