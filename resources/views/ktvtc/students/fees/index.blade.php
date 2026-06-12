@extends('ktvtc.students.layout.studentlayout')

@section('title', 'Fee Statement')

@section('content')
<div class="container mx-auto">
    <!-- Header -->
    <div class="bg-white rounded-xl shadow-sm p-6 mb-6 border border-gray-100">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Fee Statement</h1>
                <p class="text-gray-500 mt-1">Student: {{ $student->full_name ?? 'N/A' }} ({{ $student->student_number ?? 'N/A' }})</p>
            </div>
            <div class="flex gap-3">
                <a href="{{ route('student.fees.statement.download') }}"
                   class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition-colors flex items-center">
                    <i class="fas fa-download mr-2"></i> Download PDF
                </a>
                @if($totalBalance > 0)
                <button onclick="window.location.href='{{ route('student.fees.pay', $enrollments->first()->id ?? '') }}'"
                       class="px-4 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg transition-colors flex items-center">
                    <i class="fas fa-credit-card mr-2"></i> Make Payment
                </button>
                @endif
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <div class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-primary">
            <p class="text-sm text-gray-500">Total Fees</p>
            <p class="text-2xl font-bold text-gray-800">KES {{ number_format($totalFees, 2) }}</p>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-green-500">
            <p class="text-sm text-gray-500">Total Paid</p>
            <p class="text-2xl font-bold text-green-600">KES {{ number_format($totalPaid, 2) }}</p>
            <p class="text-xs text-gray-500 mt-1">{{ $totalFees > 0 ? round(($totalPaid / $totalFees) * 100, 1) : 0 }}% paid</p>
            <div class="w-full bg-gray-200 rounded-full h-1.5 mt-2">
                <div class="bg-green-500 rounded-full h-1.5" style="width: {{ $totalFees > 0 ? ($totalPaid / $totalFees) * 100 : 0 }}%"></div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-5 border-l-4 {{ $totalBalance > 0 ? 'border-red-500' : 'border-green-500' }}">
            <p class="text-sm text-gray-500">Outstanding Balance</p>
            <p class="text-2xl font-bold {{ $totalBalance > 0 ? 'text-red-600' : 'text-green-600' }}">
                KES {{ number_format($totalBalance, 2) }}
            </p>
        </div>
    </div>

    <!-- Enrollments Table -->
    <div class="bg-white rounded-xl shadow-sm overflow-hidden mb-6">
        <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-800">
                <i class="fas fa-book mr-2 text-primary"></i> Course Enrollments
            </h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Course</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Intake</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total Fees</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Amount Paid</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Balance</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($enrollments as $enrollment)
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
                                $statusColors = ['active' => 'green', 'completed' => 'blue', 'dropped' => 'red', 'suspended' => 'yellow'];
                                $color = $statusColors[$enrollment->status] ?? 'gray';
                            @endphp
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-{{ $color }}-100 text-{{ $color }}-800">
                                {{ ucfirst($enrollment->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            @if($enrollment->balance > 0)
                            <a href="{{ route('student.fees.pay', $enrollment) }}"
                               class="inline-flex items-center px-3 py-1.5 bg-primary text-white text-sm rounded-lg hover:bg-primary-dark transition-colors">
                                <i class="fas fa-credit-card mr-1"></i> Pay
                            </a>
                            @else
                            <span class="text-green-600 text-sm">Fully Paid</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                            <i class="fas fa-book-open text-4xl mb-2 text-gray-300"></i>
                            <p>No enrollments found.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Payment History -->
    @if($recentPayments->count() > 0)
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-800">
                <i class="fas fa-history mr-2 text-primary"></i> Payment History
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
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Receipt</th>
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
                            <span class="capitalize">{{ $payment->payment_method_label }}</span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <a href="{{ route('admin.fee-payments.receipt', $payment) }}" target="_blank" class="text-primary hover:text-primary-dark">
                                <i class="fas fa-print"></i>
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>
@endsection
