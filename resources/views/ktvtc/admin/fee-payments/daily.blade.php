@extends('ktvtc.admin.layout.adminlayout')

@section('title', 'Daily Payment Report')
@section('subtitle', 'Payment summary for ' . Carbon\Carbon::parse($date)->format('F j, Y'))

@section('header-actions')
<div class="flex space-x-2">
    <a href="{{ route('admin.tvet.fee-payments.reports.daily', ['date' => Carbon\Carbon::parse($date)->subDay()->format('Y-m-d')]) }}"
       class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg font-medium transition-colors flex items-center space-x-2">
        <i class="fas fa-chevron-left"></i>
        <span>Previous Day</span>
    </a>
    <a href="{{ route('admin.tvet.fee-payments.reports.daily', ['date' => Carbon\Carbon::parse($date)->addDay()->format('Y-m-d')]) }}"
       class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg font-medium transition-colors flex items-center space-x-2">
        <span>Next Day</span>
        <i class="fas fa-chevron-right"></i>
    </a>
    <a href="{{ route('admin.tvet.fee-payments.reports.daily', ['date' => $date, 'export' => true]) }}"
       class="px-4 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg font-medium transition-colors flex items-center space-x-2">
        <i class="fas fa-download"></i>
        <span>Export CSV</span>
    </a>
</div>
@endsection

@section('content')
<!-- Date Selector -->
<div class="bg-white rounded-xl border border-gray-200 p-6 mb-6">
    <form method="GET" action="{{ route('admin.tvet.fee-payments.reports.daily') }}" class="flex items-end space-x-4">
        <div class="flex-1">
            <label class="block text-sm font-medium text-gray-700 mb-2">Select Date</label>
            <input type="date" name="date" value="{{ $date }}"
                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
        </div>
        <button type="submit" class="px-6 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg transition-colors">
            View Report
        </button>
    </form>
</div>

<!-- Summary Cards -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
    <div class="bg-white rounded-xl border border-gray-200 p-6 card-hover">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">Total Payments</p>
                <p class="text-2xl font-bold text-gray-800 mt-2">{{ $summary['total_payments'] }}</p>
            </div>
            <div class="w-12 h-12 rounded-lg bg-blue-50 flex items-center justify-center">
                <i class="fas fa-money-check-alt text-blue-600 text-xl"></i>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6 card-hover">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">Completed Payments</p>
                <p class="text-2xl font-bold text-green-600 mt-2">{{ $summary['by_status']['completed']['count'] ?? 0 }}</p>
            </div>
            <div class="w-12 h-12 rounded-lg bg-green-50 flex items-center justify-center">
                <i class="fas fa-check-circle text-green-600 text-xl"></i>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6 card-hover">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">Total Amount</p>
                <p class="text-2xl font-bold text-gray-800 mt-2">KES {{ number_format($summary['total_amount'], 2) }}</p>
            </div>
            <div class="w-12 h-12 rounded-lg bg-purple-50 flex items-center justify-center">
                <i class="fas fa-coins text-purple-600 text-xl"></i>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6 card-hover">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">Average per Transaction</p>
                <p class="text-2xl font-bold text-amber-600 mt-2">
                    KES {{ number_format($summary['total_amount'] / max($summary['by_status']['completed']['count'] ?? 1, 1), 2) }}
                </p>
            </div>
            <div class="w-12 h-12 rounded-lg bg-amber-50 flex items-center justify-center">
                <i class="fas fa-chart-line text-amber-600 text-xl"></i>
            </div>
        </div>
    </div>
</div>

