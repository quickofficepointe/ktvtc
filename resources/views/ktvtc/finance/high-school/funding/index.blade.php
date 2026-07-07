@extends('ktvtc.finance.layouts.app')

@section('title', 'Funding Requests')
@section('subtitle', 'Manage all card funding requests')

@section('header-actions')
<div class="flex flex-wrap justify-end gap-2">
    <a href="{{ route('finance.funding.export') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-3 py-2 rounded-lg font-semibold flex items-center text-sm transition">
        <i class="fas fa-file-export mr-2"></i> Export
    </a>
</div>
@endsection

@section('content')
<div class="w-full max-w-full space-y-6 overflow-hidden">

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white rounded-xl shadow-sm border border-yellow-100 border-l-4 border-l-yellow-500 p-4">
            <p class="text-xs font-medium text-gray-500">Pending</p>
            <p class="text-xl font-bold text-yellow-600 mt-1">{{ number_format($pending ?? 0) }}</p>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-green-100 border-l-4 border-l-green-500 p-4">
            <p class="text-xs font-medium text-gray-500">Completed</p>
            <p class="text-xl font-bold text-green-600 mt-1">{{ number_format($completed ?? 0) }}</p>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-red-100 border-l-4 border-l-red-500 p-4">
            <p class="text-xs font-medium text-gray-500">Failed</p>
            <p class="text-xl font-bold text-red-600 mt-1">{{ number_format($failed ?? 0) }}</p>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 sm:p-6">
        <form method="GET" action="{{ route('finance.funding.index') }}" class="grid grid-cols-1 lg:grid-cols-12 gap-4 items-end">
            <div class="lg:col-span-6">
                <label class="text-xs font-semibold text-gray-600">Search</label>
                <input type="text"
                       name="search"
                       value="{{ request('search') }}"
                       placeholder="Parent phone, name..."
                       class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary text-sm">
            </div>

            <div class="lg:col-span-3">
                <label class="text-xs font-semibold text-gray-600">Status</label>
                <select name="status" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary text-sm">
                    <option value="">All</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="processing" {{ request('status') == 'processing' ? 'selected' : '' }}>Processing</option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Failed</option>
                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
            </div>

            <div class="lg:col-span-3 flex flex-wrap gap-2">
                <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary-dark transition text-sm font-semibold">
                    <i class="fas fa-search mr-2"></i> Filter
                </button>

                <a href="{{ route('finance.funding.index') }}" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition text-sm text-gray-700">
                    <i class="fas fa-times mr-1"></i> Reset
                </a>
            </div>
        </form>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100">
        <div class="w-full overflow-x-auto">
            <table class="min-w-[900px] w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600">Student</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600">Parent</th>
                        <th class="px-4 py-3 text-right font-semibold text-gray-600">Amount</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600">Status</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600">Date</th>
                        <th class="px-4 py-3 text-center font-semibold text-gray-600">Actions</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-200">
                    @forelse($requests ?? [] as $funding)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-4 py-3">
                                <span class="font-medium text-gray-900">{{ $funding->student_name ?? 'N/A' }}</span>
                                <span class="text-xs text-gray-500 block">{{ $funding->student_admission ?? '' }}</span>
                            </td>

                            <td class="px-4 py-3">
                                <span class="text-gray-800">{{ $funding->parent_name ?? 'N/A' }}</span>
                                <span class="text-xs text-gray-500 block">{{ $funding->parent_phone }}</span>
                            </td>

                            <td class="px-4 py-3 text-right font-bold text-gray-800">
                                KES {{ number_format($funding->amount ?? 0, 2) }}
                            </td>

                            <td class="px-4 py-3">
                                @if($funding->status === 'completed')
                                    <span class="text-xs px-2 py-0.5 bg-green-100 text-green-600 rounded-full">Completed</span>
                                @elseif($funding->status === 'pending' || $funding->status === 'processing')
                                    <span class="text-xs px-2 py-0.5 bg-yellow-100 text-yellow-600 rounded-full">{{ ucfirst($funding->status) }}</span>
                                @elseif($funding->status === 'failed')
                                    <span class="text-xs px-2 py-0.5 bg-red-100 text-red-600 rounded-full">Failed</span>
                                @else
                                    <span class="text-xs px-2 py-0.5 bg-gray-100 text-gray-500 rounded-full">{{ ucfirst($funding->status) }}</span>
                                @endif
                            </td>

                            <td class="px-4 py-3 text-sm text-gray-600 whitespace-nowrap">
                                {{ optional($funding->created_at)->format('d M Y H:i') }}
                            </td>

                            <td class="px-4 py-3 text-center">
                                <div class="flex justify-center gap-3 whitespace-nowrap">
                                    <a href="{{ route('finance.funding.show', $funding) }}" class="text-primary hover:text-primary-dark" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>

                                    @if($funding->status === 'pending' || $funding->status === 'processing')
                                        <button type="button" onclick="retryFunding({{ $funding->id }})" class="text-blue-600 hover:text-blue-800" title="Retry">
                                            <i class="fas fa-sync"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-12 text-center text-gray-500">
                                <i class="fas fa-wallet text-4xl text-gray-300 mb-3 block"></i>
                                <p class="text-lg font-medium">No funding requests found</p>
                                <p class="text-sm text-gray-400 mt-1">Funding requests will appear here once submitted.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(isset($requests))
            <div class="px-4 sm:px-6 py-4 border-t border-gray-200 bg-gray-50 overflow-x-auto">
                {{ $requests->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
    function retryFunding(id) {
        if (!confirm('Retry this funding request?')) {
            return;
        }

        showLoading('Retrying...');

        fetch(`/finance/funding/${id}/retry`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => response.json())
        .then(data => {
            hideLoading();

            if (data.success) {
                toastr.success(data.message || 'Retry initiated');
                setTimeout(() => location.reload(), 1000);
            } else {
                toastr.error(data.message || 'Failed to retry');
            }
        })
        .catch(() => {
            hideLoading();
            toastr.error('An error occurred');
        });
    }
</script>
@endpush
