@extends('ktvtc.cafeteria.layout.cafeterialayout')

@section('title', 'Card Scanner')
@section('subtitle', 'Scan student cards and process payments')

@section('header-actions')
<div class="flex gap-2">
    <button onclick="toggleCamera()" class="bg-purple-600 hover:bg-purple-700 text-white px-3 py-2 rounded-lg font-semibold flex items-center text-sm transition">
        <i class="fas fa-camera mr-2"></i> <span id="camera-toggle-text">Open Camera</span>
    </button>
    <button onclick="resetScanner()" class="bg-gray-600 hover:bg-gray-700 text-white px-3 py-2 rounded-lg font-semibold flex items-center text-sm transition">
        <i class="fas fa-undo mr-2"></i> Reset
    </button>
</div>
@endsection

@section('content')
<div class="max-w-5xl mx-auto">
    <div class="bg-white rounded-xl shadow-lg p-6">
        <!-- Scanner Header -->
        <div class="flex items-center gap-4 mb-6">
            <div class="bg-green-100 rounded-full p-3">
                <i class="fas fa-qrcode text-2xl text-green-600"></i>
            </div>
            <div>
                <h2 class="text-xl font-bold text-gray-800">Card Scanner</h2>
                <p class="text-sm text-gray-500">Scan student card QR code or enter admission number to process payment</p>
            </div>
        </div>

        <!-- Manual QR Entry - Fallback for dense QR codes -->
        <div class="bg-yellow-50 rounded-lg p-4 mb-4 border border-yellow-200">
            <div class="flex items-start gap-3">
                <i class="fas fa-exclamation-triangle text-xl text-yellow-600 mt-1"></i>
                <div class="flex-1">
                    <h4 class="font-semibold text-yellow-800">Having trouble scanning?</h4>
                    <p class="text-sm text-yellow-700 mt-1">
                        Use your phone's camera app to scan the QR code, then paste the result below:
                    </p>
                    <div class="flex gap-2 mt-2">
                        <input type="text"
                               id="manual-qr-input"
                               class="flex-1 px-4 py-2 border border-yellow-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent"
                               placeholder="Paste scanned QR code data here...">
                        <button onclick="processManualQR()" class="bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded-lg transition">
                            <i class="fas fa-check mr-1"></i> Process
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Camera QR Scanner - IMPROVED FOR DENSE QR CODES -->
        <div id="camera-container" class="hidden mb-6">
            <div class="relative bg-black rounded-lg overflow-hidden" style="min-height: 400px;">
                <video id="video" class="w-full h-auto" autoplay playsinline style="min-height: 400px; background: #000;"></video>
                <canvas id="canvas" class="hidden"></canvas>
                
                <!-- Camera Controls -->
                <div class="absolute bottom-4 left-0 right-0 flex justify-center space-x-4 z-10">
                    <button onclick="switchCamera()" class="bg-gray-700 hover:bg-gray-600 text-white px-4 py-2 rounded-lg text-sm transition">
                        <i class="fas fa-sync-alt mr-1"></i> Switch Camera
                    </button>
                    <button onclick="manualCaptureQR()" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg text-sm transition">
                        <i class="fas fa-camera mr-1"></i> Scan Now
                    </button>
                    <button onclick="toggleCamera()" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm transition">
                        <i class="fas fa-times mr-1"></i> Close
                    </button>
                </div>
                
                <!-- Camera Loading -->
                <div id="camera-loading" class="absolute inset-0 flex items-center justify-center bg-black bg-opacity-70 z-20">
                    <div class="text-white text-center">
                        <i class="fas fa-spinner fa-spin text-4xl mb-3 block"></i>
                        <p class="text-sm">Initializing camera...</p>
                        <p class="text-xs text-gray-400 mt-1">Please allow camera access</p>
                    </div>
                </div>
                
                <!-- Scan Overlay -->
                <div id="scan-overlay" class="absolute inset-0 flex items-center justify-center pointer-events-none z-5">
                    <div class="w-56 h-56 border-2 border-green-500 rounded-lg relative">
                        <div class="absolute -top-1 -left-1 w-4 h-4 border-t-2 border-l-2 border-green-500"></div>
                        <div class="absolute -top-1 -right-1 w-4 h-4 border-t-2 border-r-2 border-green-500"></div>
                        <div class="absolute -bottom-1 -left-1 w-4 h-4 border-b-2 border-l-2 border-green-500"></div>
                        <div class="absolute -bottom-1 -right-1 w-4 h-4 border-b-2 border-r-2 border-green-500"></div>
                        <div class="absolute inset-0 flex items-center justify-center">
                            <div class="text-green-500 text-sm font-semibold text-center">
                                <i class="fas fa-qrcode text-4xl block mb-2"></i>
                                Position QR Code Here
                            </div>
                        </div>
                        <!-- Scanning Line Animation -->
                        <div class="absolute left-0 right-0 h-0.5 bg-green-500 animate-scan-line"></div>
                    </div>
                </div>
            </div>
            
            <!-- Captured QR Preview -->
            <div id="qr-preview" class="hidden mt-3 bg-gray-50 p-3 rounded-lg flex items-center justify-between">
                <div class="flex items-center">
                    <i class="fas fa-qrcode text-2xl text-green-600 mr-3"></i>
                    <div>
                        <p class="text-sm font-medium text-gray-700">QR Code Detected</p>
                        <p id="qr-data-preview" class="text-xs text-gray-500">Processing...</p>
                    </div>
                </div>
                <button onclick="processQRData()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm transition">
                    <i class="fas fa-check mr-1"></i> Process
                </button>
            </div>
        </div>

        <!-- OR - Manual Search -->
        <div class="border-t border-b border-gray-200 py-4 my-4 flex items-center justify-center">
            <span class="bg-gray-100 px-4 py-1 rounded-full text-sm text-gray-500">— OR —</span>
        </div>

        <!-- Search/Scan Input -->
        <div class="bg-gray-50 rounded-lg p-4 mb-6">
            <div class="flex flex-col sm:flex-row gap-3">
                <div class="flex-1">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Enter Admission or Card Number</label>
                    <div class="flex gap-2">
                        <input type="text"
                               id="search-input"
                               class="flex-1 px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                               placeholder="e.g. 101 or CARD20260348"
                               autofocus>
                        <button onclick="lookupStudent()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2.5 rounded-lg transition whitespace-nowrap">
                            <i class="fas fa-search"></i> Find
                        </button>
                    </div>
                    <p class="text-xs text-gray-400 mt-1.5">Press <kbd class="px-1.5 py-0.5 bg-gray-200 rounded text-xs">Enter</kbd> to search</p>
                </div>
            </div>
        </div>

        <!-- Student Info Card -->
        <div id="student-info" class="hidden bg-gradient-to-r from-blue-50 to-purple-50 rounded-lg p-4 mb-6 border border-blue-200">
            <div class="flex flex-col sm:flex-row items-start gap-4">
                <div class="w-20 h-20 rounded-full bg-gray-200 overflow-hidden flex-shrink-0 border-2 border-white shadow">
                    <img id="student-photo" src="" alt="Student" class="w-full h-full object-cover hidden">
                    <div id="student-photo-placeholder" class="w-full h-full flex items-center justify-center bg-gray-300 text-gray-500 text-3xl">
                        <i class="fas fa-user"></i>
                    </div>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex flex-wrap items-start justify-between gap-2">
                        <div>
                            <h3 id="student-name" class="text-xl font-bold text-gray-800">-</h3>
                            <div class="flex flex-wrap gap-2 mt-1">
                                <span id="student-admission" class="text-sm text-gray-600">Admission: -</span>
                                <span id="student-class" class="text-sm text-gray-600">Class: -</span>
                            </div>
                        </div>
                        <div id="card-status-badge" class="text-sm">
                            <span class="px-3 py-1 bg-green-100 text-green-600 rounded-full font-medium">Active</span>
                        </div>
                    </div>
                    <div class="mt-2">
                        <p class="text-sm text-gray-600">Balance</p>
                        <p id="student-balance" class="text-2xl font-bold text-green-600">KES 0.00</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Purchase Form with Products -->
        <div id="purchase-form" class="hidden border-t pt-6">
            <h4 class="font-semibold text-gray-800 mb-4">Select Items</h4>

            <!-- Product Search & Filters -->
            <div class="flex flex-wrap gap-3 mb-4">
                <div class="flex-1 min-w-[200px]">
                    <input type="text"
                           id="product-search"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                           placeholder="Search products..."
                           onkeyup="filterProducts()">
                </div>
                <div>
                    <select id="category-filter" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent" onchange="filterProducts()">
                        <option value="">All Categories</option>
                    </select>
                </div>
                <div>
                    <label class="inline-flex items-center">
                        <input type="checkbox" id="in-stock-only" class="rounded border-gray-300 text-green-600 focus:ring-green-500" checked onchange="filterProducts()">
                        <span class="ml-2 text-sm text-gray-600">In Stock Only</span>
                    </label>
                </div>
                <button onclick="loadProducts()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition text-sm">
                    <i class="fas fa-sync-alt mr-1"></i> Refresh
                </button>
            </div>

            <!-- Products Grid -->
            <div id="products-grid" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3 max-h-96 overflow-y-auto p-2 bg-gray-50 rounded-lg">
                <div class="col-span-full text-center py-8 text-gray-400">
                    <i class="fas fa-spinner fa-spin text-2xl block mb-2"></i>
                    Loading products...
                </div>
            </div>

            <!-- Cart Summary -->
            <div id="cart-summary" class="hidden mt-4 bg-gray-100 p-4 rounded-lg">
                <div class="flex justify-between items-center">
                    <div>
                        <span class="font-medium">Items:</span>
                        <span id="cart-items-count" class="ml-1">0</span>
                    </div>
                    <div>
                        <span class="font-medium">Total:</span>
                        <span id="cart-total" class="text-2xl font-bold text-green-600 ml-2">KES 0.00</span>
                    </div>
                </div>
                <div class="flex flex-wrap gap-3 mt-3">
                    <button onclick="processCartPurchase()" class="flex-1 bg-green-600 hover:bg-green-700 text-white font-semibold py-3 rounded-lg transition flex items-center justify-center gap-2">
                        <i class="fas fa-check-circle"></i>
                        Confirm Payment
                    </button>
                    <button onclick="clearCart()" class="px-6 bg-gray-600 hover:bg-gray-700 text-white font-semibold py-3 rounded-lg transition">
                        <i class="fas fa-times"></i> Clear Cart
                    </button>
                </div>
            </div>

            <!-- Or Quick Amount (Legacy Support) -->
            <div class="mt-4 border-t pt-4">
                <p class="text-sm text-gray-500 mb-2">Or enter custom amount:</p>
                <div class="flex flex-wrap gap-2">
                    <input type="number"
                           id="custom-amount"
                           class="w-40 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                           placeholder="0.00"
                           min="1"
                           step="0.5">
                    <button onclick="processCustomAmount()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition text-sm">
                        <i class="fas fa-coins mr-1"></i> Pay Custom
                    </button>
                </div>
            </div>
        </div>

        <!-- Recent Transactions -->
        <div class="mt-8 border-t pt-6">
            <div class="flex justify-between items-center mb-3">
                <h4 class="font-semibold text-gray-800">Recent Transactions</h4>
                <span id="transaction-count" class="text-sm text-gray-500">0 transactions</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b text-gray-500">
                            <th class="text-left py-2 px-3">Time</th>
                            <th class="text-left py-2 px-3">Student</th>
                            <th class="text-left py-2 px-3">Items</th>
                            <th class="text-right py-2 px-3">Amount</th>
                            <th class="text-left py-2 px-3">Status</th>
                        </tr>
                    </thead>
                    <tbody id="transactions-list">
                        <tr>
                            <td colspan="5" class="text-center py-8 text-gray-400">
                                <i class="fas fa-shopping-cart text-3xl block mb-2"></i>
                                No transactions yet
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Toast Container -->
<div id="toast-container" class="fixed bottom-4 right-4 z-50 space-y-2"></div>

