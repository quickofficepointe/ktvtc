{{--
    Reverse Transaction Modal
    Usage: @include('ktvtc.finance.transactions.reverse-modal', ['transactionId' => $transaction->id ?? null])
--}}

<div id="reverseModal" class="hidden fixed inset-0 z-[1200] overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 py-6">
        <div class="fixed inset-0 bg-black/50" onclick="closeReverseModal()"></div>

        <div class="relative bg-white rounded-xl p-6 max-w-md w-full shadow-2xl">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-bold text-gray-800 flex items-center">
                    <i class="fas fa-undo text-red-600 mr-2"></i>
                    Reverse Transaction
                </h3>
                <button type="button" onclick="closeReverseModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <form id="reverseForm" method="POST" action="{{ isset($transactionId) ? route('finance.transactions.reverse', $transactionId) : '#' }}">
                @csrf

                <div class="mb-4">
                    <div class="bg-red-50 border border-red-200 rounded-lg p-3 mb-4">
                        <p class="text-sm text-red-700 flex items-start">
                            <i class="fas fa-exclamation-triangle text-red-500 mr-2 mt-0.5"></i>
                            <span>This action will reverse the transaction and update all related records. This cannot be undone.</span>
                        </p>
                    </div>

                    <label class="text-sm font-medium text-gray-700 block mb-1">
                        Reason for Reversal <span class="text-red-500">*</span>
                    </label>
                    <textarea name="reason"
                              id="reverseReason"
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent transition duration-200"
                              rows="3"
                              placeholder="Please explain why you are reversing this transaction..."
                              required></textarea>
                </div>

                <div class="flex justify-end space-x-3">
                    <button type="button"
                            onclick="closeReverseModal()"
                            class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors font-medium text-gray-700">
                        Cancel
                    </button>

                    <button type="submit"
                            class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors font-semibold flex items-center">
                        <i class="fas fa-undo mr-2"></i>
                        Reverse Transaction
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function openReverseModal(transactionId) {
        const modal = document.getElementById('reverseModal');
        const form = document.getElementById('reverseForm');

        if (form && transactionId) {
            form.action = '/finance/transactions/' + transactionId + '/reverse';
        }

        if (modal) {
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        // Clear previous reason
        const reason = document.getElementById('reverseReason');
        if (reason) {
            reason.value = '';
        }
    }

    function closeReverseModal() {
        const modal = document.getElementById('reverseModal');

        if (modal) {
            modal.classList.add('hidden');
            document.body.style.overflow = 'auto';
        }
    }

    // Close on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeReverseModal();
        }
    });

    // Close on overlay click
    document.addEventListener('DOMContentLoaded', function() {
        const modal = document.getElementById('reverseModal');
        if (modal) {
            modal.addEventListener('click', function(e) {
                if (e.target === this || e.target.classList.contains('bg-black/50')) {
                    closeReverseModal();
                }
            });
        }
    });
</script>
@endpush
