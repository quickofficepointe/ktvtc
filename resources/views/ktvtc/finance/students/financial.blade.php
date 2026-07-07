@extends('ktvtc.finance.layouts.app')

@section('title', 'Student Financial Details')
@section('subtitle', 'View complete financial details for student')

@section('breadcrumb')
    <li><span class="mx-2">/</span></li>
    <li>
        <a href="{{ route('finance.students.search') }}" class="hover:text-primary transition whitespace-nowrap">
            Students
        </a>
    </li>
    <li><span class="mx-2">/</span></li>
    <li class="text-primary font-medium whitespace-nowrap">
        {{ $student->full_name ?? 'Student' }}
    </li>
@endsection

@section('header-actions')
    <div class="flex flex-col sm:flex-row gap-2 w-full sm:w-auto">
        <a href="{{ route('finance.student-fees.create', ['student_id' => $student->id]) }}"
           class="inline-flex items-center justify-center gap-2 bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-semibold transition">
            <i class="fas fa-credit-card"></i>
            Record Payment
        </a>

        <a href="{{ route('finance.students.statement', $student) }}"
           target="_blank"
           class="inline-flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-semibold transition">
            <i class="fas fa-file-invoice"></i>
            Statement
        </a>

        <a href="{{ route('finance.students.transactions', $student) }}"
           class="inline-flex items-center justify-center gap-2 bg-gray-700 hover:bg-gray-800 text-white px-4 py-2 rounded-lg font-semibold transition">
            <i class="fas fa-list"></i>
            All Transactions
        </a>
    </div>
@endsection

@section('content')
@php
    $balance = $balance ?? (($totalFees ?? 0) - ($totalPaid ?? 0));
@endphp