<!-- Loading Overlay -->
<div id="loadingOverlay" class="fixed inset-0 bg-black bg-opacity-60 z-50 flex items-center justify-center hidden">
    <div class="bg-white rounded-xl p-8 text-center max-w-sm">
        <div class="spinner mx-auto"></div>
        <p id="loadingMessage" class="mt-4 text-gray-700 font-medium">Processing...</p>
    </div>
</div>

<script>
let currentCardId = null;
let currentStudent = null;
let stream = null;
let facingMode = 'environment';
let capturedQRData = null;
let cart = [];
let products = [];
let isScanning = false;
let scanInterval = null;

// ============================================
// MANUAL QR ENTRY
// ============================================

function processManualQR() {
    const input = document.getElementById('manual-qr-input').value.trim();
    if (!input) {
        showToast('Please enter QR code data', 'warning');
        return;
    }
    capturedQRData = input;
    document.getElementById('qr-data-preview').textContent = 'Manual QR: ' + input.substring(0, 30) + '...';
    document.getElementById('qr-preview').classList.remove('hidden');
    showToast('✅ QR code entered manually!', 'success');
    setTimeout(() => processQRData(), 300);
}

// ============================================
// IMPROVED CAMERA QR SCANNER FOR DENSE CODES
// ============================================

async function toggleCamera() {
    const container = document.getElementById('camera-container');
    const btn = document.getElementById('camera-toggle-text');
    
    if (container.classList.contains('hidden')) {
        container.classList.remove('hidden');
        btn.textContent = 'Close Camera';
        await startCamera();
    } else {
        container.classList.add('hidden');
        btn.textContent = 'Open Camera';
        stopCamera();
    }
}

