@extends('ktvtc.finance.layouts.app')

@section('title', 'Student Transactions')
@section('subtitle', 'View all transactions for student')

@section('breadcrumb')
    <li><span class="mx-2">/</span></li>
    <li>
        <a href="{{ route('finance.students.search') }}" class="hover:text-primary transition whitespace-nowrap">
            Students
        </a>
    </li>
    <li><span class="mx-2">/</span></li>
    <li>
        <a href="{{ route('finance.students.financial', $student) }}" class="hover:text-primary transition whitespace-nowrap">
            {{ $student->full_name ?? 'Student' }}
        </a>
    </li>
    <li><span class="mx-2">/</span></li>
    <li class="text-primary font-medium whitespace-nowrap">Transactions</li>
@endsection

@section('header-actions')
    <div class="flex flex-col sm:flex-row gap-2 w-full sm:w-auto">
        <a href="{{ route('finance.students.statement', $student) }}"
           target="_blank"
           class="inline-flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-semibold transition">
            <i class="fas fa-file-invoice"></i>
            Statement
        </a>

        <a href="{{ route('finance.student-fees.create', ['student_id' => $student->id]) }}"
           class="inline-flex items-center justify-center gap-2 bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-semibold transition">
            <i class="fas fa-credit-card"></i>
            Record Payment
        </a>
    </div>
@endsection

@section('content')
@php
    $totalFees = $totalFees ?? \App\Models\Enrollment::where('student_id', $student->id)->sum('total_fees');
    $totalPaid = $totalPaid ?? \App\Models\FeePayment::where('student_id', $student->id)->where('status', 'completed')->sum('amount');
    $balance = $totalFees - $totalPaid;
@endphp

<div class="space-y-6">
    <div class="finance-card p-4 sm:p-6 relative overflow-hidden">
        <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-primary via-red-400 to-primary"></div>

        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <p class="text-sm text-gray-500">Student</p>
                <h3 class="text-lg font-bold text-gray-800">{{ $student->full_name ?? 'N/A' }}</h3>
                <p class="text-sm text-gray-500">{{ $student->student_number ?? 'N/A' }}</p>
            </div>

            <div class="sm:text-right">
                <p class="text-sm text-gray-500">Balance</p>
                <p class="text-2xl font-bold {{ $balance > 0 ? 'text-red-600' : 'text-green-600' }}">
                    KES {{ number_format(abs($balance), 2) }}
                </p>
                <p class="text-xs text-gray-500">{{ $balance > 0 ? 'Outstanding' : 'Fully Paid' }}</p>
            </div>
        </div>
    </div>

    <div class="finance-card p-4 sm:p-6 relative overflow-hidden">
        <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-primary via-red-400 to-primary"></div>

        <div class="table-responsive">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-gray-500 border-b">
                        <th class="py-3 px-4 font-semibold">Receipt #</th>
                        <th class="py-3 px-4 font-semibold">Course</th>
                        <th class="py-3 px-4 font-semibold text-right">Amount</th>
                        <th class="py-3 px-4 font-semibold">Method</th>
                        <th class="py-3 px-4 font-semibold">Date</th>
                        <th class="py-3 px-4 font-semibold">Status</th>
                        <th class="py-3 px-4 font-semibold text-center">Actions</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($transactions ?? [] as $payment)
                        @php
                            $status = strtolower($payment->status ?? '');
                        @endphp

                        <tr class="border-b border-gray-100 hover:bg-gray-50">
                            <td class="py-3 px-4">
                                <span class="font-medium text-primary">
                                    {{ $payment->receipt_number ?? 'N/A' }}
                                </span>
                            </td>

                            <td class="py-3 px-4">
                                {{ $payment->enrollment->course->name ?? 'N/A' }}
                            </td>

                            <td class="py-3 px-4 text-right font-semibold">
                                KES {{ number_format($payment->amount ?? 0, 2) }}
                            </td>

                            <td class="py-3 px-4">
                                <span class="text-xs uppercase px-2 py-1 rounded bg-gray-100">
                                    {{ strtoupper($payment->payment_method ?? 'N/A') }}
                                </span>
                            </td>

                            <td class="py-3 px-4">
                                {{ optional($payment->payment_date)->format('d M Y H:i') ?? 'N/A' }}
                            </td>

                            <td class="py-3 px-4">
                                @if($payment->is_verified && $status === 'completed')
                                    <span class="status-badge status-verified">
                                        <i class="fas fa-check-circle mr-1"></i> Verified
                                    </span>
                                @elseif($status === 'completed' && !$payment->is_verified)
                                    <span class="status-badge status-pending">
                                        <i class="fas fa-clock mr-1"></i> Pending
                                    </span>
                                @elseif($status === 'reversed')
                                    <span class="status-badge status-reversed">
                                        <i class="fas fa-undo mr-1"></i> Reversed
                                    </span>
                                @else
                                    <span class="status-badge status-warning">
                                        {{ ucfirst($status ?: 'N/A') }}
                                    </span>
                                @endif
                            </td>

                            <td class="py-3 px-4 text-center">
                                <div class="flex items-center justify-center gap-3">
                                    <a href="{{ route('finance.student-fees.show', $payment) }}"
                                       class="text-primary hover:text-primary-dark"
                                       title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>

                                    <a href="{{ route('finance.student-fees.receipt', $payment) }}"
                                       target="_blank"
                                       class="text-gray-500 hover:text-gray-700"
                                       title="Receipt">
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
                        <td colspan="2" class="py-3 px-4 font-bold">Total</td>
                        <td class="py-3 px-4 text-right font-bold">
                            KES {{ number_format(collect($transactions ?? [])->sum('amount'), 2) }}
                        </td>
                        <td colspan="4"></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        @if(method_exists($transactions, 'links'))
            <div class="mt-4">
                {{ $transactions->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
