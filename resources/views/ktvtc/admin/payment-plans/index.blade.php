@extends('ktvtc.admin.layout.adminlayout')

@section('title', 'Payment Plans')
@section('subtitle', 'Manage payment plans for students')

@section('breadcrumb')
<li>
    <div class="flex items-center">
        <i class="fas fa-angle-right text-gray-400"></i>
        <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">Fee Management</span>
    </div>
</li>
<li aria-current="page">
    <div class="flex items-center">
        <i class="fas fa-angle-right text-gray-400"></i>
        <span class="ml-1 text-sm font-medium text-gray-800 md:ml-2">Payment Plans</span>
    </div>
</li>
@endsection

@section('header-actions')
<div class="flex space-x-2">
    <a href="{{ route('admin.fees.payment-plans.create') }}" class="px-4 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg font-medium transition-colors flex items-center space-x-2">
        <i class="fas fa-plus"></i>
        <span>New Payment Plan</span>
    </a>
</div>
@endsection

@section('content')
<!-- Stats Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600">Total Plans</p>
                <p class="text-2xl font-bold text-gray-800">{{ $totalPlans }}</p>
            </div>
            <div class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center">
                <i class="fas fa-file-invoice-dollar text-blue-600 text-xl"></i>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600">Active Plans</p>
                <p class="text-2xl font-bold text-green-600">{{ $activePlans }}</p>
            </div>
            <div class="w-12 h-12 rounded-full bg-green-100 flex items-center justify-center">
                <i class="fas fa-check-circle text-green-600 text-xl"></i>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600">Overdue Plans</p>
                <p class="text-2xl font-bold text-red-600">{{ $overduePlans }}</p>
            </div>
            <div class="w-12 h-12 rounded-full bg-red-100 flex items-center justify-center">
                <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600">Total Balance</p>
                <p class="text-2xl font-bold text-gray-800">KES {{ number_format($totalBalance, 2) }}</p>
            </div>
            <div class="w-12 h-12 rounded-full bg-purple-100 flex items-center justify-center">
                <i class="fas fa-money-bill-wave text-purple-600 text-xl"></i>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="bg-white rounded-xl border border-gray-200 mb-6">
    <div class="p-6">
        <form method="GET" action="{{ route('admin.fees.payment-plans.index') }}">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Student</label>
                    <select name="student_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        <option value="">All Students</option>
                        @foreach($students as $student)
                            <option value="{{ $student->id }}" {{ request('student_id') == $student->id ? 'selected' : '' }}>
                                {{ $student->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        <option value="">All Status</option>
                        @foreach($statuses as $status)
                            <option value="{{ $status }}" {{ request('status') == $status ? 'selected' : '' }}>
                                {{ ucfirst(str_replace('_', ' ', $status)) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Plan Type</label>
                    <select name="plan_type" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        <option value="">All Types</option>
                        @foreach($planTypes as $type)
                            <option value="{{ $type }}" {{ request('plan_type') == $type ? 'selected' : '' }}>
                                {{ ucfirst(str_replace('_', ' ', $type)) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Registration</label>
                    <select name="registration_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        <option value="">All Registrations</option>
                        @foreach($registrations as $registration)
                            <option value="{{ $registration->id }}" {{ request('registration_id') == $registration->id ? 'selected' : '' }}>
                                {{ $registration->student->name ?? 'N/A' }} - {{ $registration->course->name ?? 'N/A' }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="flex justify-end space-x-3 mt-4">
                <a href="{{ route('admin.fees.payment-plans.index') }}" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                    Clear Filters
                </a>
                <button type="submit" class="px-4 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg">
                    Apply Filters
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Payment Plans Table -->
<div class="bg-white rounded-xl border border-gray-200">
    <div class="p-6 border-b border-gray-200">
        <h3 class="text-lg font-semibold text-gray-800">Payment Plans</h3>
    </div>
    <div class="p-6">
        @if($paymentPlans->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-gray-50">
                            <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase">Plan Code</th>
                            <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase">Student</th>
                            <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase">Course</th>
                            <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase">Total Amount</th>
                            <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase">Balance</th>
                            <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($paymentPlans as $plan)
                        <tr class="hover:bg-gray-50">
                            <td class="py-3 px-4">
                                <div class="font-medium text-gray-900">{{ $plan->plan_code }}</div>
                                <div class="text-sm text-gray-600">{{ $plan->plan_name }}</div>
                            </td>
                            <td class="py-3 px-4">
                                <div class="font-medium text-gray-900">{{ $plan->student->name ?? 'N/A' }}</div>
                                <div class="text-sm text-gray-600">{{ $plan->student->admission_number ?? '' }}</div>
                            </td>
                            <td class="py-3 px-4">
                                <div class="text-gray-900">{{ $plan->registration->course->name ?? 'N/A' }}</div>
                                <div class="text-sm text-gray-600">{{ $plan->registration->campus->name ?? 'N/A' }}</div>
                            </td>
                            <td class="py-3 px-4">
                                <div class="font-medium">KES {{ number_format($plan->total_course_amount, 2) }}</div>
                                <div class="text-sm text-gray-600">{{ $plan->number_of_installments }} installments</div>
                            </td>
                            <td class="py-3 px-4">
                                <div class="font-medium {{ $plan->total_balance > 0 ? 'text-red-600' : 'text-green-600' }}">
                                    KES {{ number_format($plan->total_balance, 2) }}
                                </div>
                                <div class="text-sm text-gray-600">
                                    {{ round($plan->completion_percentage) }}% complete
                                </div>
                            </td>
                            <td class="py-3 px-4">
                                @php
                                    $statusColors = [
                                        'draft' => 'bg-gray-100 text-gray-800',
                                        'pending_approval' => 'bg-yellow-100 text-yellow-800',
                                        'approved' => 'bg-blue-100 text-blue-800',
                                        'active' => 'bg-green-100 text-green-800',
                                        'completed' => 'bg-purple-100 text-purple-800',
                                        'cancelled' => 'bg-red-100 text-red-800',
                                        'suspended' => 'bg-orange-100 text-orange-800',
                                        'defaulted' => 'bg-red-100 text-red-800',
                                    ];
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$plan->status] ?? 'bg-gray-100 text-gray-800' }}">
                                    {{ ucfirst(str_replace('_', ' ', $plan->status)) }}
                                </span>
                            </td>
                            <td class="py-3 px-4">
                                <div class="flex space-x-2">
                                    <a href="{{ route('admin.fees.payment-plans.show', $plan) }}"
                                       class="p-1 text-blue-600 hover:text-blue-800" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @if(in_array($plan->status, ['draft', 'pending_approval']))
                                    <a href="{{ route('admin.fees.payment-plans.edit', $plan) }}"
                                       class="p-1 text-yellow-600 hover:text-yellow-800" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @endif
                                    <button onclick="deletePlan('{{ $plan->id }}')"
                                            class="p-1 text-red-600 hover:text-red-800" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $paymentPlans->links() }}
            </div>
        @else
            <div class="text-center py-12">
                <div class="text-gray-400 text-5xl mb-4">
                    <i class="fas fa-file-invoice-dollar"></i>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No Payment Plans Found</h3>
                <p class="text-gray-600 mb-6">Get started by creating a new payment plan.</p>
                <a href="{{ route('admin.fees.payment-plans.create') }}" class="px-4 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg inline-flex items-center">
                    <i class="fas fa-plus mr-2"></i>
                    Create Payment Plan
                </a>
            </div>
        @endif
    </div>
</div>

<!-- Delete Modal -->
<div id="deleteModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity modal-overlay" onclick="closeModal('deleteModal')"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen"></span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full modal-content">
            <div class="bg-white px-6 pt-5 pb-4">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-800">Delete Payment Plan</h3>
                    <button onclick="closeModal('deleteModal')" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <p class="text-gray-700">Are you sure you want to delete this payment plan? This action cannot be undone.</p>
            </div>
            <div class="bg-gray-50 px-6 py-4 flex justify-end space-x-3">
                <button onclick="closeModal('deleteModal')"
                        class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                    Cancel
                </button>
                <form id="deleteForm" method="POST" action="">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg">
                        Delete Plan
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function deletePlan(planId) {
        document.getElementById('deleteForm').action = `/admin/fees/payment-plans/${planId}`;
        openModal('deleteModal', 'lg');
    }
</script>
@endsection