async function startCamera() {
    try {
        document.getElementById('camera-loading').classList.remove('hidden');
        document.getElementById('scan-overlay').classList.remove('hidden');
        
        // ✅ HIGHER RESOLUTION for dense QR codes
        const constraints = {
            video: {
                facingMode: facingMode,
                width: { ideal: 1920, min: 1280 },
                height: { ideal: 1080, min: 720 },
                aspectRatio: 1.7777777778
            },
            audio: false
        };
        
        stream = await navigator.mediaDevices.getUserMedia(constraints);
        const video = document.getElementById('video');
        video.srcObject = stream;
        await video.play();
        
        document.getElementById('camera-loading').classList.add('hidden');
        
        // ✅ Start continuous scanning
        startContinuousScan();
        
        showToast('📷 Camera ready - Position QR code clearly', 'info');
        
    } catch (error) {
        console.error('Camera error:', error);
        document.getElementById('camera-loading').classList.add('hidden');
        showToast('Unable to access camera. Please check permissions or use manual entry.', 'error');
        toggleCamera();
    }
}

function stopCamera() {
    if (stream) {
        stream.getTracks().forEach(track => track.stop());
        stream = null;
    }
    stopContinuousScan();
    const video = document.getElementById('video');
    video.srcObject = null;
}

function switchCamera() {
    facingMode = facingMode === 'environment' ? 'user' : 'environment';
    stopCamera();
    setTimeout(startCamera, 500);
}

