{{--
    Verify Transaction Modal
    Usage: @include('ktvtc.finance.transactions.verify-modal', ['transactionId' => $transaction->id ?? null])
--}}

<div id="verifyModal" class="hidden fixed inset-0 z-[1200] overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 py-6">
        <div class="fixed inset-0 bg-black/50" onclick="closeVerifyModal()"></div>

        <div class="relative bg-white rounded-xl p-6 max-w-md w-full shadow-2xl">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-bold text-gray-800 flex items-center">
                    <i class="fas fa-check-circle text-green-600 mr-2"></i>
                    Verify Transaction
                </h3>
                <button type="button" onclick="closeVerifyModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <form id="verifyForm" method="POST" action="{{ isset($transactionId) ? route('finance.transactions.verify', $transactionId) : '#' }}">
                @csrf

                <div class="mb-4">
                    <p class="text-sm text-gray-600 mb-3">
                        Are you sure you want to verify this transaction? This will mark it as completed.
                    </p>

                    <label class="text-sm font-medium text-gray-700 block mb-1">
                        Notes <span class="text-gray-400">(Optional)</span>
                    </label>
                    <textarea name="notes"
                              id="verifyNotes"
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition duration-200"
                              rows="3"
                              placeholder="Add verification notes..."></textarea>
                </div>

                <div class="flex justify-end space-x-3">
                    <button type="button"
                            onclick="closeVerifyModal()"
                            class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors font-medium text-gray-700">
                        Cancel
                    </button>

                    <button type="submit"
                            class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors font-semibold flex items-center">
                        <i class="fas fa-check-circle mr-2"></i>
                        Verify Transaction
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function openVerifyModal(transactionId) {
        const modal = document.getElementById('verifyModal');
        const form = document.getElementById('verifyForm');

        if (form && transactionId) {
            form.action = '/finance/transactions/' + transactionId + '/verify';
        }

        if (modal) {
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        // Clear previous notes
        const notes = document.getElementById('verifyNotes');
        if (notes) {
            notes.value = '';
        }
    }

    function closeVerifyModal() {
        const modal = document.getElementById('verifyModal');

        if (modal) {
            modal.classList.add('hidden');
            document.body.style.overflow = 'auto';
        }
    }

    // Close on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeVerifyModal();
        }
    });

    // Close on overlay click
    document.addEventListener('DOMContentLoaded', function() {
        const modal = document.getElementById('verifyModal');
        if (modal) {
            modal.addEventListener('click', function(e) {
                if (e.target === this || e.target.classList.contains('bg-black/50')) {
                    closeVerifyModal();
                }
            });
        }
    });
</script>
@endpush
