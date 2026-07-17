@extends('ktvtc.finance.layouts.app')

@section('title', 'Student Fee Payments')
@section('subtitle', 'Manage and track all student fee payments')

@section('breadcrumb')
<li>
    <span class="mx-2">/</span>
    <span class="text-primary font-medium">Student Fees</span>
</li>
@endsection

@section('header-actions')
<div class="flex flex-wrap justify-end gap-2">
    <a href="{{ route('finance.student-fees.export') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg font-semibold flex items-center text-sm transition">
        <i class="fas fa-file-export mr-2"></i> Export
    </a>
    <a href="{{ route('finance.student-fees.create') }}" class="bg-primary hover:bg-primary-dark text-white px-4 py-2 rounded-lg font-semibold flex items-center text-sm transition">
        <i class="fas fa-plus-circle mr-2"></i> Record Payment
    </a>
</div>
@endsection

@section('content')
<div class="w-full max-w-full space-y-6 overflow-hidden">

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
        <!-- Total Payments -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 transition-all duration-300 hover:-translate-y-1 hover:shadow-lg hover:shadow-red-100/50 group">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Total Payments</p>
                    <h3 class="text-2xl font-bold text-gray-800 mt-1">{{ number_format($totalPayments ?? 0) }}</h3>
                    <p class="text-xs text-gray-500 mt-1">All payments</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-red-100 flex items-center justify-center text-red-600 group-hover:scale-110 transition-transform duration-300">
                    <i class="fas fa-credit-card text-xl"></i>
                </div>
            </div>
        </div>

        <!-- Total Amount -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 transition-all duration-300 hover:-translate-y-1 hover:shadow-lg hover:shadow-green-100/50 group">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Total Amount</p>
                    <h3 class="text-2xl font-bold text-green-600 mt-1">KES {{ number_format($totalAmount ?? 0, 2) }}</h3>
                    <p class="text-xs text-gray-500 mt-1">Total collected</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-green-100 flex items-center justify-center text-green-600 group-hover:scale-110 transition-transform duration-300">
                    <i class="fas fa-coins text-xl"></i>
                </div>
            </div>
        </div>

        <!-- Today's Collection -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 transition-all duration-300 hover:-translate-y-1 hover:shadow-lg hover:shadow-blue-100/50 group">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Today's Collection</p>
                    <h3 class="text-2xl font-bold text-blue-600 mt-1">KES {{ number_format($todayAmount ?? 0, 2) }}</h3>
                    <p class="text-xs text-gray-500 mt-1">Today's receipts</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-blue-100 flex items-center justify-center text-blue-600 group-hover:scale-110 transition-transform duration-300">
                    <i class="fas fa-calendar-day text-xl"></i>
                </div>
            </div>
        </div>

        <!-- Pending Verification -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 transition-all duration-300 hover:-translate-y-1 hover:shadow-lg hover:shadow-yellow-100/50 group">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Pending Verification</p>
                    <h3 class="text-2xl font-bold text-yellow-600 mt-1">{{ number_format($pendingVerification ?? 0) }}</h3>
                    <p class="text-xs text-gray-500 mt-1">Awaiting verification</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-yellow-100 flex items-center justify-center text-yellow-600 group-hover:scale-110 transition-transform duration-300">
                    <i class="fas fa-clock text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Modern Filters -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 relative overflow-hidden">
        <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-primary via-red-400 to-primary"></div>
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-bold text-gray-800 flex items-center">
                <i class="fas fa-sliders-h text-primary mr-2"></i>
                Filter Payments
            </h3>
            <span class="text-xs text-gray-400">Apply filters to narrow results</span>
        </div>

        <form method="GET" action="{{ route('finance.student-fees.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
            <div>
                <label class="text-xs font-semibold text-gray-600 block mb-1.5">Date From</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}" class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition duration-200 text-sm">
            </div>
            <div>
                <label class="text-xs font-semibold text-gray-600 block mb-1.5">Date To</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}" class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition duration-200 text-sm">
            </div>
            <div>
                <label class="text-xs font-semibold text-gray-600 block mb-1.5">Payment Method</label>
                <select name="payment_method" class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition duration-200 text-sm">
                    <option value="">All Methods</option>
                    <option value="cash" {{ request('payment_method') == 'cash' ? 'selected' : '' }}>Cash</option>
                    <option value="mpesa" {{ request('payment_method') == 'mpesa' ? 'selected' : '' }}>M-Pesa</option>
                    <option value="bank" {{ request('payment_method') == 'bank' ? 'selected' : '' }}>Bank</option>
                    <option value="kcb" {{ request('payment_method') == 'kcb' ? 'selected' : '' }}>KCB</option>
                    <option value="other" {{ request('payment_method') == 'other' ? 'selected' : '' }}>Other</option>
                </select>
            </div>
            <div>
                <label class="text-xs font-semibold text-gray-600 block mb-1.5">Status</label>
                <select name="status" class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition duration-200 text-sm">
                    <option value="">All Status</option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Failed</option>
                    <option value="reversed" {{ request('status') == 'reversed' ? 'selected' : '' }}>Reversed</option>
                </select>
            </div>
            <div>
                <label class="text-xs font-semibold text-gray-600 block mb-1.5">Search</label>
                <div class="flex">
                    <input type="text" name="search" placeholder="Receipt, Student..." value="{{ request('search') }}" class="flex-1 px-3 py-2.5 border border-gray-300 rounded-l-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition duration-200 text-sm">
                    <button type="submit" class="bg-primary hover:bg-primary-dark text-white px-5 py-2.5 rounded-r-lg transition-colors duration-200 flex items-center gap-2">
                        <i class="fas fa-search text-sm"></i>
                    </button>
                </div>
            </div>
        </form>

        <div class="mt-4 flex items-center justify-between border-t border-gray-100 pt-4">
            <div class="flex items-center gap-4 text-sm text-gray-500">
                @if(request('date_from') || request('date_to') || request('payment_method') || request('search'))
                    <span class="flex items-center">
                        <i class="fas fa-filter text-primary mr-1"></i>
                        Filters active
                    </span>
                @endif
            </div>
            <a href="{{ route('finance.student-fees.index') }}" class="text-sm text-primary hover:text-primary-dark hover:underline font-medium flex items-center gap-1">
                <i class="fas fa-times"></i> Clear Filters
            </a>
        </div>
    </div>

    <!-- Payments Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 relative overflow-hidden">
        <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-primary via-red-400 to-primary"></div>

        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="font-bold text-gray-800 flex items-center">
                    <i class="fas fa-list text-primary mr-2"></i>
                    Payment Records
                </h3>
                <p class="text-xs text-gray-500 mt-1">
                    Showing {{ $payments->count() ?? 0 }} of {{ $payments->total() ?? 0 }} payments
                </p>
            </div>
            <div class="flex items-center gap-3">
                <span class="text-sm text-gray-500">{{ $payments->total() ?? 0 }} records</span>
                <a href="{{ route('finance.student-fees.create') }}" class="bg-primary hover:bg-primary-dark text-white px-4 py-2 rounded-lg text-sm font-semibold flex items-center gap-2 shadow-md hover:shadow-lg transition-all duration-200">
                    <i class="fas fa-plus"></i> Record Payment
                </a>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-200">
                        <th class="py-3 px-3 text-left font-semibold text-gray-600">
                            <div class="flex items-center gap-2">
                                <i class="fas fa-user text-primary text-xs"></i>
                                Student
                            </div>
                        </th>
                        <th class="py-3 px-3 text-left font-semibold text-gray-600">
                            <div class="flex items-center gap-2">
                                <i class="fas fa-receipt text-primary text-xs"></i>
                                Receipt
                            </div>
                        </th>
                        <th class="py-3 px-3 text-right font-semibold text-gray-600">
                            <div class="flex items-center justify-end gap-2">
                                <i class="fas fa-money-bill-wave text-primary text-xs"></i>
                                Amount
                            </div>
                        </th>
                        <th class="py-3 px-3 text-left font-semibold text-gray-600">
                            <div class="flex items-center gap-2">
                                <i class="fas fa-credit-card text-primary text-xs"></i>
                                Method
                            </div>
                        </th>
                        <th class="py-3 px-3 text-center font-semibold text-gray-600">
                            <div class="flex items-center justify-center gap-2">
                                <i class="fas fa-info-circle text-primary text-xs"></i>
                                Status
                            </div>
                        </th>
                        <th class="py-3 px-3 text-center font-semibold text-gray-600">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payments ?? [] as $payment)
                        <tr class="border-b border-gray-100 hover:bg-gray-50/50 transition-colors duration-150 group">
                            <td class="py-3 px-3">
                                <div class="flex items-center gap-2">
                                    <div class="w-8 h-8 rounded-full bg-primary/10 flex items-center justify-center text-primary font-semibold text-xs">
                                        {{ $payment->student && $payment->student->full_name ? substr($payment->student->full_name, 0, 2) : 'NA' }}
                                    </div>
                                    <div>
                                        <span class="font-medium text-gray-800">{{ $payment->student->full_name ?? 'N/A' }}</span>
                                        <span class="text-xs text-gray-400 block">{{ $payment->student->student_number ?? '' }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3 px-3">
                                <div>
                                    <span class="font-medium text-primary">{{ $payment->receipt_number ?? 'N/A' }}</span>
                                    <span class="text-xs text-gray-400 block">{{ optional($payment->payment_date)->format('d M Y') }}</span>
                                </div>
                            </td>
                            <td class="py-3 px-3 text-right font-semibold text-gray-800">
                                KES {{ number_format($payment->amount ?? 0, 2) }}
                            </td>
                            <td class="py-3 px-3">
                                <span class="text-xs uppercase px-2 py-1 rounded-full bg-gray-100 text-gray-700">
                                    {{ $payment->payment_method ?? 'N/A' }}
                                </span>
                            </td>
                            <td class="py-3 px-3 text-center">
                                @if(($payment->is_verified ?? false) && ($payment->status ?? '') === 'completed')
                                    <span class="status-badge status-verified"><i class="fas fa-check-circle mr-1"></i> Verified</span>
                                @elseif(($payment->status ?? '') === 'completed' && !($payment->is_verified ?? false))
                                    <span class="status-badge status-pending"><i class="fas fa-clock mr-1"></i> Pending</span>
                                @elseif(($payment->status ?? '') === 'reversed')
                                    <span class="status-badge status-reversed"><i class="fas fa-undo mr-1"></i> Reversed</span>
                                @elseif(($payment->status ?? '') === 'failed')
                                    <span class="status-badge status-failed"><i class="fas fa-times-circle mr-1"></i> Failed</span>
                                @else
                                    <span class="status-badge status-pending">{{ ucfirst($payment->status ?? 'N/A') }}</span>
                                @endif
                            </td>
                            <td class="py-3 px-3 text-center">
                                <div class="flex items-center justify-center gap-3">
                                    <a href="{{ route('finance.student-fees.show', $payment) }}" class="text-primary hover:text-primary-dark transition-colors" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('finance.student-fees.receipt', $payment) }}" target="_blank" class="text-gray-500 hover:text-gray-700 transition-colors" title="Receipt">
                                        <i class="fas fa-print"></i>
                                    </a>
                                    @if(($payment->status ?? '') === 'completed' && !($payment->is_verified ?? false))
                                        <button onclick="verifyPayment({{ $payment->id }})" class="text-green-600 hover:text-green-800 transition-colors" title="Verify">
                                            <i class="fas fa-check-circle"></i>
                                        </button>
                                    @endif
                                    @if(($payment->status ?? '') === 'completed')
                                        <button onclick="reversePayment({{ $payment->id }})" class="text-red-600 hover:text-red-800 transition-colors" title="Reverse">
                                            <i class="fas fa-undo"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-gray-500">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="w-16 h-16 rounded-full bg-gray-100 flex items-center justify-center mb-3">
                                        <i class="fas fa-credit-card text-3xl text-gray-300"></i>
                                    </div>
                                    <p class="font-medium text-gray-600">No payments found</p>
                                    <p class="text-sm text-gray-400 mt-1">Try adjusting your filters or record a new payment</p>
                                    <a href="{{ route('finance.student-fees.create') }}" class="mt-4 bg-primary hover:bg-primary-dark text-white px-4 py-2 rounded-lg text-sm font-semibold flex items-center gap-2 transition-colors duration-200">
                                        <i class="fas fa-plus"></i> Record Payment
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>

                @if($payments && $payments->count() > 0)
                <tfoot class="border-t-2 border-gray-300 bg-gray-50">
                    <tr>
                        <td colspan="2" class="py-3 px-3 font-bold text-gray-800">Total</td>
                        <td class="py-3 px-3 text-right font-bold text-gray-800">
                            KES {{ number_format($payments->sum('amount'), 2) }}
                        </td>
                        <td colspan="3" class="py-3 px-3"></td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>

        @if(isset($payments) && method_exists($payments, 'links'))
            <div class="mt-4 border-t border-gray-100 pt-4">
                {{ $payments->links() }}
            </div>
        @endif
    </div>

    <!-- Quick Stats Row -->
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 text-center hover:shadow-md transition-shadow duration-200">
            <p class="text-xs text-gray-500">Today</p>
            <p class="text-lg font-bold text-gray-800">{{ number_format($todayPaymentsCount ?? 0) }}</p>
            <p class="text-xs text-gray-400">transactions</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 text-center hover:shadow-md transition-shadow duration-200">
            <p class="text-xs text-gray-500">This Week</p>
            <p class="text-lg font-bold text-gray-800">{{ number_format($weeklyPayments ?? 0) }}</p>
            <p class="text-xs text-gray-400">payments</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 text-center hover:shadow-md transition-shadow duration-200">
            <p class="text-xs text-gray-500">This Month</p>
            <p class="text-lg font-bold text-gray-800">KES {{ number_format($monthlyCollection ?? 0, 2) }}</p>
            <p class="text-xs text-gray-400">collected</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 text-center hover:shadow-md transition-shadow duration-200">
            <p class="text-xs text-gray-500">Average</p>
            <p class="text-lg font-bold text-gray-800">KES {{ number_format($averagePayment ?? 0, 2) }}</p>
            <p class="text-xs text-gray-400">per payment</p>
        </div>
    </div>
</div>

<!-- Verify Modal -->
@include('ktvtc.finance.transactions.verify-modal')

<!-- Reverse Modal -->
@include('ktvtc.finance.transactions.reverse-modal')

@endsection

@push('scripts')
<script>
    function verifyPayment(id) {
        openVerifyModal(id);
    }

    function reversePayment(id) {
        openReverseModal(id);
    }
</script>
@endpush