// ✅ CONTINUOUS SCANNING with better quality
function startContinuousScan() {
    stopContinuousScan();
    isScanning = true;
    
    scanInterval = setInterval(() => {
        if (stream && stream.active && isScanning) {
            captureQR();
        }
    }, 1500);  // Scan every 1.5 seconds
}

function stopContinuousScan() {
    isScanning = false;
    if (scanInterval) {
        clearInterval(scanInterval);
        scanInterval = null;
    }
}

// ✅ CAPTURE with FULL resolution
function captureQR() {
    const video = document.getElementById('video');
    const canvas = document.getElementById('canvas');
    const context = canvas.getContext('2d');
    
    // Use full video resolution
    canvas.width = video.videoWidth || 1920;
    canvas.height = video.videoHeight || 1080;
    context.drawImage(video, 0, 0, canvas.width, canvas.height);
    
    // Get image as JPEG with high quality
    const imageData = canvas.toDataURL('image/jpeg', 0.95);
    
    // Send to server for decoding
    fetch('/api/qr/decode', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ image: imageData })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.qr_data) {
            // QR found - stop scanning and process
            stopContinuousScan();
            capturedQRData = data.qr_data;
            document.getElementById('qr-data-preview').textContent = 'QR: ' + data.qr_data.substring(0, 30) + '...';
            document.getElementById('qr-preview').classList.remove('hidden');
            document.getElementById('scan-overlay').classList.add('hidden');
            
            showToast('✅ QR Code detected!', 'success');
            
            // Auto-process after short delay
            setTimeout(() => processQRData(), 500);
        }
    })
    .catch(error => {
        // Silently continue scanning
        console.debug('Scan attempt failed, continuing...');
    });
}

// ✅ MANUAL CAPTURE (for when auto-scan isn't working)
function manualCaptureQR() {
    if (!stream || !stream.active) {
        showToast('Camera not active. Please open camera first.', 'warning');
        return;
    }
    showToast('📷 Scanning now...', 'info');
    captureQR();
}

function decodeQR(imageData) {
    // This is now handled by the captureQR function
    // Kept for compatibility
}

function processQRData() {
    if (!capturedQRData) {
        showToast('No QR code detected. Please scan again.', 'warning');
        return;
    }
    
    showLoading('Processing QR code...');
    
    fetch('/api/qr/scan', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ qr_data: capturedQRData })
    })
    .then(response => response.json())
    .then(data => {
        hideLoading();
        if (data.success) {
            displayStudent(data.data);
            document.getElementById('qr-preview').classList.add('hidden');
            document.getElementById('manual-qr-input').value = '';
            showToast('Student found via QR!', 'success');
        } else {
            showToast(data.message || 'Invalid QR code', 'error');
        }
    })
    .catch(error => {
        hideLoading();
        console.error('Error:', error);
        showToast('Error processing QR code', 'error');
    });
}

// ============================================
// SEARCH / LOOKUP
// ============================================

document.getElementById('search-input').addEventListener('keydown', function(e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        lookupStudent();
    }
});

