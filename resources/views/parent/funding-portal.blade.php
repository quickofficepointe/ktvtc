<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fund {{ $student->full_name }}'s Card - Kenswed Senior School</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
        * { font-family: 'Inter', sans-serif; }
        
        .loading-spinner {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255,255,255,.3);
            border-radius: 50%;
            border-top-color: #fff;
            animation: spin 1s ease-in-out infinite;
        }
        @keyframes spin { to { transform: rotate(360deg); } }
        
        .balance-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .balance-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        }
        
        .fund-btn {
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        .fund-btn:hover:not(:disabled) { transform: scale(1.02); }
        .fund-btn:disabled { opacity: 0.6; cursor: not-allowed; }
        
        .toast {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            max-width: 450px;
            animation: slideInRight 0.5s ease;
            box-shadow: 0 10px 40px rgba(0,0,0,0.15);
        }
        @keyframes slideInRight {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        .toast.hide {
            animation: slideOutRight 0.5s ease forwards;
        }
        @keyframes slideOutRight {
            from { transform: translateX(0); opacity: 1; }
            to { transform: translateX(100%); opacity: 0; }
        }
        
        .status-pending {
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.6; }
            100% { opacity: 1; }
        }
        
        .amount-btn {
            transition: all 0.2s ease;
            cursor: pointer;
        }
        .amount-btn:active { transform: scale(0.95); }
        .amount-btn.selected { background: #3B82F6 !important; color: white !important; }
        .amount-btn.green.selected { background: #16A34A !important; color: white !important; }
        
        .transaction-row:hover { background-color: #f9fafb; }
        
        @media (max-width: 640px) {
            .container { padding-left: 12px; padding-right: 12px; }
            .balance-card { padding: 16px !important; }
            .balance-card h2 { font-size: 1.5rem !important; }
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen">

    <!-- Toast Notification -->
    <div id="toast" class="toast hidden">
        <div id="toast-content" class="bg-white rounded-lg shadow-lg p-4 border-l-4">
            <div class="flex items-center">
                <div id="toast-icon" class="flex-shrink-0">
                    <i class="fas fa-check-circle text-green-500 text-xl"></i>
                </div>
                <div class="ml-3 flex-1">
                    <p id="toast-message" class="text-sm text-gray-700">Success!</p>
                </div>
                <button onclick="hideToast()" class="ml-2 text-gray-400 hover:text-gray-600 transition">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    </div>

    <div class="container mx-auto px-4 py-6 max-w-5xl">
        <!-- Header -->
        <div class="bg-gradient-to-r from-red-700 to-red-800 rounded-2xl shadow-xl p-6 mb-8 text-white">
            <div class="flex flex-col md:flex-row items-center justify-between">
                <div class="flex items-center">
                    <img src="{{ asset('Assets/images/Kenswed_logo.png') }}" 
                         alt="Kenswed Senior School" 
                         class="w-16 h-16 rounded-full bg-white p-1 mr-4 shadow-lg">
                    <div>
                        <h1 class="text-2xl md:text-3xl font-bold">Card Funding Portal</h1>
                        <p class="text-red-100 text-sm">Kenswed Senior School</p>
                    </div>
                </div>
                <div class="mt-4 md:mt-0 text-right">
                    <p class="text-sm text-red-100">Student Portal</p>
                    <p class="text-lg font-semibold">{{ $student->full_name }}</p>
                    <p class="text-sm text-red-100">Admission: {{ $student->admission_number }}</p>
                </div>
            </div>
        </div>

        <!-- Student Info -->
        <div class="bg-white rounded-2xl shadow-lg p-6 mb-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="flex items-center">
                    <div class="bg-red-100 rounded-full p-3 mr-3">
                        <i class="fas fa-user-graduate text-red-700 text-xl"></i>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Student Name</p>
                        <p class="font-semibold text-gray-800">{{ $student->full_name }}</p>
                    </div>
                </div>
                <div class="flex items-center">
                    <div class="bg-red-100 rounded-full p-3 mr-3">
                        <i class="fas fa-users text-red-700 text-xl"></i>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Class</p>
                        <p class="font-semibold text-gray-800">{{ $student->class }}</p>
                    </div>
                </div>
                <div class="flex items-center">
                    <div class="bg-red-100 rounded-full p-3 mr-3">
                        <i class="fas fa-id-card text-red-700 text-xl"></i>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Admission Number</p>
                        <p class="font-semibold text-gray-800">{{ $student->admission_number }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Balance Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <!-- Transport Balance -->
            <div class="balance-card bg-gradient-to-br from-blue-500 to-blue-700 rounded-2xl shadow-lg p-6 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-blue-100 text-sm">Transport Balance</p>
                        <p class="text-3xl font-bold mt-1" id="transportBalance">
                            KES {{ number_format($card->balance ?? 0, 2) }}
                        </p>
                        <p class="text-blue-200 text-xs mt-1">
                            <i class="fas fa-history mr-1"></i>
                            Total Funded: KES {{ number_format($totalFunded ?? 0, 2) }}
                        </p>
                    </div>
                    <div class="bg-white/20 rounded-full p-3">
                        <i class="fas fa-bus text-3xl"></i>
                    </div>
                </div>
                <div class="mt-4">
                    <div class="w-full bg-blue-900/30 rounded-full h-2">
                        <div class="bg-white rounded-full h-2 transition-all duration-1000" 
                             style="width: {{ min(($card->balance ?? 0) / 5000 * 100, 100) }}%">
                        </div>
                    </div>
                    <p class="text-blue-200 text-xs mt-1">
                        {{ ($card->balance ?? 0) > 0 ? 'Available for transport' : '⚠️ Low balance' }}
                    </p>
                </div>
            </div>

            <!-- Canteen Balance -->
            <div class="balance-card bg-gradient-to-br from-green-500 to-green-700 rounded-2xl shadow-lg p-6 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-green-100 text-sm">Canteen Balance</p>
                        <p class="text-3xl font-bold mt-1" id="canteenBalance">
                            KES {{ number_format($card->canteen_balance ?? 0, 2) }}
                        </p>
                        <p class="text-green-200 text-xs mt-1">
                            <i class="fas fa-history mr-1"></i>
                            Total Spent: KES {{ number_format($totalSpent ?? 0, 2) }}
                        </p>
                    </div>
                    <div class="bg-white/20 rounded-full p-3">
                        <i class="fas fa-utensils text-3xl"></i>
                    </div>
                </div>
                <div class="mt-4">
                    <div class="w-full bg-green-900/30 rounded-full h-2">
                        <div class="bg-white rounded-full h-2 transition-all duration-1000" 
                             style="width: {{ min(($card->canteen_balance ?? 0) / 5000 * 100, 100) }}%">
                        </div>
                    </div>
                    <p class="text-green-200 text-xs mt-1">
                        {{ ($card->canteen_balance ?? 0) > 0 ? 'Available for meals' : '⚠️ Low balance' }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Recent Transactions -->
        @if($recentTransactions->count() > 0)
        <div class="bg-white rounded-2xl shadow-lg p-6 mb-8">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">
                <i class="fas fa-history text-red-700 mr-2"></i>
                Recent Transactions
            </h3>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-200">
                            <th class="text-left py-2 px-3 text-gray-600 font-medium">Date</th>
                            <th class="text-left py-2 px-3 text-gray-600 font-medium">Type</th>
                            <th class="text-right py-2 px-3 text-gray-600 font-medium">Amount</th>
                            <th class="text-right py-2 px-3 text-gray-600 font-medium">Balance</th>
                            <th class="text-left py-2 px-3 text-gray-600 font-medium">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentTransactions as $transaction)
                        <tr class="transaction-row border-b border-gray-100">
                            <td class="py-2 px-3 text-gray-700">
                                {{ $transaction->created_at->format('d/m/Y H:i') }}
                            </td>
                            <td class="py-2 px-3">
                                <span class="px-2 py-1 rounded-full text-xs font-medium 
                                    {{ $transaction->transaction_type == 'funding' ? 'bg-green-100 text-green-700' : 'bg-blue-100 text-blue-700' }}">
                                    <i class="fas {{ $transaction->transaction_type == 'funding' ? 'fa-arrow-down' : 'fa-arrow-up' }} mr-1"></i>
                                    {{ ucfirst($transaction->transaction_type) }}
                                </span>
                            </td>
                            <td class="text-right py-2 px-3 font-medium text-gray-700">
                                {{ $transaction->transaction_type == 'funding' ? '+' : '-' }}
                                KES {{ number_format($transaction->amount, 2) }}
                            </td>
                            <td class="text-right py-2 px-3 text-gray-700">
                                KES {{ number_format($transaction->balance_after, 2) }}
                            </td>
                            <td class="py-2 px-3">
                                <span class="px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-700">
                                    <i class="fas fa-check-circle mr-1"></i>
                                    Completed
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        <!-- Funding Options -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Fund Transport -->
            <div class="bg-white rounded-2xl shadow-lg p-6">
                <div class="flex items-center mb-4">
                    <div class="bg-blue-100 rounded-full p-3 mr-3">
                        <i class="fas fa-bus text-blue-700 text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800">Fund Transport</h3>
                        <p class="text-xs text-gray-500">Add money for bus/shuttle fare</p>
                    </div>
                </div>
                
                <form id="transportForm" class="space-y-4">
                    @csrf
                    <input type="hidden" name="student_id" value="{{ $student->id }}">
                    <input type="hidden" name="funding_type" value="transport">
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Amount (KES)</label>
                        <div class="flex gap-2 flex-wrap mb-2">
                            <button type="button" class="amount-btn bg-gray-100 hover:bg-blue-100 text-gray-700 px-4 py-1.5 rounded-full text-sm font-medium transition" data-amount="100">100</button>
                            <button type="button" class="amount-btn bg-gray-100 hover:bg-blue-100 text-gray-700 px-4 py-1.5 rounded-full text-sm font-medium transition" data-amount="200">200</button>
                            <button type="button" class="amount-btn bg-gray-100 hover:bg-blue-100 text-gray-700 px-4 py-1.5 rounded-full text-sm font-medium transition" data-amount="500">500</button>
                            <button type="button" class="amount-btn bg-gray-100 hover:bg-blue-100 text-gray-700 px-4 py-1.5 rounded-full text-sm font-medium transition" data-amount="1000">1000</button>
                            <button type="button" class="amount-btn bg-gray-100 hover:bg-blue-100 text-gray-700 px-4 py-1.5 rounded-full text-sm font-medium transition" data-amount="2000">2000</button>
                            <button type="button" class="amount-btn bg-gray-100 hover:bg-blue-100 text-gray-700 px-4 py-1.5 rounded-full text-sm font-medium transition" data-amount="5000">5000</button>
                        </div>
                        <input type="number" 
                               id="transportAmount" 
                               name="amount" 
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                               placeholder="Enter amount (min 10)"
                               min="10"
                               max="50000"
                               required>
                        <p class="text-xs text-gray-500 mt-1">Minimum: KES 10, Maximum: KES 50,000</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">M-Pesa Phone Number</label>
                        <input type="tel" 
                               id="transportPhone" 
                               name="phone_number" 
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                               placeholder="0712345678"
                               pattern="[0-9]{10,12}"
                               value="{{ $student->parent_phone }}"
                               required>
                        <p class="text-xs text-gray-500 mt-1">Enter the phone number registered with M-Pesa</p>
                    </div>
                    
                    <button type="submit" 
                            class="fund-btn w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 rounded-xl transition-all duration-200 flex items-center justify-center">
                        <i class="fas fa-mobile-alt mr-2"></i>
                        <span>Fund Transport</span>
                    </button>
                </form>
            </div>

            <!-- Fund Canteen -->
            <div class="bg-white rounded-2xl shadow-lg p-6">
                <div class="flex items-center mb-4">
                    <div class="bg-green-100 rounded-full p-3 mr-3">
                        <i class="fas fa-utensils text-green-700 text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800">Fund Canteen</h3>
                        <p class="text-xs text-gray-500">Add money for meals and snacks</p>
                    </div>
                </div>
                
                <form id="canteenForm" class="space-y-4">
                    @csrf
                    <input type="hidden" name="student_id" value="{{ $student->id }}">
                    <input type="hidden" name="funding_type" value="canteen">
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Amount (KES)</label>
                        <div class="flex gap-2 flex-wrap mb-2">
                            <button type="button" class="amount-btn green bg-gray-100 hover:bg-green-100 text-gray-700 px-4 py-1.5 rounded-full text-sm font-medium transition" data-amount="100">100</button>
                            <button type="button" class="amount-btn green bg-gray-100 hover:bg-green-100 text-gray-700 px-4 py-1.5 rounded-full text-sm font-medium transition" data-amount="200">200</button>
                            <button type="button" class="amount-btn green bg-gray-100 hover:bg-green-100 text-gray-700 px-4 py-1.5 rounded-full text-sm font-medium transition" data-amount="500">500</button>
                            <button type="button" class="amount-btn green bg-gray-100 hover:bg-green-100 text-gray-700 px-4 py-1.5 rounded-full text-sm font-medium transition" data-amount="1000">1000</button>
                            <button type="button" class="amount-btn green bg-gray-100 hover:bg-green-100 text-gray-700 px-4 py-1.5 rounded-full text-sm font-medium transition" data-amount="2000">2000</button>
                            <button type="button" class="amount-btn green bg-gray-100 hover:bg-green-100 text-gray-700 px-4 py-1.5 rounded-full text-sm font-medium transition" data-amount="5000">5000</button>
                        </div>
                        <input type="number" 
                               id="canteenAmount" 
                               name="amount" 
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-transparent transition"
                               placeholder="Enter amount (min 10)"
                               min="10"
                               max="50000"
                               required>
                        <p class="text-xs text-gray-500 mt-1">Minimum: KES 10, Maximum: KES 50,000</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">M-Pesa Phone Number</label>
                        <input type="tel" 
                               id="canteenPhone" 
                               name="phone_number" 
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-transparent transition"
                               placeholder="0712345678"
                               pattern="[0-9]{10,12}"
                               value="{{ $student->parent_phone }}"
                               required>
                        <p class="text-xs text-gray-500 mt-1">Enter the phone number registered with M-Pesa</p>
                    </div>
                    
                    <button type="submit" 
                            class="fund-btn w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-3 rounded-xl transition-all duration-200 flex items-center justify-center">
                        <i class="fas fa-mobile-alt mr-2"></i>
                        <span>Fund Canteen</span>
                    </button>
                </form>
            </div>
        </div>

        <!-- Pending Funding Status -->
        @if($pendingFunding)
        <div class="mt-6 bg-yellow-50 border-2 border-yellow-200 rounded-2xl p-4 status-pending">
            <div class="flex items-center">
                <div class="bg-yellow-100 rounded-full p-2 mr-3">
                    <i class="fas fa-spinner fa-spin text-yellow-700 text-xl"></i>
                </div>
                <div class="flex-1">
                    <p class="font-semibold text-yellow-800">Pending Funding Request</p>
                    <p class="text-sm text-yellow-700">
                        There is a pending funding request of <strong>KES {{ number_format($pendingFunding->amount, 2) }}</strong> 
                        ({{ $pendingFunding->funding_type == 'T' ? 'Transport' : 'Canteen' }})
                    </p>
                    <p class="text-xs text-yellow-600 mt-1">
                        Status: {{ ucfirst($pendingFunding->status) }} - Please wait for M-Pesa confirmation
                    </p>
                </div>
                <button onclick="checkPendingStatus('{{ $pendingFunding->checkout_request_id }}')" 
                        class="bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition flex items-center">
                    <i class="fas fa-sync-alt mr-2"></i>
                    Check Status
                </button>
            </div>
        </div>
        @endif

        <!-- Quick Tips -->
        <div class="mt-8 bg-blue-50 border-2 border-blue-200 rounded-2xl p-4">
            <h4 class="font-semibold text-blue-800 mb-2 flex items-center">
                <i class="fas fa-info-circle mr-2"></i>
                Quick Tips
            </h4>
            <ul class="text-sm text-blue-700 space-y-1">
                <li><i class="fas fa-check-circle text-green-500 mr-2"></i> You'll receive an M-Pesa prompt on your phone after clicking "Fund"</li>
                <li><i class="fas fa-check-circle text-green-500 mr-2"></i> Enter your M-Pesa PIN to complete the transaction</li>
                <li><i class="fas fa-check-circle text-green-500 mr-2"></i> Funds reflect immediately after successful payment</li>
                <li><i class="fas fa-check-circle text-green-500 mr-2"></i> Check transaction history for all past funding</li>
                <li><i class="fas fa-check-circle text-green-500 mr-2"></i> For help, contact the school finance office</li>
            </ul>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-red-700 text-white py-6 mt-12">
        <div class="container mx-auto px-4 text-center">
            <p class="text-sm">&copy; {{ date('Y') }} Kenswed Senior School. All rights reserved.</p>
            <p class="text-xs text-red-200 mt-1">Powered by Quick Office Pointe</p>
        </div>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Amount quick-select buttons
            document.querySelectorAll('.amount-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const amount = this.dataset.amount;
                    const parentForm = this.closest('form');
                    const amountInput = parentForm.querySelector('input[name="amount"]');
                    amountInput.value = amount;
                    
                    // Highlight selected
                    const siblings = this.parentElement.querySelectorAll('.amount-btn');
                    siblings.forEach(s => s.classList.remove('selected'));
                    this.classList.add('selected');
                    
                    amountInput.focus();
                });
            });

            // Auto-fill parent phone if available
            const parentPhone = "{{ $student->parent_phone }}";
            if (parentPhone) {
                document.getElementById('transportPhone').value = parentPhone;
                document.getElementById('canteenPhone').value = parentPhone;
            }
        });

        // Transport Form Submit
        document.getElementById('transportForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const submitBtn = this.querySelector('button[type="submit"]');
            handleFundingSubmit(this, submitBtn, 'transport');
        });

        // Canteen Form Submit
        document.getElementById('canteenForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const submitBtn = this.querySelector('button[type="submit"]');
            handleFundingSubmit(this, submitBtn, 'canteen');
        });

        function handleFundingSubmit(form, submitBtn, type) {
            const formData = new FormData(form);
            const endpoint = type === 'transport' 
                ? '/parent/fund/transport' 
                : '/parent/fund/canteen';
            
            // Get CSRF token from meta tag
            const csrfToken = document.querySelector('meta[name="csrf-token"]');
            const token = csrfToken ? csrfToken.content : '';
            
            // ✅ Convert FormData to JSON
            const data = {};
            formData.forEach((value, key) => {
                data[key] = value;
            });
            
            // Show loading state
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<span class="loading-spinner mr-2"></span> Processing...';
            submitBtn.disabled = true;

            console.log('Sending request to:', endpoint);
            console.log('Data:', data);

            fetch(endpoint, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(data)
            })
            .then(response => {
                console.log('Response status:', response.status);
                if (!response.ok) {
                    return response.text().then(text => {
                        console.error('Error response:', text);
                        throw new Error(`HTTP ${response.status}`);
                    });
                }
                return response.json();
            })
            .then(data => {
                console.log('Response data:', data);
                if (data.success) {
                    showToast('success', data.message);
                    if (data.checkout_request_id) {
                        pollStatus(data.checkout_request_id);
                    }
                } else {
                    showToast('error', data.message || 'Something went wrong');
                }
            })
            .catch(error => {
                console.error('Fetch error:', error);
                showToast('error', 'Network error: ' + error.message);
            })
            .finally(() => {
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            });
        }

        // Poll for status
        function pollStatus(checkoutRequestId) {
            let attempts = 0;
            const maxAttempts = 30;
            
            const interval = setInterval(() => {
                attempts++;
                
                fetch('/parent/fund/status/' + checkoutRequestId)
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'completed') {
                        clearInterval(interval);
                        showToast('success', '✅ Payment successful! Card has been funded.');
                        setTimeout(() => window.location.reload(), 3000);
                    } else if (data.status === 'failed') {
                        clearInterval(interval);
                        showToast('error', '❌ Payment failed: ' + (data.result_description || 'Unknown error'));
                    } else {
                        if (attempts % 3 === 0) {
                            showToast('info', '⏳ Waiting for payment confirmation...');
                        }
                    }
                })
                .catch(error => {
                    console.error('Status check error:', error);
                    if (attempts >= maxAttempts) {
                        clearInterval(interval);
                        showToast('info', '⏳ Payment still processing. Check status later.');
                    }
                });
                
                if (attempts >= maxAttempts) {
                    clearInterval(interval);
                    showToast('info', '⏳ Payment taking longer than expected. Check status later.');
                }
            }, 5000);
        }

        // Check pending status
        function checkPendingStatus(checkoutRequestId) {
            showToast('info', 'Checking payment status...');
            pollStatus(checkoutRequestId);
        }

        // Toast notification
        function showToast(type, message) {
            const toast = document.getElementById('toast');
            const toastMessage = document.getElementById('toast-message');
            const toastIcon = document.getElementById('toast-icon');
            const toastContent = document.getElementById('toast-content');
            
            // Reset classes
            toastContent.className = 'bg-white rounded-lg shadow-lg p-4 border-l-4';
            
            const icons = {
                success: { icon: 'fa-check-circle', color: 'text-green-500', border: 'border-green-500' },
                error: { icon: 'fa-exclamation-circle', color: 'text-red-500', border: 'border-red-500' },
                info: { icon: 'fa-info-circle', color: 'text-blue-500', border: 'border-blue-500' }
            };
            
            const selected = icons[type] || icons.info;
            toastContent.className += ` ${selected.border}`;
            toastIcon.innerHTML = `<i class="fas ${selected.icon} ${selected.color} text-xl"></i>`;
            
            toastMessage.textContent = message;
            toast.classList.remove('hidden', 'hide');
            
            clearTimeout(window.toastTimeout);
            window.toastTimeout = setTimeout(() => {
                hideToast();
            }, 8000);
        }

        function hideToast() {
            const toast = document.getElementById('toast');
            toast.classList.add('hide');
            setTimeout(() => {
                toast.classList.add('hidden');
                toast.classList.remove('hide');
            }, 500);
        }

        // Click outside to close
        document.getElementById('toast').addEventListener('click', hideToast);
    </script>
</body>
</html>