<div class="space-y-6">
    <div class="finance-card relative overflow-hidden p-4 sm:p-6">
        <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-primary via-red-400 to-primary"></div>

        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-5">
            <div class="flex items-start sm:items-center gap-4">
                <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-full bg-primary/10 flex items-center justify-center text-xl sm:text-2xl font-bold text-primary flex-shrink-0">
                    {{ strtoupper(substr($student->first_name ?? 'S', 0, 1)) }}{{ strtoupper(substr($student->last_name ?? 'T', 0, 1)) }}
                </div>

                <div class="min-w-0">
                    <h3 class="text-xl font-bold text-gray-800 break-words">
                        {{ $student->full_name ?? 'N/A' }}
                    </h3>

                    <p class="text-sm text-gray-500">
                        Student #: {{ $student->student_number ?? 'N/A' }}
                    </p>

                    <div class="flex flex-wrap gap-2 mt-2">
                        <span class="text-xs px-2 py-1 bg-gray-100 rounded break-all">
                            {{ $student->email ?? 'No email' }}
                        </span>
                        <span class="text-xs px-2 py-1 bg-gray-100 rounded">
                            {{ $student->phone ?? 'No phone' }}
                        </span>
                        <span class="text-xs px-2 py-1 bg-gray-100 rounded">
                            {{ $student->campus->name ?? 'No campus' }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="md:text-right">
                <p class="text-sm text-gray-500">Total Balance</p>
                <p class="text-3xl font-bold {{ $balance > 0 ? 'text-red-600' : 'text-green-600' }}">
                    KES {{ number_format(abs($balance), 2) }}
                </p>
                <p class="text-xs text-gray-500">
                    {{ $balance > 0 ? 'Outstanding' : 'Fully Paid' }}
                </p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
        <div class="finance-card p-4">
            <p class="text-sm text-gray-500">Total Fees</p>
            <p class="text-2xl font-bold text-gray-800">
                KES {{ number_format($totalFees ?? 0, 2) }}
            </p>
        </div>

        <div class="finance-card p-4 bg-green-50 border-green-200">
            <p class="text-sm text-gray-500">Total Paid</p>
            <p class="text-2xl font-bold text-green-600">
                KES {{ number_format($totalPaid ?? 0, 2) }}
            </p>
        </div>

        <div class="finance-card p-4 {{ $balance > 0 ? 'bg-red-50 border-red-200' : 'bg-green-50 border-green-200' }}">
            <p class="text-sm text-gray-500">Balance</p>
            <p class="text-2xl font-bold {{ $balance > 0 ? 'text-red-600' : 'text-green-600' }}">
                KES {{ number_format(abs($balance), 2) }}
            </p>
        </div>

        <div class="finance-card p-4 bg-blue-50 border-blue-200">
            <p class="text-sm text-gray-500">Enrollments</p>
            <p class="text-2xl font-bold text-blue-600">
                {{ collect($enrollments ?? [])->count() }}
            </p>
        </div>
    </div>

    <div class="finance-card relative overflow-hidden p-4 sm:p-6">
        <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-primary via-red-400 to-primary"></div>

        <h3 class="font-bold text-gray-800 text-lg mb-4 flex items-center">
            <i class="fas fa-book-open text-primary mr-2"></i>
            Enrollments
        </h3>

        <div class="table-responsive">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-gray-500 border-b">
                        <th class="py-3 px-3 font-semibold">Course</th>
                        <th class="py-3 px-3 font-semibold text-right">Total Fees</th>
                        <th class="py-3 px-3 font-semibold text-right">Amount Paid</th>
                        <th class="py-3 px-3 font-semibold text-right">Balance</th>
                        <th class="py-3 px-3 font-semibold">Status</th>
                        <th class="py-3 px-3 font-semibold">Actions</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($enrollments ?? [] as $enrollment)
                        @php
                            $enrollmentStatus = strtolower($enrollment->status ?? '');

                            $enrollmentBadge = match($enrollmentStatus) {
                                'active' => 'status-active',
                                'completed', 'graduated' => 'status-success',
                                'inactive', 'dropped' => 'status-inactive',
                                default => 'status-warning',
                            };
                        @endphp

                        <tr class="border-b border-gray-100 hover:bg-gray-50">
                            <td class="py-3 px-3">
                                <span class="font-medium">{{ $enrollment->course->name ?? 'N/A' }}</span>
                                <span class="text-xs text-gray-500 block">{{ $enrollment->course->code ?? '' }}</span>
                            </td>

                            <td class="py-3 px-3 text-right font-medium">
                                KES {{ number_format($enrollment->total_fees ?? 0, 2) }}
                            </td>

                            <td class="py-3 px-3 text-right text-green-600 font-medium">
                                KES {{ number_format($enrollment->amount_paid ?? 0, 2) }}
                            </td>

                            <td class="py-3 px-3 text-right font-bold {{ ($enrollment->balance ?? 0) > 0 ? 'text-red-600' : 'text-green-600' }}">
                                KES {{ number_format(abs($enrollment->balance ?? 0), 2) }}
                            </td>

                            <td class="py-3 px-3">
                                <span class="status-badge {{ $enrollmentBadge }}">
                                    {{ ucfirst($enrollmentStatus ?: 'N/A') }}
                                </span>
                            </td>

                            <td class="py-3 px-3">
                                <a href="{{ route('finance.student-fees.create', ['enrollment_id' => $enrollment->id]) }}"
                                   class="text-primary hover:text-primary-dark text-sm font-medium">
                                    <i class="fas fa-credit-card mr-1"></i>
                                    Pay
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-gray-500">
                                No enrollments found
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="finance-card relative overflow-hidden p-4 sm:p-6">
        <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-primary via-red-400 to-primary"></div>

        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
            <h3 class="font-bold text-gray-800 text-lg flex items-center">
                <i class="fas fa-receipt text-primary mr-2"></i>
                Recent Payments
            </h3>

            <a href="{{ route('finance.students.transactions', $student) }}"
               class="text-sm text-primary hover:underline font-medium">
                View All →
            </a>
        </div>

        <div class="table-responsive">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-gray-500 border-b">
                        <th class="py-3 px-3 font-semibold">Receipt</th>
                        <th class="py-3 px-3 font-semibold text-right">Amount</th>
                        <th class="py-3 px-3 font-semibold">Method</th>
                        <th class="py-3 px-3 font-semibold">Date</th>
                        <th class="py-3 px-3 font-semibold">Status</th>
                        <th class="py-3 px-3 font-semibold">Actions</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse(collect($payments ?? [])->take(10) as $payment)
                        @php
                            $paymentStatus = strtolower($payment->status ?? '');
                        @endphp

                        <tr class="border-b border-gray-100 hover:bg-gray-50">
                            <td class="py-3 px-3 text-primary font-medium">
                                {{ $payment->receipt_number ?? 'N/A' }}
                            </td>

                            <td class="py-3 px-3 text-right font-semibold">
                                KES {{ number_format($payment->amount ?? 0, 2) }}
                            </td>

                            <td class="py-3 px-3">
                                <span class="text-xs uppercase px-2 py-1 rounded bg-gray-100">
                                    {{ strtoupper($payment->payment_method ?? 'N/A') }}
                                </span>
                            </td>

                            <td class="py-3 px-3">
                                {{ optional($payment->payment_date)->format('d M Y') ?? 'N/A' }}
                            </td>

                            <td class="py-3 px-3">
                                @if($paymentStatus === 'reversed')
                                    <span class="status-badge status-reversed">
                                        <i class="fas fa-undo mr-1"></i>
                                        Reversed
                                    </span>
                                @elseif($payment->is_verified)
                                    <span class="status-badge status-verified">
                                        <i class="fas fa-check-circle mr-1"></i>
                                        Verified
                                    </span>
                                @else
                                    <span class="status-badge status-pending">
                                        <i class="fas fa-clock mr-1"></i>
                                        Pending
                                    </span>
                                @endif
                            </td>

                            <td class="py-3 px-3">
                                <a href="{{ route('finance.student-fees.show', $payment) }}"
                                   class="text-primary hover:text-primary-dark"
                                   title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-gray-500">
                                No payments recorded
                            </td>
                        </tr>
                    @endforelse
                </tbody>

                <tfoot class="border-t-2 border-gray-300">
                    <tr>
                        <td class="py-3 px-3 font-bold">Total</td>
                        <td class="py-3 px-3 text-right font-bold">
                            KES {{ number_format(collect($payments ?? [])->sum('amount'), 2) }}
                        </td>
                        <td colspan="4"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@endsection