function lookupStudent() {
    const query = document.getElementById('search-input').value.trim();
    if (!query) {
        showToast('Please enter a student admission or card number', 'warning');
        return;
    }

    showLoading('Searching for student...');

    fetch(`/cafeteria/scanner/lookup?query=${encodeURIComponent(query)}`, {
        method: 'GET',
        headers: {
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        hideLoading();
        if (data.success) {
            displayStudent(data.data);
        } else {
            showToast(data.message || 'Student not found', 'error');
        }
    })
    .catch(error => {
        hideLoading();
        console.error('Error:', error);
        showToast('Error searching for student', 'error');
    });
}

function displayStudent(student) {
    currentStudent = student;
    currentCardId = student.card_id;

    document.getElementById('student-name').textContent = student.student_name;
    document.getElementById('student-admission').textContent = 'Admission: ' + student.admission_number;
    document.getElementById('student-class').textContent = 'Class: ' + student.class;
    document.getElementById('student-balance').textContent = 'KES ' + parseFloat(student.balance).toFixed(2);

    const photoEl = document.getElementById('student-photo');
    const placeholderEl = document.getElementById('student-photo-placeholder');
    if (student.photo) {
        photoEl.src = student.photo;
        photoEl.classList.remove('hidden');
        placeholderEl.classList.add('hidden');
    } else {
        photoEl.classList.add('hidden');
        placeholderEl.classList.remove('hidden');
    }

    const badge = document.getElementById('card-status-badge');
    if (student.is_blocked) {
        badge.innerHTML = '<span class="px-3 py-1 bg-red-100 text-red-600 rounded-full font-medium">Blocked</span>';
    } else if (student.is_locked) {
        badge.innerHTML = '<span class="px-3 py-1 bg-yellow-100 text-yellow-600 rounded-full font-medium">Locked</span>';
    } else if (student.card_status === 'active') {
        badge.innerHTML = '<span class="px-3 py-1 bg-green-100 text-green-600 rounded-full font-medium">Active</span>';
    } else {
        badge.innerHTML = '<span class="px-3 py-1 bg-gray-100 text-gray-500 rounded-full font-medium">Inactive</span>';
    }

    document.getElementById('student-info').classList.remove('hidden');
    document.getElementById('purchase-form').classList.remove('hidden');

    loadProducts();
    loadTransactions(student.card_id);
}

// ============================================
// PRODUCTS
// ============================================

function loadProducts() {
    const grid = document.getElementById('products-grid');
    grid.innerHTML = `
        <div class="col-span-full text-center py-8 text-gray-400">
            <i class="fas fa-spinner fa-spin text-2xl block mb-2"></i>
            Loading products...
        </div>
    `;

    const params = new URLSearchParams();
    const categoryFilter = document.getElementById('category-filter').value;
    const searchTerm = document.getElementById('product-search').value;
    const inStockOnly = document.getElementById('in-stock-only').checked;

    if (categoryFilter) params.append('category_id', categoryFilter);
    if (searchTerm) params.append('search', searchTerm);
    if (inStockOnly) params.append('in_stock_only', 'true');

    fetch(`/cafeteria/scanner/products?${params.toString()}`, {
        method: 'GET',
        headers: {
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            products = data.products;
            renderProducts(products);
            renderCategories(data.categories || []);
        } else {
            grid.innerHTML = `
                <div class="col-span-full text-center py-8 text-red-400">
                    <i class="fas fa-exclamation-circle text-2xl block mb-2"></i>
                    Failed to load products
                </div>
            `;
        }
    })
    .catch(error => {
        console.error('Error loading products:', error);
        grid.innerHTML = `
            <div class="col-span-full text-center py-8 text-red-400">
                <i class="fas fa-exclamation-circle text-2xl block mb-2"></i>
                Error loading products
            </div>
        `;
    });
}

function renderProducts(products) {
    const grid = document.getElementById('products-grid');
    
    if (!products || products.length === 0) {
        grid.innerHTML = `
            <div class="col-span-full text-center py-8 text-gray-400">
                <i class="fas fa-box-open text-2xl block mb-2"></i>
                No products found
            </div>
        `;
        return;
    }

    grid.innerHTML = products.map(product => {
        const inCart = cart.find(item => item.product_id === product.id);
        const quantity = inCart ? inCart.quantity : 0;
        const isAvailable = product.is_available;
        
        return `
            <div class="bg-white rounded-lg border ${isAvailable ? 'border-gray-200 hover:border-green-400' : 'border-gray-200 opacity-60'} p-3 transition cursor-pointer ${isAvailable ? 'hover:shadow-md' : ''}" 
                 onclick="${isAvailable ? `addToCart(${product.id})` : ''}">
                <div class="flex items-center justify-between mb-1">
                    <span class="text-xs text-gray-400">${product.product_code || ''}</span>
                    ${!isAvailable ? '<span class="text-xs text-red-500 font-semibold">Out of Stock</span>' : ''}
                </div>
                <p class="font-medium text-sm text-gray-800 line-clamp-2">${product.product_name}</p>
                <p class="text-green-600 font-bold mt-1">KES ${parseFloat(product.selling_price).toFixed(2)}</p>
                ${product.track_inventory ? `<p class="text-xs text-gray-400">Stock: ${product.current_stock}</p>` : ''}
                ${inCart ? `<div class="mt-2 flex items-center justify-center gap-1">
                    <button onclick="event.stopPropagation(); updateCart(${product.id}, -1)" class="bg-gray-200 hover:bg-gray-300 rounded-full w-6 h-6 flex items-center justify-center text-sm font-bold">-</button>
                    <span class="font-semibold text-sm w-6 text-center">${quantity}</span>
                    <button onclick="event.stopPropagation(); updateCart(${product.id}, 1)" class="bg-gray-200 hover:bg-gray-300 rounded-full w-6 h-6 flex items-center justify-center text-sm font-bold">+</button>
                </div>` : ''}
            </div>
        `;
    }).join('');
}

function renderCategories(categories) {
    const select = document.getElementById('category-filter');
    const currentValue = select.value;
    
    select.innerHTML = '<option value="">All Categories</option>';
    categories.forEach(cat => {
        select.innerHTML += `<option value="${cat.id}">${cat.category_name}</option>`;
    });
    
    if (currentValue) {
        select.value = currentValue;
    }
}

function filterProducts() {
    const searchTerm = document.getElementById('product-search').value.toLowerCase();
    const categoryFilter = document.getElementById('category-filter').value;
    const inStockOnly = document.getElementById('in-stock-only').checked;

    let filtered = products;

    if (searchTerm) {
        filtered = filtered.filter(p => 
            p.product_name.toLowerCase().includes(searchTerm) || 
            (p.product_code && p.product_code.toLowerCase().includes(searchTerm))
        );
    }

    if (categoryFilter) {
        loadProducts();
        return;
    }

    if (inStockOnly) {
        filtered = filtered.filter(p => p.is_available);
    }

    renderProducts(filtered);
}

// ============================================
// CART
// ============================================

function addToCart(productId) {
    const product = products.find(p => p.id === productId);
    if (!product) return;

    if (!product.is_available) {
        showToast('Product is out of stock', 'warning');
        return;
    }

    const existing = cart.find(item => item.product_id === productId);
    if (existing) {
        existing.quantity += 1;
    } else {
        cart.push({
            product_id: product.id,
            product_name: product.product_name,
            quantity: 1,
            unit_price: product.selling_price,
            product_code: product.product_code
        });
    }

    updateCartUI();
    renderProducts(products);
    showToast(`${product.product_name} added to cart`, 'success');
}

function updateCart(productId, delta) {
    const index = cart.findIndex(item => item.product_id === productId);
    if (index === -1) return;

    cart[index].quantity += delta;
    if (cart[index].quantity <= 0) {
        cart.splice(index, 1);
    }

    updateCartUI();
    renderProducts(products);
}

function updateCartUI() {
    const summary = document.getElementById('cart-summary');
    const countEl = document.getElementById('cart-items-count');
    const totalEl = document.getElementById('cart-total');

    if (cart.length === 0) {
        summary.classList.add('hidden');
        return;
    }

    summary.classList.remove('hidden');
    const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
    const totalAmount = cart.reduce((sum, item) => sum + (item.unit_price * item.quantity), 0);

    countEl.textContent = totalItems;
    totalEl.textContent = 'KES ' + totalAmount.toFixed(2);
}

function clearCart() {
    cart = [];
    updateCartUI();
    renderProducts(products);
    showToast('Cart cleared', 'info');
}

// ============================================
// PROCESS PURCHASE
// ============================================

function processCartPurchase() {
    if (!currentCardId) {
        showToast('Please scan a card first', 'warning');
        return;
    }

    if (cart.length === 0) {
        showToast('Cart is empty', 'warning');
        return;
    }

    const totalAmount = cart.reduce((sum, item) => sum + (item.unit_price * item.quantity), 0);
    const availableBalance = parseFloat(currentStudent.balance);

    if (totalAmount > availableBalance) {
        showToast(`Insufficient balance. Available: KES ${availableBalance.toFixed(2)}, Required: KES ${totalAmount.toFixed(2)}`, 'error');
        return;
    }

    const items = cart.map(item => ({
        product_id: item.product_id,
        quantity: item.quantity
    }));

    const data = {
        card_id: currentCardId,
        items: items
    };

    showLoading('Processing payment...');

    fetch('/cafeteria/scanner/purchase', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        hideLoading();
        if (data.success) {
            const itemNames = cart.map(item => item.product_name).join(', ');
            showToast(`✅ Payment successful! ${itemNames} - KES ${totalAmount.toFixed(2)}. New balance: KES ${parseFloat(data.new_balance).toFixed(2)}`, 'success');
            
            currentStudent.balance = data.new_balance;
            document.getElementById('student-balance').textContent = 'KES ' + parseFloat(data.new_balance).toFixed(2);
            
            cart = [];
            updateCartUI();
            loadProducts();
            loadTransactions(currentCardId);
            
            if (data.transaction) {
                addTransaction(data.transaction);
            }
        } else {
            showToast(data.message || 'Payment failed', 'error');
        }
    })
    .catch(error => {
        hideLoading();
        console.error('Error:', error);
        showToast('Error processing payment', 'error');
    });
}

