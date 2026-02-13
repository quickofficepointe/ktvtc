@extends('ktvtc.library.layout.librarylayout')

@section('content')
<div class="container mx-auto px-4 py-8">

    {{-- Success Message --}}
    @if(session('success'))
        <div class="mb-6 p-4 rounded-lg bg-green-50 border border-green-200">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <span class="text-green-800 font-medium">{{ session('success') }}</span>
            </div>
        </div>
    @endif

    {{-- Header Section --}}
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 font-serif">Fine Rules</h1>
            <p class="text-gray-600 mt-2">Configure library fine policies and calculations</p>
        </div>
        <button onclick="openCreateModal()"
            class="bg-amber-600 hover:bg-amber-700 text-white px-6 py-3 rounded-lg font-medium transition-all duration-200 transform hover:scale-105 shadow-lg hover:shadow-xl flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
            </svg>
            Add Rule
        </button>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-xl shadow-sm border border-amber-200 p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-lg bg-amber-100 text-amber-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total Rules</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $rules->count() }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-green-200 p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-lg bg-green-100 text-green-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 01118 0z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Active Rules</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $rules->where('is_active', true)->count() }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-blue-200 p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-lg bg-blue-100 text-blue-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Daily Rules</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $rules->where('calculation_type', 'daily')->count() }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-purple-200 p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-lg bg-purple-100 text-purple-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Fixed Rules</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $rules->where('calculation_type', 'fixed')->count() }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Rules Table --}}
    <div class="bg-white rounded-xl shadow-sm border border-amber-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-amber-200 bg-amber-50">
            <h2 class="text-lg font-semibold text-gray-900 font-serif">Fine Policies</h2>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-amber-50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-amber-800 uppercase tracking-wider">Rule Details</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-amber-800 uppercase tracking-wider">Calculation</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-amber-800 uppercase tracking-wider">Grace Period</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-amber-800 uppercase tracking-wider">Limits</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-amber-800 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-amber-800 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-amber-100">
                    @forelse($rules as $rule)
                        <tr class="hover:bg-amber-50 transition-colors duration-150">
                            <td class="px-6 py-4">
                                <div class="flex-1 min-w-0">
                                    <h3 class="text-lg font-semibold text-gray-900 font-serif">{{ $rule->name }}</h3>
                                    @if($rule->description)
                                        <p class="text-sm text-gray-500 mt-1">{{ $rule->description }}</p>
                                    @endif
                                    <p class="text-xs text-gray-400 mt-1">Created: {{ $rule->created_at->format('M d, Y') }}</p>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="space-y-1">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        {{ $rule->calculation_type === 'daily' ? 'bg-blue-100 text-blue-800' :
                                           ($rule->calculation_type === 'fixed' ? 'bg-purple-100 text-purple-800' : 'bg-green-100 text-green-800') }}">
                                        {{ ucfirst($rule->calculation_type) }}
                                    </span>
                                    <p class="text-sm font-semibold text-gray-900">
                                        ${{ number_format($rule->fine_amount, 2) }}
                                        @if($rule->calculation_type === 'daily')
                                            <span class="text-xs text-gray-500">/ day</span>
                                        @endif
                                    </p>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-center">
                                    <span class="inline-flex items-center justify-center w-12 h-12 rounded-full
                                        {{ $rule->grace_period_days > 0 ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }} font-bold text-lg">
                                        {{ $rule->grace_period_days }}
                                    </span>
                                    <p class="text-xs text-gray-500 mt-1">days</p>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="space-y-1 text-sm">
                                    @if($rule->max_fine_days)
                                        <p class="text-gray-600">
                                            <span class="font-medium">Max Days:</span> {{ $rule->max_fine_days }}
                                        </p>
                                    @endif
                                    @if($rule->max_fine_amount)
                                        <p class="text-gray-600">
                                            <span class="font-medium">Max Amount:</span> ${{ number_format($rule->max_fine_amount, 2) }}
                                        </p>
                                    @endif
                                    @if(!$rule->max_fine_days && !$rule->max_fine_amount)
                                        <p class="text-gray-400 italic">No limits</p>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium {{ $rule->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                    <span class="w-2 h-2 rounded-full mr-2 {{ $rule->is_active ? 'bg-green-400' : 'bg-gray-400' }}"></span>
                                    {{ $rule->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center space-x-2">
                                    <button onclick="openEditModal({{ $rule->id }})"
                                        class="inline-flex items-center px-3 py-2 border border-amber-300 shadow-sm text-sm leading-4 font-medium rounded-md text-amber-700 bg-white hover:bg-amber-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500 transition-colors">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                        Edit
                                    </button>
                                    @if($rule->is_active)
                                        <button onclick="deactivateRule({{ $rule->id }})"
                                            class="inline-flex items-center px-3 py-2 border border-red-300 shadow-sm text-sm leading-4 font-medium rounded-md text-red-700 bg-white hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                            </svg>
                                            Deactivate
                                        </button>
                                    @else
                                        <button onclick="activateRule({{ $rule->id }})"
                                            class="inline-flex items-center px-3 py-2 border border-green-300 shadow-sm text-sm leading-4 font-medium rounded-md text-green-700 bg-white hover:bg-green-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                            </svg>
                                            Activate
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center justify-center text-gray-500">
                                    <svg class="w-16 h-16 mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                    </svg>
                                    <h3 class="text-lg font-medium text-gray-900 mb-2">No fine rules found</h3>
                                    <p class="text-gray-600 mb-4">Configure your library's fine policies.</p>
                                    <button onclick="openCreateModal()"
                                        class="bg-amber-600 hover:bg-amber-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                                        Create First Rule
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Create Rule Modal -->
<div id="createModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 py-6">
        <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity" onclick="closeCreateModal()"></div>

        <div class="relative bg-white rounded-2xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-hidden transform transition-all">
            <!-- Modal Header -->
            <div class="bg-gradient-to-r from-amber-600 to-amber-700 px-6 py-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="p-2 bg-white bg-opacity-20 rounded-lg">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-white font-serif">Create Fine Rule</h3>
                            <p class="text-amber-100 text-sm">Configure a new fine policy</p>
                        </div>
                    </div>
                    <button onclick="closeCreateModal()"
                            class="p-2 hover:bg-white hover:bg-opacity-20 rounded-lg transition-colors">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Modal Body -->
            <div class="p-6 overflow-y-auto max-h-[calc(90vh-80px)]">
                <form action="{{ route('fine-rules.store') }}" method="POST" class="space-y-6">
                    @csrf

                    <div class="grid grid-cols-1 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Rule Name *</label>
                            <input type="text" name="name" value="{{ old('name') }}" required
                                   class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-amber-500 focus:border-transparent transition-colors"
                                   placeholder="e.g., Standard Late Fee, Student Fine Policy">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                            <textarea name="description" rows="3"
                                      class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-amber-500 focus:border-transparent transition-colors"
                                      placeholder="Describe this fine policy">{{ old('description') }}</textarea>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Calculation Type *</label>
                                <select name="calculation_type" required
                                        class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-amber-500 focus:border-transparent transition-colors">
                                    <option value="daily" {{ old('calculation_type', 'daily') == 'daily' ? 'selected' : '' }}>Daily Fine</option>
                                    <option value="fixed" {{ old('calculation_type') == 'fixed' ? 'selected' : '' }}>Fixed Fine</option>
                                    <option value="percentage" {{ old('calculation_type') == 'percentage' ? 'selected' : '' }}>Percentage</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Fine Amount *</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="text-gray-500 sm:text-sm">$</span>
                                    </div>
                                    <input type="number" name="fine_amount" value="{{ old('fine_amount', 0.50) }}" required step="0.01" min="0"
                                           class="pl-7 w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-amber-500 focus:border-transparent transition-colors"
                                           placeholder="0.50">
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Grace Period (Days)</label>
                                <input type="number" name="grace_period_days" value="{{ old('grace_period_days', 3) }}" min="0"
                                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-amber-500 focus:border-transparent transition-colors">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Max Fine Days</label>
                                <input type="number" name="max_fine_days" value="{{ old('max_fine_days') }}" min="0"
                                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-amber-500 focus:border-transparent transition-colors"
                                       placeholder="e.g., 30">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Max Fine Amount</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 sm:text-sm">$</span>
                                </div>
                                <input type="number" name="max_fine_amount" value="{{ old('max_fine_amount') }}" step="0.01" min="0"
                                       class="pl-7 w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-amber-500 focus:border-transparent transition-colors"
                                       placeholder="e.g., 10.00">
                            </div>
                        </div>
                    </div>

                    <!-- Status -->
                    <div class="bg-amber-50 rounded-xl p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <h4 class="text-lg font-semibold text-gray-900 mb-1">Rule Status</h4>
                                <p class="text-sm text-gray-600">Activate this rule for use</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="is_active" value="1" checked class="sr-only peer">
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-amber-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-amber-600"></div>
                                <span class="ml-3 text-sm font-medium text-gray-900">Active</span>
                            </label>
                        </div>
                    </div>

                    <!-- Modal Footer -->
                    <div class="flex justify-end space-x-3 pt-6 border-t border-gray-200">
                        <button type="button" onclick="closeCreateModal()"
                                class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 font-medium hover:bg-gray-50 transition-colors">
                            Cancel
                        </button>
                        <button type="submit"
                                class="px-6 py-3 bg-amber-600 text-white rounded-lg font-medium hover:bg-amber-700 transition-colors shadow-lg hover:shadow-xl">
                            Create Rule
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    // Modal Functions
    function openCreateModal() {
        document.getElementById('createModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeCreateModal() {
        document.getElementById('createModal').classList.add('hidden');
        document.body.style.overflow = 'auto';
    }

    function deactivateRule(ruleId) {
        if(confirm('Deactivate this fine rule? It will no longer be used for new fines.')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/fine-rules/${ruleId}/deactivate`;

            const csrf = document.createElement('input');
            csrf.type = 'hidden';
            csrf.name = '_token';
            csrf.value = '{{ csrf_token() }}';

            form.appendChild(csrf);
            document.body.appendChild(form);
            form.submit();
        }
    }

    function activateRule(ruleId) {
        if(confirm('Activate this fine rule?')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/fine-rules/${ruleId}/activate`;

            const csrf = document.createElement('input');
            csrf.type = 'hidden';
            csrf.name = '_token';
            csrf.value = '{{ csrf_token() }}';

            form.appendChild(csrf);
            document.body.appendChild(form);
            form.submit();
        }
    }

    // Close modals on Escape key
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closeCreateModal();
        }
    });
</script>
@endsection
