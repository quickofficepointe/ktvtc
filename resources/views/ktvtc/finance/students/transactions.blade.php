@extends('ktvtc.finance.layouts.app')

@section('title', 'Student Transactions')
@section('subtitle', 'View all transactions for student')

@section('breadcrumb')
<li>
    <span class="mx-2">/</span>
    <a href="{{ route('finance.students.search') }}" class="text-gray-600 hover:text-primary">Students</a>
</li>
<li>
    <span class="mx-2">/</span>
    <a href="{{ route('finance.students.financial', $student) }}" class="text-gray-600 hover:text-primary">{{ $student->full_name ?? 'Student' }}</a>
</li>
<li>
    <span class="mx-2">/</span>
    <span class="text-gray-400">Transactions</span>
</li>
@endsection

@section('header-actions')
<div class="flex space-x-3">
    <a href="{{ route('finance.students.statement', $student) }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-semibold flex items-center transition" target="_blank">
        <i class="fas fa-file-invoice mr-2"></i> Statement
    </a>
    <a href="{{ route('finance.student-fees.create', ['student_id' => $student->id]) }}" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-semibold flex items-center transition">
        <i class="fas fa-credit-card mr-2"></i> Record Payment
    </a>
</div>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Student Summary -->
    <div class="finance-card p-4 bg-gray-50">
        <div class="flex flex-wrap items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Student</p>
                <h3 class="text-lg font-bold text-gray-800">{{ $student->full_name ?? 'N/A' }}</h3>
                <p class="text-sm text-gray-500">{{ $student->student_number ?? 'N/A' }}</p>
            </div>
            <div class="text-right">
                <p class="text-sm text-gray-500">Balance</p>
                @php
                    $totalFees = \App\Models\Enrollment::where('student_id', $student->id)->sum('total_fees');
                    $totalPaid = \App\Models\FeePayment::where('student_id', $student->id)->where('status', 'completed')->sum('amount');
                    $balance = $totalFees - $totalPaid;
                @endphp
                <p class="text-2xl font-bold {{ $balance > 0 ? 'text-red-600' : 'text-green-600' }}">
                    KES {{ number_format(abs($balance), 2) }}
                </p>
            </div>
        </div>
    </div>

    <!-- Transactions Table -->
    <div class="finance-card p-4">
        <div class="overflow-x-auto">
            <table class="w-full finance-table">
                <thead>
                    <tr>
                        <th class="text-left py-3 px-4">Receipt #</th>
                        <th class="text-left py-3 px-4">Course</th>
                        <th class="text-right py-3 px-4">Amount</th>
                        <th class="text-left py-3 px-4">Method</th>
                        <th class="text-left py-3 px-4">Date</th>
                        <th class="text-left py-3 px-4">Status</th>
                        <th class="text-center py-3 px-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions ?? [] as $payment)
                        <tr class="hover:bg-gray-50">
                            <td class="py-3 px-4">
                                <span class="font-medium text-primary">{{ $payment->receipt_number }}</span>
                            </td>
                            <td class="py-3 px-4">
                                <span class="text-sm">{{ $payment->enrollment->course->name ?? 'N/A' }}</span>
                            </td>
                            <td class="py-3 px-4 text-right font-semibold">KES {{ number_format($payment->amount, 2) }}</td>
                            <td class="py-3 px-4">
                                <span class="text-xs uppercase px-2 py-1 rounded bg-gray-100">{{ $payment->payment_method }}</span>
                            </td>
                            <td class="py-3 px-4 text-sm">{{ $payment->payment_date->format('d M Y H:i') }}</td>
                            <td class="py-3 px-4">
                                @if($payment->is_verified && $payment->status === 'completed')
                                    <span class="status-badge status-verified"><i class="fas fa-check-circle mr-1"></i> Verified</span>
                                @elseif($payment->status === 'completed' && !$payment->is_verified)
                                    <span class="status-badge status-pending"><i class="fas fa-clock mr-1"></i> Pending</span>
                                @elseif($payment->status === 'reversed')
                                    <span class="status-badge status-failed"><i class="fas fa-times-circle mr-1"></i> Reversed</span>
                                @else
                                    <span class="status-badge status-pending">{{ ucfirst($payment->status) }}</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-center">
                                <div class="flex items-center justify-center space-x-2">
                                    <a href="{{ route('finance.student-fees.show', $payment) }}" class="text-primary hover:text-primary-dark" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('finance.student-fees.receipt', $payment) }}" class="text-gray-500 hover:text-gray-700" title="Receipt" target="_blank">
                                        <i class="fas fa-print"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-gray-500">
                                <i class="fas fa-credit-card text-4xl text-gray-300 mb-2 block"></i>
                                No transactions found for this student
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot class="border-t-2 border-gray-300">
                    <tr>
                        <td colspan="2" class="py-2 px-4 font-bold">Total</td>
                        <td class="py-2 px-4 text-right font-bold">KES {{ number_format($transactions->sum('amount'), 2) }}</td>
                        <td colspan="4" class="py-2 px-4"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
        <div class="mt-4">
            {{ $transactions->links() }}
        </div>
    </div>
</div>
@endpush