function processCustomAmount() {
    const amountInput = document.getElementById('custom-amount');
    const amount = parseFloat(amountInput.value);

    if (!currentCardId) {
        showToast('Please scan a card first', 'warning');
        return;
    }

    if (!amount || amount <= 0) {
        showToast('Please enter a valid amount', 'warning');
        return;
    }

    if (amount > currentStudent.balance) {
        showToast(`Insufficient balance. Available: KES ${parseFloat(currentStudent.balance).toFixed(2)}`, 'error');
        return;
    }

    const description = document.getElementById('product-search').value || 'Custom amount';
    
    const data = {
        card_id: currentCardId,
        amount: amount,
        description: description
    };

    showLoading('Processing custom payment...');

    fetch('/cafeteria/scanner/custom-purchase', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        hideLoading();
        if (data.success) {
            showToast(`✅ Payment successful! KES ${amount.toFixed(2)}. New balance: KES ${parseFloat(data.new_balance).toFixed(2)}`, 'success');
            
            currentStudent.balance = data.new_balance;
            document.getElementById('student-balance').textContent = 'KES ' + parseFloat(data.new_balance).toFixed(2);
            document.getElementById('custom-amount').value = '';
            
            if (data.transaction) {
                addTransaction(data.transaction);
                loadTransactions(currentCardId);
            }
        } else {
            showToast(data.message || 'Payment failed', 'error');
        }
    })
    .catch(error => {
        hideLoading();
        console.error('Error:', error);
        showToast('Error processing payment', 'error');
    });
}

