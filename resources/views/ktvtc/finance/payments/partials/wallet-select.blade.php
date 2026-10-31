{{-- Which card wallet the money goes to; defaults to the letter the payer typed (T = transport, C = canteen) --}}
<select name="funding_type" class="no-select2 border border-gray-300 rounded-lg px-2 py-1 text-sm" aria-label="Wallet">
    <option value="C" @selected($fundingType === 'C')>Canteen</option>
    <option value="T" @selected($fundingType === 'T')>Transport</option>
</select>
