@extends('ktvtc.finance.layouts.app')

@section('title', 'Student Financial Details')
@section('subtitle', 'View complete financial details for student')

@section('breadcrumb')
<li>
    <span class="mx-2">/</span>
    <a href="{{ route('finance.students.search') }}" class="text-gray-600 hover:text-primary">Students</a>
</li>
<li>
    <span class="mx-2">/</span>
    <span class="text-gray-400">{{ $student->full_name ?? 'Student' }}</span>
</li>
@endsection

@section('header-actions')
<div class="flex space-x-3">
    <a href="{{ route('finance.student-fees.create', ['student_id' => $student->id]) }}" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-semibold flex items-center transition">
        <i class="fas fa-credit-card mr-2"></i> Record Payment
    </a>
    <a href="{{ route('finance.students.statement', $student) }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-semibold flex items-center transition" target="_blank">
        <i class="fas fa-file-invoice mr-2"></i> Statement
    </a>
    <a href="{{ route('finance.students.transactions', $student) }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg font-semibold flex items-center transition">
        <i class="fas fa-list mr-2"></i> All Transactions
    </a>
</div>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Student Info -->
    <div class="finance-card p-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between">
            <div class="flex items-center">
                <div class="w-16 h-16 rounded-full bg-primary/10 flex items-center justify-center text-2xl font-bold text-primary">
                    {{ strtoupper(substr($student->first_name ?? 'S', 0, 1)) }}{{ strtoupper(substr($student->last_name ?? 'T', 0, 1)) }}
                </div>
                <div class="ml-4">
                    <h3 class="text-xl font-bold text-gray-800">{{ $student->full_name ?? 'N/A' }}</h3>
                    <p class="text-sm text-gray-500">Student #: {{ $student->student_number ?? 'N/A' }}</p>
                    <div class="flex flex-wrap gap-2 mt-1">
                        <span class="text-xs px-2 py-1 bg-gray-100 rounded">{{ $student->email ?? 'No email' }}</span>
                        <span class="text-xs px-2 py-1 bg-gray-100 rounded">{{ $student->phone ?? 'No phone' }}</span>
                        <span class="text-xs px-2 py-1 bg-gray-100 rounded">{{ $student->campus->name ?? 'No campus' }}</span>
                    </div>
                </div>
            </div>
            <div class="mt-4 md:mt-0 text-right">
                <p class="text-sm text-gray-500">Total Balance</p>
                <p class="text-3xl font-bold {{ $balance > 0 ? 'text-red-600' : 'text-green-600' }}">
                    {{ $balance > 0 ? 'KES ' : 'KES ' }}{{ number_format(abs($balance), 2) }}
                </p>
                <p class="text-xs text-gray-500">{{ $balance > 0 ? 'Outstanding' : 'Fully Paid' }}</p>
            </div>
        </div>
    </div>

    <!-- Financial Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="finance-card p-4">
            <p class="text-sm text-gray-500">Total Fees</p>
            <p class="text-2xl font-bold text-gray-800">KES {{ number_format($totalFees ?? 0, 2) }}</p>
        </div>
        <div class="finance-card p-4 bg-green-50 border-green-200">
            <p class="text-sm text-gray-500">Total Paid</p>
            <p class="text-2xl font-bold text-green-600">KES {{ number_format($totalPaid ?? 0, 2) }}</p>
        </div>
        <div class="finance-card p-4 {{ $balance > 0 ? 'bg-red-50 border-red-200' : 'bg-green-50 border-green-200' }}">
            <p class="text-sm text-gray-500">Balance</p>
            <p class="text-2xl font-bold {{ $balance > 0 ? 'text-red-600' : 'text-green-600' }}">
                KES {{ number_format(abs($balance), 2) }}
            </p>
        </div>
        <div class="finance-card p-4 bg-blue-50 border-blue-200">
            <p class="text-sm text-gray-500">Enrollments</p>
            <p class="text-2xl font-bold text-blue-600">{{ $enrollments->count() }}</p>
        </div>
    </div>

    <!-- Enrollments -->
    <div class="finance-card p-6">
        <h3 class="font-bold text-gray-800 text-lg mb-4">Enrollments</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-gray-500 border-b">
                        <th class="pb-2 font-semibold">Course</th>
                        <th class="pb-2 font-semibold text-right">Total Fees</th>
                        <th class="pb-2 font-semibold text-right">Amount Paid</th>
                        <th class="pb-2 font-semibold text-right">Balance</th>
                        <th class="pb-2 font-semibold">Status</th>
                        <th class="pb-2 font-semibold">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($enrollments ?? [] as $enrollment)
                        <tr class="border-b border-gray-100 hover:bg-gray-50">
                            <td class="py-2">
                                <span class="font-medium">{{ $enrollment->course->name ?? 'N/A' }}</span>
                                <span class="text-xs text-gray-500 block">{{ $enrollment->course->code ?? '' }}</span>
                            </td>
                            <td class="py-2 text-right font-medium">KES {{ number_format($enrollment->total_fees ?? 0, 2) }}</td>
                            <td class="py-2 text-right text-green-600 font-medium">KES {{ number_format($enrollment->amount_paid ?? 0, 2) }}</td>
                            <td class="py-2 text-right font-bold {{ ($enrollment->balance ?? 0) > 0 ? 'text-red-600' : 'text-green-600' }}">
                                KES {{ number_format($enrollment->balance ?? 0, 2) }}
                            </td>
                            <td class="py-2">
                                <span class="status-badge status-{{ $enrollment->status }}">{{ ucfirst($enrollment->status ?? 'N/A') }}</span>
                            </td>
                            <td class="py-2">
                                <a href="{{ route('finance.student-fees.create', ['enrollment_id' => $enrollment->id]) }}" class="text-primary hover:text-primary-dark text-sm">
                                    <i class="fas fa-credit-card mr-1"></i> Pay
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-4 text-center text-gray-500">No enrollments found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Recent Payments -->
    <div class="finance-card p-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="font-bold text-gray-800 text-lg">Recent Payments</h3>
            <a href="{{ route('finance.students.transactions', $student) }}" class="text-sm text-primary hover:underline">View All →</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-gray-500 border-b">
                        <th class="pb-2 font-semibold">Receipt</th>
                        <th class="pb-2 font-semibold text-right">Amount</th>
                        <th class="pb-2 font-semibold">Method</th>
                        <th class="pb-2 font-semibold">Date</th>
                        <th class="pb-2 font-semibold">Status</th>
                        <th class="pb-2 font-semibold">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payments->take(10) ?? [] as $payment)
                        <tr class="border-b border-gray-100 hover:bg-gray-50">
                            <td class="py-2 text-primary font-medium">{{ $payment->receipt_number }}</td>
                            <td class="py-2 text-right font-semibold">KES {{ number_format($payment->amount, 2) }}</td>
                            <td class="py-2">
                                <span class="text-xs uppercase px-2 py-1 rounded bg-gray-100">{{ $payment->payment_method }}</span>
                            </td>
                            <td class="py-2 text-sm">{{ $payment->payment_date->format('d M Y') }}</td>
                            <td class="py-2">
                                @if($payment->is_verified)
                                    <span class="status-badge status-verified"><i class="fas fa-check-circle mr-1"></i> Verified</span>
                                @else
                                    <span class="status-badge status-pending"><i class="fas fa-clock mr-1"></i> Pending</span>
                                @endif
                            </td>
                            <td class="py-2">
                                <a href="{{ route('finance.student-fees.show', $payment) }}" class="text-primary hover:text-primary-dark">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-4 text-center text-gray-500">No payments recorded</td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot class="border-t-2 border-gray-300">
                    <tr>
                        <td colspan="1" class="py-2 font-bold">Total</td>
                        <td class="py-2 text-right font-bold">KES {{ number_format($payments->sum('amount') ?? 0, 2) }}</td>
                        <td colspan="4" class="py-2"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@endpush