// ============================================
// TRANSACTIONS
// ============================================

function loadTransactions(cardId) {
    fetch(`/cafeteria/scanner/transactions?card_id=${cardId}`, {
        method: 'GET',
        headers: {
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            renderTransactions(data.transactions);
        }
    })
    .catch(error => console.error('Error loading transactions:', error));
}

function renderTransactions(transactions) {
    const list = document.getElementById('transactions-list');
    const count = document.getElementById('transaction-count');

    if (!transactions || transactions.length === 0) {
        list.innerHTML = `
            <tr>
                <td colspan="5" class="text-center py-8 text-gray-400">
                    <i class="fas fa-shopping-cart text-3xl block mb-2"></i>
                    No transactions yet
                </td>
            </tr>
        `;
        count.textContent = '0 transactions';
        return;
    }

    count.textContent = transactions.length + ' transactions';

    list.innerHTML = transactions.map(t => `
        <tr class="border-b hover:bg-gray-50">
            <td class="py-2 px-3 text-gray-600">${new Date(t.created_at).toLocaleTimeString()}</td>
            <td class="py-2 px-3 font-medium">${currentStudent ? currentStudent.student_name : '-'}</td>
            <td class="py-2 px-3 text-gray-600">${t.description || 'Purchase'}</td>
            <td class="py-2 px-3 text-right font-medium text-red-600">-KES ${parseFloat(t.amount).toFixed(2)}</td>
            <td class="py-2 px-3">
                <span class="px-2 py-1 bg-green-100 text-green-600 rounded-full text-xs">Completed</span>
            </td>
        </tr>
    `).join('');
}

function addTransaction(transaction) {
    const list = document.getElementById('transactions-list');
    const count = document.getElementById('transaction-count');

    const noDataRow = list.querySelector('td[colspan="5"]');
    if (noDataRow && noDataRow.closest('tr')) {
        list.innerHTML = '';
    }

    const row = document.createElement('tr');
    row.className = 'border-b hover:bg-gray-50';
    row.innerHTML = `
        <td class="py-2 px-3 text-gray-600">${new Date().toLocaleTimeString()}</td>
        <td class="py-2 px-3 font-medium">${currentStudent ? currentStudent.student_name : '-'}</td>
        <td class="py-2 px-3 text-gray-600">${transaction.description || 'Purchase'}</td>
        <td class="py-2 px-3 text-right font-medium text-red-600">-KES ${parseFloat(transaction.amount).toFixed(2)}</td>
        <td class="py-2 px-3">
            <span class="px-2 py-1 bg-green-100 text-green-600 rounded-full text-xs">Completed</span>
        </td>
    `;
    list.prepend(row);

    const currentCount = parseInt(count.textContent) || 0;
    count.textContent = (currentCount + 1) + ' transactions';
}

// ============================================
// RESET
// ============================================