<!-- Payment Methods Breakdown -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Payment Methods</h3>
        <div class="space-y-4">
            @foreach($summary['by_method'] as $method => $data)
            <div>
                <div class="flex items-center justify-between mb-1">
                    <span class="text-sm font-medium text-gray-700">
                        @switch($method)
                            @case('kcb_stk_push') KCB STK Push @break
                            @case('paybill') Paybill @break
                            @case('bank_deposit') Bank Deposit @break
                            @case('cash') Cash @break
                            @case('helb') HELB @break
                            @case('sponsor') Sponsor @break
                            @default {{ ucfirst($method) }}
                        @endswitch
                    </span>
                    <span class="text-sm text-gray-600">KES {{ number_format($data['total'], 2) }}</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-primary rounded-full h-2" style="width: {{ ($data['total'] / $summary['total_amount']) * 100 }}%"></div>
                </div>
                <div class="flex justify-between mt-1 text-xs text-gray-500">
                    <span>{{ $data['count'] }} transactions</span>
                    <span>{{ number_format(($data['total'] / $summary['total_amount']) * 100, 1) }}%</span>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Payment Status</h3>
        <div class="space-y-4">
            @foreach($summary['by_status'] as $status => $data)
            <div>
                <div class="flex items-center justify-between mb-1">
                    <span class="text-sm font-medium text-gray-700">
                        @switch($status)
                            @case('completed') Completed @break
                            @case('pending') Pending @break
                            @case('failed') Failed @break
                            @case('reversed') Reversed @break
                            @case('disputed') Disputed @break
                            @default {{ ucfirst($status) }}
                        @endswitch
                    </span>
                    <span class="text-sm text-gray-600">{{ $data['count'] }} payments</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    @php
                        $color = match($status) {
                            'completed' => 'bg-green-500',
                            'pending' => 'bg-yellow-500',
                            'failed' => 'bg-red-500',
                            'reversed' => 'bg-orange-500',
                            default => 'bg-gray-500'
                        };
                    @endphp
                    <div class="{{ $color }} rounded-full h-2" style="width: {{ ($data['count'] / $summary['total_payments']) * 100 }}%"></div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>

<!-- Payments Table -->
<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200">
        <h3 class="text-lg font-semibold text-gray-800">Payment Transactions</h3>
        <p class="text-sm text-gray-600 mt-1">All payments recorded on {{ Carbon\Carbon::parse($date)->format('F j, Y') }}</p>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-200">
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Time</th>
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Receipt No.</th>
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student</th>
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Course</th>
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Method</th>
                    <th class="py-3 px-6 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                    <th class="py-3 px-6 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($payments as $payment)
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="py-3 px-6">
                        <span class="text-sm text-gray-900">{{ $payment->payment_time }}</span>
                    </td>
                    <td class="py-3 px-6">
                        <div>
                            <span class="text-sm font-medium text-gray-900">{{ $payment->receipt_number }}</span>
                            <span class="text-xs text-gray-500 block">{{ $payment->transaction_id }}</span>
                        </div>
                    </td>
                    <td class="py-3 px-6">
                        <span class="text-sm text-gray-900">{{ $payment->student->full_name ?? 'N/A' }}</span>
                        <span class="text-xs text-gray-500 block">{{ $payment->student->student_number ?? '' }}</span>
                    </td>
                    <td class="py-3 px-6">
                        <span class="text-sm text-gray-900">{{ $payment->enrollment->course->name ?? 'N/A' }}</span>
                    </td>
                    <td class="py-3 px-6">
                        <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium
                            {{ $payment->payment_method == 'kcb_stk_push' ? 'bg-purple-100 text-purple-800' :
                               ($payment->payment_method == 'paybill' ? 'bg-blue-100 text-blue-800' :
                               ($payment->payment_method == 'bank_deposit' ? 'bg-green-100 text-green-800' :
                               ($payment->payment_method == 'cash' ? 'bg-yellow-100 text-yellow-800' :
                               'bg-gray-100 text-gray-800'))) }}">
                            {{ $payment->payment_method_label }}
                        </span>
                    </td>
                    <td class="py-3 px-6 text-right">
                        <span class="text-sm font-bold text-gray-900">KES {{ number_format($payment->amount, 2) }}</span>
                    </td>
                    <td class="py-3 px-6 text-center">
                        @if($payment->status == 'completed')
                            @if($payment->is_verified)
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                <i class="fas fa-check-circle mr-1"></i> Verified
                            </span>
                            @else
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                <i class="fas fa-clock mr-1"></i> Pending
                            </span>
                            @endif
                        @else
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                {{ $payment->status == 'failed' ? 'bg-red-100 text-red-800' :
                                   ($payment->status == 'reversed' ? 'bg-orange-100 text-orange-800' :
                                   'bg-gray-100 text-gray-800') }}">
                                {{ ucfirst($payment->status) }}
                            </span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="py-8 px-6 text-center">
                        <div class="flex flex-col items-center justify-center">
                            <i class="fas fa-money-check-alt text-gray-300 text-4xl mb-4"></i>
                            <p class="text-gray-500 text-lg font-medium">No payments found for this date</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
            <tfoot class="bg-gray-50 font-medium">
                <tr>
                    <td colspan="5" class="py-3 px-6 text-right">Total:</td>
                    <td class="py-3 px-6 text-right text-primary font-bold">KES {{ number_format($summary['total_amount'], 2) }}</td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
@endsection
