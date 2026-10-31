@extends('ktvtc.finance.layouts.app')

@section('title', 'Withdrawal Charges')
@section('subtitle', 'Safaricom charge added to transport top-ups and deducted each time fare is given')

@section('breadcrumb')
<li><span class="mx-2">/</span><a href="{{ route('finance.transport.index') }}" class="text-gray-600 hover:text-primary">Transport</a></li>
<li><span class="mx-2">/</span><span class="text-gray-400">Withdrawal charges</span></li>
@endsection

@section('content')
<div class="finance-card p-5 max-w-2xl">
    <p class="text-sm text-gray-600 mb-4">
        Check these against Safaricom's current M-Pesa withdrawal tariff. They are used in two places:
        a parent topping up transport is prompted for the amount <strong>plus</strong> this charge,
        and each fare hand-out deducts the cash <strong>plus</strong> this charge from the transport balance.
    </p>
    <form method="POST" action="{{ route('finance.transport.fees.update') }}">
        @csrf
        @method('PUT')
        <table class="w-full text-sm" id="tierTable">
            <thead>
                <tr class="text-left text-gray-500 border-b">
                    <th class="py-2">From (KES)</th><th class="py-2">To (KES)</th><th class="py-2">Charge (KES)</th><th></th>
                </tr>
            </thead>
            <tbody>
                @foreach(old('tiers', $tiers->toArray()) as $i => $t)
                    <tr class="border-b">
                        <td class="py-1 pr-2"><input type="number" step="0.01" name="tiers[{{ $i }}][min_amount]" value="{{ $t['min_amount'] }}" required class="w-full border rounded px-2 py-1"></td>
                        <td class="py-1 pr-2"><input type="number" step="0.01" name="tiers[{{ $i }}][max_amount]" value="{{ $t['max_amount'] }}" required class="w-full border rounded px-2 py-1"></td>
                        <td class="py-1 pr-2"><input type="number" step="0.01" name="tiers[{{ $i }}][fee]" value="{{ $t['fee'] }}" required class="w-full border rounded px-2 py-1"></td>
                        <td class="py-1"><button type="button" onclick="this.closest('tr').remove()" class="text-red-600 text-xs">Remove</button></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="flex gap-2 mt-4">
            <button type="button" onclick="addTier()" class="border rounded-lg px-3 py-2 text-sm">Add band</button>
            <button class="btn-primary text-white px-4 py-2 rounded-lg text-sm font-semibold">Save charges</button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    let next = 1000;
    function addTier() {
        const i = next++;
        document.querySelector('#tierTable tbody').insertAdjacentHTML('beforeend', `
            <tr class="border-b">
                <td class="py-1 pr-2"><input type="number" step="0.01" name="tiers[${i}][min_amount]" required class="w-full border rounded px-2 py-1"></td>
                <td class="py-1 pr-2"><input type="number" step="0.01" name="tiers[${i}][max_amount]" required class="w-full border rounded px-2 py-1"></td>
                <td class="py-1 pr-2"><input type="number" step="0.01" name="tiers[${i}][fee]" required class="w-full border rounded px-2 py-1"></td>
                <td class="py-1"><button type="button" onclick="this.closest('tr').remove()" class="text-red-600 text-xs">Remove</button></td>
            </tr>`);
    }
</script>
@endpush
