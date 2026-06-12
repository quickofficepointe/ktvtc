@extends('ktvtc.students.layout.studentlayout')

@section('content')
<div class="relative">

    {{-- Main dashboard content --}}
    <div>
        <!-- Welcome Header -->
        <div class="bg-white rounded-xl shadow-sm p-6 mb-6 border border-gray-100">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">Welcome back, {{ auth()->user()->name }}!</h1>
                    <p class="text-gray-500 mt-1">Here's what's happening with your academic journey today.</p>
                </div>
                <div class="w-12 h-12 bg-primary bg-opacity-10 rounded-full flex items-center justify-center">
                    <i class="fas fa-graduation-cap text-primary text-xl"></i>
                </div>
            </div>
        </div>

        {{-- Stats Cards --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
            <div class="bg-white rounded-xl shadow-sm p-5 border border-gray-100 card-hover">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 mb-1">Enrolled Courses</p>
                        <p class="text-2xl font-bold text-gray-800">{{ $enrollmentCount ?? 0 }}</p>
                    </div>
                    <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-book-open text-blue-600"></i>
                    </div>
                </div>
                <div class="mt-3 text-xs text-gray-400">
                    <i class="fas fa-check-circle text-green-500 mr-1"></i> Active courses
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-5 border border-gray-100 card-hover">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 mb-1">Total Fees</p>
                        <p class="text-2xl font-bold text-gray-800">KES {{ number_format($totalFees ?? 0, 2) }}</p>
                    </div>
                    <div class="w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-money-bill-wave text-purple-600"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-5 border border-gray-100 card-hover">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 mb-1">Amount Paid</p>
                        <p class="text-2xl font-bold text-green-600">KES {{ number_format($totalPaid ?? 0, 2) }}</p>
                    </div>
                    <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-check-circle text-green-600"></i>
                    </div>
                </div>
                <div class="mt-3 text-xs text-gray-400">
                    <i class="fas fa-chart-line mr-1"></i> {{ ($totalFees ?? 0) > 0 ? round(($totalPaid ?? 0) / ($totalFees ?? 1) * 100, 1) : 0 }}% paid
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-5 border border-gray-100 card-hover">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 mb-1">Outstanding Balance</p>
                        <p class="text-2xl font-bold {{ ($totalBalance ?? 0) > 0 ? 'text-red-600' : 'text-green-600' }}">
                            KES {{ number_format($totalBalance ?? 0, 2) }}
                        </p>
                    </div>
                    <div class="w-10 h-10 {{ ($totalBalance ?? 0) > 0 ? 'bg-red-100' : 'bg-green-100' }} rounded-full flex items-center justify-center">
                        <i class="fas fa-credit-card {{ ($totalBalance ?? 0) > 0 ? 'text-red-600' : 'text-green-600' }}"></i>
                    </div>
                </div>
            </div>
        </div>

        {{-- Enrollments Table --}}
        @if(isset($enrollments) && $enrollments->count() > 0)
        <div class="bg-white rounded-xl shadow-sm overflow-hidden mb-8">
            <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-gray-800">
                        <i class="fas fa-book text-primary mr-2"></i> My Enrollments
                    </h2>
                    <a href="{{ route('student.fees.index') }}" class="text-primary hover:text-primary-dark text-sm font-medium">
                        View Full Statement <i class="fas fa-arrow-right ml-1"></i>
                    </a>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Course</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Intake</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total Fees</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Paid</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Balance</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($enrollments as $enrollment)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <p class="font-medium text-gray-800">{{ $enrollment->course_name }}</p>
                                <p class="text-xs text-gray-500">{{ $enrollment->course_code ?? '' }}</p>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">
                                {{ $enrollment->intake_month }} {{ $enrollment->intake_year }}
                            </td>
                            <td class="px-6 py-4 text-right font-medium">
                                KES {{ number_format($enrollment->total_fees, 2) }}
                            </td>
                            <td class="px-6 py-4 text-right text-green-600">
                                KES {{ number_format($enrollment->amount_paid, 2) }}
                            </td>
                            <td class="px-6 py-4 text-right {{ $enrollment->balance > 0 ? 'text-red-600 font-bold' : 'text-green-600' }}">
                                KES {{ number_format($enrollment->balance, 2) }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                @php
                                    $statusColors = [
                                        'active' => 'green',
                                        'completed' => 'blue',
                                        'dropped' => 'red',
                                        'suspended' => 'yellow',
                                        'pending' => 'orange',
                                    ];
                                    $color = $statusColors[$enrollment->status] ?? 'gray';
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-{{ $color }}-100 text-{{ $color }}-800">
                                    <i class="fas fa-circle mr-1 text-{{ $color }}-500 text-xs"></i>
                                    {{ ucfirst($enrollment->status) }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        {{-- Recent Payments Table --}}
        @if(isset($recentPayments) && $recentPayments->count() > 0)
        <div class="bg-white rounded-xl shadow-sm overflow-hidden mb-8">
            <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-800">
                    <i class="fas fa-history text-primary mr-2"></i> Recent Payments
                </h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Receipt No.</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Course</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Method</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($recentPayments as $payment)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-sm">{{ $payment->payment_date->format('d/m/Y') }}</td>
                            <td class="px-6 py-4 text-sm font-mono text-primary">{{ $payment->receipt_number }}</td>
                            <td class="px-6 py-4 text-sm">{{ $payment->enrollment->course_name ?? 'N/A' }}</td>
                            <td class="px-6 py-4 text-right font-medium text-green-600">
                                KES {{ number_format($payment->amount, 2) }}
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                    @if($payment->payment_method == 'kcb') bg-blue-100 text-blue-800
                                    @elseif($payment->payment_method == 'mpesa') bg-green-100 text-green-800
                                    @elseif($payment->payment_method == 'cash') bg-yellow-100 text-yellow-800
                                    @else bg-gray-100 text-gray-800
                                    @endif">
                                    {{ $payment->payment_method_label }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        {{-- Quick Payment Alert --}}
        @if(($totalBalance ?? 0) > 0)
        <div class="bg-gradient-to-r from-primary/10 to-primary/5 rounded-xl p-5 border border-primary/20">
            <div class="flex flex-col md:flex-row items-center justify-between gap-4">
                <div class="flex items-start">
                    <div class="w-10 h-10 bg-primary bg-opacity-20 rounded-full flex items-center justify-center mr-3 flex-shrink-0">
                        <i class="fas fa-bell text-primary"></i>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-800">Outstanding Balance Alert</h3>
                        <p class="text-gray-600 text-sm">You have an outstanding balance of <span class="font-bold text-red-600">KES {{ number_format($totalBalance ?? 0, 2) }}</span></p>
                        <p class="text-xs text-gray-500 mt-1">Please clear your fees to avoid interruption of services.</p>
                    </div>
                </div>
                <a href="{{ route('student.fees.index') }}"
                   class="px-6 py-2.5 bg-primary hover:bg-primary-dark text-white font-semibold rounded-lg transition-colors shadow-md flex items-center">
                    <i class="fas fa-credit-card mr-2"></i> Pay Now
                </a>
            </div>
        </div>
        @endif

        {{-- No Data Message --}}
        @if(($enrollmentCount ?? 0) == 0)
        <div class="bg-white rounded-xl shadow-sm p-12 text-center">
            <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-book-open text-gray-400 text-3xl"></i>
            </div>
            <h3 class="text-lg font-semibold text-gray-800 mb-2">No Enrollments Found</h3>
            <p class="text-gray-500">You are not currently enrolled in any courses.</p>
            <p class="text-sm text-gray-400 mt-2">Please contact the admissions office for assistance.</p>
        </div>
        @endif
    </div>

</div>
@endsection
