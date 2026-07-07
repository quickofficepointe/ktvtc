@extends('ktvtc.finance.layouts.app')

@section('title', 'Dashboard')
@section('subtitle', 'Finance Overview & Analytics')

@section('breadcrumb')
<li>
    <div class="flex items-center">
        <i class="fas fa-angle-right text-gray-400"></i>
        <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">Dashboard</span>
    </div>
</li>
@endsection

@section('content')
<div class="space-y-6">
    <!-- ============================================================ -->
    <!-- ROW 1: GRAND TOTALS -->
    <!-- ============================================================ -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Total Collected -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 transition-all duration-300 hover:-translate-y-1 hover:shadow-md hover:shadow-red-100/50">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Total Collected</p>
                    <h3 class="text-2xl font-bold text-gray-800 mt-1">KES {{ number_format($totalCollected ?? 0, 2) }}</h3>
                    <p class="text-xs text-gray-500 mt-1">All time collections</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-red-100 flex items-center justify-center text-red-600">
                    <i class="fas fa-coins text-xl"></i>
                </div>
            </div>
            <div class="mt-3">
                <div class="h-1.5 rounded-full bg-gray-200 overflow-hidden">
                    <div class="h-full rounded-full bg-red-500 transition-all duration-1000" style="width: {{ $collectionRate ?? 0 }}%"></div>
                </div>
                <p class="text-xs text-gray-500 mt-1">Collection Rate: {{ $collectionRate ?? 0 }}%</p>
            </div>
        </div>

        <!-- Today's Collection -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 transition-all duration-300 hover:-translate-y-1 hover:shadow-md hover:shadow-blue-100/50">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Today's Collection</p>
                    <h3 class="text-2xl font-bold text-blue-600 mt-1">KES {{ number_format($todayCollection ?? 0, 2) }}</h3>
                    <p class="text-xs text-gray-500 mt-1">{{ $todayPaymentsCount ?? 0 }} transactions today</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-blue-100 flex items-center justify-center text-blue-600">
                    <i class="fas fa-calendar-day text-xl"></i>
                </div>
            </div>
        </div>

        <!-- Monthly Collection -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 transition-all duration-300 hover:-translate-y-1 hover:shadow-md hover:shadow-purple-100/50">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Monthly Collection</p>
                    <h3 class="text-2xl font-bold text-purple-600 mt-1">KES {{ number_format($monthlyCollection ?? 0, 2) }}</h3>
                    <p class="text-xs text-gray-500 mt-1">{{ now()->format('F Y') }}</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-purple-100 flex items-center justify-center text-purple-600">
                    <i class="fas fa-calendar-alt text-xl"></i>
                </div>
            </div>
        </div>

        <!-- Outstanding Balance -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 transition-all duration-300 hover:-translate-y-1 hover:shadow-md hover:shadow-yellow-100/50">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Outstanding Balance</p>
                    <h3 class="text-2xl font-bold text-yellow-600 mt-1">KES {{ number_format($outstandingBalance ?? 0, 2) }}</h3>
                    <p class="text-xs text-gray-500 mt-1">Pending student fees</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-yellow-100 flex items-center justify-center text-yellow-600">
                    <i class="fas fa-exclamation-triangle text-xl"></i>
                </div>
            </div>
            <div class="mt-3">
                <a href="{{ route('finance.student-fees.reports.outstanding') }}" class="text-xs text-primary hover:text-primary-dark hover:underline font-medium">
                    View outstanding students →
                </a>
            </div>
        </div>
    </div>

    <!-- ============================================================ -->
    <!-- ROW 2: PAYMENT SOURCE BREAKDOWN -->
    <!-- ============================================================ -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
        <!-- School Fees -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-lg bg-red-100 flex items-center justify-center text-red-600">
                    <i class="fas fa-user-graduate"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-xs font-medium text-gray-500 truncate">School Fees</p>
                    <p class="text-sm font-bold text-gray-800">KES {{ number_format($schoolFeesTotal ?? 0, 2) }}</p>
                    <p class="text-xs text-gray-400">Today: KES {{ number_format($schoolFeesToday ?? 0, 2) }}</p>
                </div>
            </div>
            <div class="mt-2">
                <a href="{{ route('finance.student-fees.index') }}" class="text-xs text-primary hover:underline">View all →</a>
            </div>
        </div>

        <!-- KCB IPN -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center text-blue-600">
                    <i class="fas fa-sync-alt"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-xs font-medium text-gray-500 truncate">KCB IPN (Auto)</p>
                    <p class="text-sm font-bold text-gray-800">KES {{ number_format($kcbIpnTotal ?? 0, 2) }}</p>
                    <p class="text-xs text-gray-400">Today: KES {{ number_format($kcbIpnToday ?? 0, 2) }}</p>
                </div>
            </div>
            <div class="mt-2">
                <a href="{{ route('finance.student-fees.index', ['payment_method' => 'kcb']) }}" class="text-xs text-primary hover:underline">View all →</a>
            </div>
        </div>

        <!-- Cafeteria Sales -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-lg bg-green-100 flex items-center justify-center text-green-600">
                    <i class="fas fa-utensils"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-xs font-medium text-gray-500 truncate">Cafeteria Sales</p>
                    <p class="text-sm font-bold text-gray-800">KES {{ number_format($cafeteriaSalesTotal ?? 0, 2) }}</p>
                    <p class="text-xs text-gray-400">Today: KES {{ number_format($cafeteriaSalesToday ?? 0, 2) }}</p>
                </div>
            </div>
            <div class="mt-2">
                <a href="{{ route('finance.transactions.index') }}" class="text-xs text-primary hover:underline">View all →</a>
            </div>
        </div>

        <!-- Event Fees -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-lg bg-purple-100 flex items-center justify-center text-purple-600">
                    <i class="fas fa-calendar-check"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-xs font-medium text-gray-500 truncate">Event Fees</p>
                    <p class="text-sm font-bold text-gray-800">KES {{ number_format($eventFeesTotal ?? 0, 2) }}</p>
                    <p class="text-xs text-gray-400">Today: KES {{ number_format($eventFeesToday ?? 0, 2) }}</p>
                </div>
            </div>
            <div class="mt-2">
                <a href="{{ route('finance.transactions.mpesa') }}" class="text-xs text-primary hover:underline">View all →</a>
            </div>
        </div>

        <!-- Application Fees -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-lg bg-amber-100 flex items-center justify-center text-amber-600">
                    <i class="fas fa-file-alt"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-xs font-medium text-gray-500 truncate">Application Fees</p>
                    <p class="text-sm font-bold text-gray-800">KES {{ number_format($applicationFeesTotal ?? 0, 2) }}</p>
                    <p class="text-xs text-gray-400">Today: KES {{ number_format($applicationFeesToday ?? 0, 2) }}</p>
                </div>
            </div>
            <div class="mt-2">
                <a href="{{ route('finance.transactions.mpesa') }}" class="text-xs text-primary hover:underline">View all →</a>
            </div>
        </div>
    </div>

    <!-- ============================================================ -->
    <!-- ROW 3: PENDING ITEMS -->
    <!-- ============================================================ -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <!-- Pending Verifications -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-gray-500">Pending Verifications</p>
                    <p class="text-lg font-bold text-orange-600">{{ $pendingFeeVerifications ?? 0 }}</p>
                </div>
                <div class="w-10 h-10 rounded-lg bg-orange-100 flex items-center justify-center text-orange-600">
                    <i class="fas fa-clock"></i>
                </div>
            </div>
            @if(($pendingFeeVerifications ?? 0) > 0)
                <a href="{{ route('finance.student-fees.index', ['is_verified' => 0]) }}" class="text-xs text-primary hover:underline">Verify now →</a>
            @else
                <span class="text-xs text-green-600"><i class="fas fa-check-circle mr-1"></i> All verified</span>
            @endif
        </div>

        <!-- Pending Applications -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-gray-500">Pending Applications</p>
                    <p class="text-lg font-bold text-amber-600">{{ $pendingApplications ?? 0 }}</p>
                </div>
                <div class="w-10 h-10 rounded-lg bg-amber-100 flex items-center justify-center text-amber-600">
                    <i class="fas fa-file-alt"></i>
                </div>
            </div>
            @if(($pendingApplications ?? 0) > 0)
                <a href="{{ route('finance.student-fees.index') }}" class="text-xs text-primary hover:underline">Review now →</a>
            @else
                <span class="text-xs text-green-600"><i class="fas fa-check-circle mr-1"></i> All processed</span>
            @endif
        </div>

        <!-- Pending Transactions -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-gray-500">Pending Transactions</p>
                    <p class="text-lg font-bold text-indigo-600">{{ $pendingTransactions ?? 0 }}</p>
                </div>
                <div class="w-10 h-10 rounded-lg bg-indigo-100 flex items-center justify-center text-indigo-600">
                    <i class="fas fa-exchange-alt"></i>
                </div>
            </div>
            @if(($pendingTransactions ?? 0) > 0)
                <a href="{{ route('finance.transactions.pending') }}" class="text-xs text-primary hover:underline">View pending →</a>
            @else
                <span class="text-xs text-green-600"><i class="fas fa-check-circle mr-1"></i> All processed</span>
            @endif
        </div>

        <!-- Fee Structure Changes -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-gray-500">Fee Structure Changes</p>
                    <p class="text-lg font-bold text-amber-600">{{ $pendingFeeChanges ?? 0 }}</p>
                </div>
                <div class="w-10 h-10 rounded-lg bg-amber-100 flex items-center justify-center text-amber-600">
                    <i class="fas fa-file-invoice-dollar"></i>
                </div>
            </div>
            @if(($pendingFeeChanges ?? 0) > 0)
                <a href="{{ route('finance.fee-structure.index', ['filter' => 'pending']) }}" class="text-xs text-primary hover:underline">Review changes →</a>
            @else
                <span class="text-xs text-green-600"><i class="fas fa-check-circle mr-1"></i> No pending changes</span>
            @endif
        </div>
    </div>

    <!-- ============================================================ -->
    <!-- ROW 4: CHARTS -->
    <!-- ============================================================ -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Monthly Trend Chart - Stacked -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h3 class="font-bold text-gray-800 mb-4">Monthly Collection Trend</h3>
            <div style="height: 250px;">
                <canvas id="monthlyTrendChart"></canvas>
            </div>
            <div class="flex flex-wrap justify-center gap-4 mt-3">
                <span class="text-xs flex items-center"><span class="w-3 h-3 bg-red-500 rounded-full mr-1"></span> School Fees</span>
                <span class="text-xs flex items-center"><span class="w-3 h-3 bg-blue-500 rounded-full mr-1"></span> KCB IPN</span>
                <span class="text-xs flex items-center"><span class="w-3 h-3 bg-green-500 rounded-full mr-1"></span> Cafeteria</span>
                <span class="text-xs flex items-center"><span class="w-3 h-3 bg-purple-500 rounded-full mr-1"></span> Events</span>
            </div>
        </div>

        <!-- Payment Methods Chart -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h3 class="font-bold text-gray-800 mb-4">Payment Methods Breakdown</h3>
            <div style="height: 250px;">
                <canvas id="paymentMethodsChart"></canvas>
            </div>
        </div>
    </div>

    <!-- ============================================================ -->
    <!-- ROW 5: RECENT ACTIVITY -->
    <!-- ============================================================ -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent School Fee Payments -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-bold text-gray-800">Recent School Fee Payments</h3>
                <a href="{{ route('finance.student-fees.index') }}" class="text-sm text-primary hover:text-primary-dark hover:underline font-medium">
                    View all →
                </a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-gray-500 border-b">
                            <th class="pb-2 font-semibold">Student</th>
                            <th class="pb-2 font-semibold">Amount</th>
                            <th class="pb-2 font-semibold">Method</th>
                            <th class="pb-2 font-semibold">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentFeePayments ?? [] as $payment)
                            <tr class="border-b border-gray-100 hover:bg-gray-50">
                                <td class="py-2">
                                    <span class="font-medium">{{ $payment->student->full_name ?? 'N/A' }}</span>
                                    <span class="text-xs text-gray-500 block">{{ $payment->student->student_number ?? '' }}</span>
                                </td>
                                <td class="py-2 font-semibold text-gray-800">KES {{ number_format($payment->amount, 2) }}</td>
                                <td class="py-2">
                                    <span class="text-xs uppercase font-medium">{{ $payment->payment_method_label }}</span>
                                </td>
                                <td class="py-2">
                                    @if($payment->is_verified)
                                        <span class="status-badge status-verified"><i class="fas fa-check-circle mr-1"></i> Verified</span>
                                    @else
                                        <span class="status-badge status-pending"><i class="fas fa-clock mr-1"></i> Pending</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="py-4 text-center text-gray-500">No recent payments</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Recent Cafeteria Transactions -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-bold text-gray-800">Recent Cafeteria Transactions</h3>
                <a href="{{ route('finance.transactions.index') }}" class="text-sm text-primary hover:text-primary-dark hover:underline font-medium">
                    View all →
                </a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-gray-500 border-b">
                            <th class="pb-2 font-semibold">Reference</th>
                            <th class="pb-2 font-semibold">Amount</th>
                            <th class="pb-2 font-semibold">Method</th>
                            <th class="pb-2 font-semibold">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentCafeteriaTransactions ?? [] as $transaction)
                            <tr class="border-b border-gray-100 hover:bg-gray-50">
                                <td class="py-2">
                                    <span class="font-medium">{{ $transaction->transaction_number ?? 'N/A' }}</span>
                                    <span class="text-xs text-gray-500 block">{{ $transaction->sale->invoice_number ?? 'N/A' }}</span>
                                </td>
                                <td class="py-2 font-semibold text-gray-800">KES {{ number_format($transaction->amount, 2) }}</td>
                                <td class="py-2">
                                    <span class="text-xs uppercase font-medium">{{ $transaction->payment_method }}</span>
                                </td>
                                <td class="py-2">
                                    <span class="status-badge status-verified"><i class="fas fa-check-circle mr-1"></i> Completed</span>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="py-4 text-center text-gray-500">No recent transactions</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ============================================================ -->
    <!-- ROW 6: RECENT EVENT APPLICATIONS -->
    <!-- ============================================================ -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-bold text-gray-800">Recent Event Registrations</h3>
            <a href="{{ route('finance.transactions.mpesa') }}" class="text-sm text-primary hover:text-primary-dark hover:underline font-medium">
                View all →
            </a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-gray-500 border-b">
                        <th class="pb-2 font-semibold">Event</th>
                        <th class="pb-2 font-semibold">Parent</th>
                        <th class="pb-2 font-semibold">Attendees</th>
                        <th class="pb-2 font-semibold">Amount</th>
                        <th class="pb-2 font-semibold">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentEventApplications ?? [] as $application)
                        <tr class="border-b border-gray-100 hover:bg-gray-50">
                            <td class="py-2">
                                <span class="font-medium">{{ $application->event->title ?? 'N/A' }}</span>
                            </td>
                            <td class="py-2">{{ $application->parent_name }}</td>
                            <td class="py-2">{{ $application->number_of_people }}</td>
                            <td class="py-2 font-semibold text-gray-800">KES {{ number_format($application->total_amount, 2) }}</td>
                            <td class="py-2">
                                <span class="status-badge status-verified"><i class="fas fa-check-circle mr-1"></i> Confirmed</span>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="py-4 text-center text-gray-500">No recent event registrations</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- ============================================================ -->
    <!-- ROW 7: QUICK STATS FOOTER -->
    <!-- ============================================================ -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white p-4 rounded-lg border border-gray-200 text-center">
            <p class="text-2xl font-bold text-gray-800">{{ $totalStudents ?? 0 }}</p>
            <p class="text-xs text-gray-500">Total Students</p>
        </div>
        <div class="bg-white p-4 rounded-lg border border-gray-200 text-center">
            <p class="text-2xl font-bold text-gray-800">{{ $activeEnrollments ?? 0 }}</p>
            <p class="text-xs text-gray-500">Active Enrollments</p>
        </div>
        <div class="bg-white p-4 rounded-lg border border-gray-200 text-center">
            <p class="text-2xl font-bold text-gray-800">{{ $totalPayments ?? 0 }}</p>
            <p class="text-xs text-gray-500">Total Payments</p>
        </div>
        <div class="bg-white p-4 rounded-lg border border-gray-200 text-center">
            <p class="text-2xl font-bold text-orange-600">{{ $pendingVerifications ?? 0 }}</p>
            <p class="text-xs text-gray-500">Pending Verification</p>
        </div>
    </div>

    <!-- ============================================================ -->
    <!-- ROW 8: FEE STRUCTURE QUICK ACTIONS -->
    <!-- ============================================================ -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-bold text-gray-800">Fee Structure Management</h3>
            <a href="{{ route('finance.fee-structure.index') }}" class="text-sm text-primary hover:text-primary-dark hover:underline font-medium">
                Manage Fee Structures →
            </a>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <a href="{{ route('finance.fee-structure.index') }}" class="block p-4 bg-gray-50 rounded-lg border border-gray-200 hover:bg-primary/5 hover:border-primary transition-all">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-lg bg-primary/10 flex items-center justify-center text-primary">
                        <i class="fas fa-list-ul"></i>
                    </div>
                    <div>
                        <p class="font-medium text-gray-800">All Fee Structures</p>
                        <p class="text-xs text-gray-500">View and manage all course fees</p>
                    </div>
                </div>
            </a>
            <a href="{{ route('finance.fee-structure.index', ['filter' => 'pending']) }}" class="block p-4 bg-gray-50 rounded-lg border border-gray-200 hover:bg-amber/5 hover:border-amber-400 transition-all">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-lg bg-amber-100 flex items-center justify-center text-amber-600">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div>
                        <p class="font-medium text-gray-800">Pending Approvals</p>
                        <p class="text-xs text-gray-500">{{ $pendingFeeChanges ?? 0 }} course(s) pending approval</p>
                    </div>
                </div>
            </a>
            <a href="{{ route('finance.fee-structure.export') }}" class="block p-4 bg-gray-50 rounded-lg border border-gray-200 hover:bg-green/5 hover:border-green-400 transition-all">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-lg bg-green-100 flex items-center justify-center text-green-600">
                        <i class="fas fa-file-export"></i>
                    </div>
                    <div>
                        <p class="font-medium text-gray-800">Export Report</p>
                        <p class="text-xs text-gray-500">Download fee structure report</p>
                    </div>
                </div>
            </a>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // ============================================================
        // Monthly Trend Chart - Stacked Bar
        // ============================================================
        const monthlyCtx = document.getElementById('monthlyTrendChart');
        if (monthlyCtx) {
            const labels = {!! json_encode($monthlyLabels ?? []) !!};
            const schoolData = {!! json_encode($monthlySchoolFees ?? []) !!};
            const kcbData = {!! json_encode($monthlyKcbIpn ?? []) !!};
            const cafeteriaData = {!! json_encode($monthlyCafeteria ?? []) !!};
            const eventData = {!! json_encode($monthlyEvents ?? []) !!};

            new Chart(monthlyCtx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'School Fees',
                            data: schoolData,
                            backgroundColor: 'rgba(185, 28, 28, 0.8)',
                            borderColor: 'rgba(185, 28, 28, 1)',
                            borderWidth: 1,
                            borderRadius: 2
                        },
                        {
                            label: 'KCB IPN',
                            data: kcbData,
                            backgroundColor: 'rgba(59, 130, 246, 0.8)',
                            borderColor: 'rgba(59, 130, 246, 1)',
                            borderWidth: 1,
                            borderRadius: 2
                        },
                        {
                            label: 'Cafeteria',
                            data: cafeteriaData,
                            backgroundColor: 'rgba(16, 185, 129, 0.8)',
                            borderColor: 'rgba(16, 185, 129, 1)',
                            borderWidth: 1,
                            borderRadius: 2
                        },
                        {
                            label: 'Events',
                            data: eventData,
                            backgroundColor: 'rgba(139, 92, 246, 0.8)',
                            borderColor: 'rgba(139, 92, 246, 1)',
                            borderWidth: 1,
                            borderRadius: 2
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        x: {
                            stacked: true
                        },
                        y: {
                            stacked: true,
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return 'KES ' + value.toLocaleString();
                                }
                            }
                        }
                    }
                }
            });
        }

        // ============================================================
        // Payment Methods Chart - Doughnut
        // ============================================================
        const methodsCtx = document.getElementById('paymentMethodsChart');
        if (methodsCtx) {
            const methodData = {!! json_encode($paymentMethods ?? []) !!};
            const labels = methodData.map(item => item.payment_method);
            const data = methodData.map(item => parseFloat(item.total) || 0);
            const colors = methodData.map(item => item.color || '#B91C1C');

            new Chart(methodsCtx, {
                type: 'doughnut',
                data: {
                    labels: labels.length > 0 ? labels : ['No Data'],
                    datasets: [{
                        data: data.length > 0 ? data : [1],
                        backgroundColor: colors.length > 0 ? colors : ['#B91C1C'],
                        borderWidth: 2,
                        borderColor: '#ffffff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                padding: 12,
                                usePointStyle: true,
                                pointStyle: 'circle'
                            }
                        }
                    },
                    cutout: '60%'
                }
            });
        }

        // ============================================================
        // Animate progress bars on load
        // ============================================================
        document.querySelectorAll('.h-1\\.5.rounded-full.bg-gray-200 .h-full').forEach(function(bar) {
            var width = bar.style.width;
            bar.style.width = '0%';
            setTimeout(function() {
                bar.style.width = width;
            }, 100);
        });
    });
</script>
@endpush