function resetScanner() {
    document.getElementById('student-info').classList.add('hidden');
    document.getElementById('purchase-form').classList.add('hidden');
    document.getElementById('search-input').value = '';
    document.getElementById('custom-amount').value = '';
    document.getElementById('product-search').value = '';
    document.getElementById('manual-qr-input').value = '';
    document.getElementById('qr-preview').classList.add('hidden');
    document.getElementById('cart-summary').classList.add('hidden');
    
    cart = [];
    products = [];
    currentCardId = null;
    currentStudent = null;
    capturedQRData = null;
    
    stopContinuousScan();
    if (!document.getElementById('camera-container').classList.contains('hidden')) {
        toggleCamera();
    }
    
    document.getElementById('search-input').focus();
}

// ============================================
// TOAST NOTIFICATIONS
// ============================================

function showToast(message, type = 'info') {
    const container = document.getElementById('toast-container');
    const toast = document.createElement('div');
    const colors = {
        success: 'bg-green-500',
        error: 'bg-red-500',
        warning: 'bg-yellow-500',
        info: 'bg-blue-500'
    };
    const icons = {
        success: 'fa-check-circle',
        error: 'fa-exclamation-circle',
        warning: 'fa-exclamation-triangle',
        info: 'fa-info-circle'
    };

    toast.className = `${colors[type] || colors.info} text-white px-4 py-3 rounded-lg shadow-lg flex items-center transition-all duration-300 transform translate-x-full`;
    toast.innerHTML = `
        <i class="fas ${icons[type] || icons.info} mr-2"></i>
        <span>${message}</span>
        <button onclick="this.parentElement.remove()" class="ml-4 text-white hover:text-gray-200">
            <i class="fas fa-times"></i>
        </button>
    `;
    container.appendChild(toast);

    setTimeout(() => toast.classList.remove('translate-x-full'), 10);
    setTimeout(() => {
        toast.classList.add('translate-x-full');
        setTimeout(() => toast.remove(), 300);
    }, 5000);
}

// ============================================
// LOADING
// ============================================

function showLoading(message = 'Processing...') {
    const overlay = document.getElementById('loadingOverlay');
    const messageEl = document.getElementById('loadingMessage');
    if (overlay) {
        if (messageEl) messageEl.textContent = message;
        overlay.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }
}

function hideLoading() {
    const overlay = document.getElementById('loadingOverlay');
    if (overlay) {
        overlay.style.display = 'none';
        document.body.style.overflow = '';
    }
}

// ============================================
// KEYBOARD SHORTCUTS
// ============================================

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        resetScanner();
    }
    if (e.key === 'Enter' && (e.ctrlKey || e.metaKey)) {
        if (currentCardId && cart.length > 0) {
            processCartPurchase();
        }
    }
});

// ============================================
// INITIALIZE
// ============================================

document.addEventListener('DOMContentLoaded', function() {
    setTimeout(() => document.getElementById('search-input').focus(), 100);
});
</script>

<style>
kbd {
    display: inline-block;
    padding: 0.1rem 0.4rem;
    font-size: 0.75rem;
    font-weight: 600;
    line-height: 1.4;
    color: #333;
    background-color: #f0f0f0;
    border: 1px solid #ccc;
    border-radius: 3px;
    box-shadow: 0 1px 0 rgba(0,0,0,0.1);
}
#loadingOverlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.6);
    z-index: 9999;
    align-items: center;
    justify-content: center;
}
.spinner {
    width: 40px;
    height: 40px;
    border: 4px solid #f3f3f3;
    border-top: 4px solid #E63946;
    border-radius: 50%;
    animation: spin 0.8s linear infinite;
    margin: 0 auto 10px;
}
@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
#toast-container > div {
    transition: all 0.3s ease;
    min-width: 250px;
    max-width: 500px;
}
#toast-container > div.translate-x-full {
    transform: translateX(100%);
}
#video {
    background: #000;
    min-height: 400px;
    max-height: 500px;
    object-fit: cover;
}
#scan-overlay {
    animation: pulse-scan 2s infinite;
}
@keyframes pulse-scan {
    0% { opacity: 0.8; }
    50% { opacity: 1; }
    100% { opacity: 0.8; }
}
@keyframes scan-line {
    0% { top: 0; }
    100% { top: 100%; }
}
.animate-scan-line {
    animation: scan-line 2s linear infinite;
    top: 0;
}
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
#products-grid {
    scrollbar-width: thin;
}
#products-grid::-webkit-scrollbar {
    width: 6px;
}
#products-grid::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 3px;
}
#products-grid::-webkit-scrollbar-thumb {
    background: #ccc;
    border-radius: 3px;
}
#products-grid::-webkit-scrollbar-thumb:hover {
    background: #aaa;
}
</style>
@endsection