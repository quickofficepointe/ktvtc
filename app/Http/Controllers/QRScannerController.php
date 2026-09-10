<?php
// app/Http/Controllers/QRScannerController.php

namespace App\Http\Controllers;

use App\Models\CardAccount;
use App\Models\HighSchoolStudent;
use App\Models\CardTransaction;
use App\Models\CardAuditLog;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class QRScannerController extends Controller
{
    /**
     * Show the scanner interface
     */
    public function index()
    {
        return view('ktvtc.cafeteria.scanner.index');
    }

    /**
     * Get available products for the scanner
     */
    public function getProducts(Request $request)
    {
        try {
            $user = auth()->user();
            $shopId = $user->shop_id ?? 1;

            $query = Product::with(['category'])
                ->where('is_active', true)
                ->where('shop_id', $shopId);

            // Filter by category
            if ($request->filled('category_id')) {
                $query->where('category_id', $request->category_id);
            }

            // Search
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('product_name', 'like', "%{$search}%")
                      ->orWhere('product_code', 'like', "%{$search}%");
                });
            }

            // In stock only
            if ($request->boolean('in_stock_only')) {
                $query->where(function($q) {
                    $q->where('track_inventory', false)
                      ->orWhere('current_stock', '>', 0);
                });
            }

            $products = $query->orderBy('product_name')
                ->get()
                ->map(function($product) {
                    return [
                        'id' => $product->id,
                        'product_code' => $product->product_code,
                        'product_name' => $product->product_name,
                        'selling_price' => (float) $product->selling_price,
                        'current_stock' => (int) $product->current_stock,
                        'track_inventory' => (bool) $product->track_inventory,
                        'unit' => $product->unit,
                        'image' => $product->image ? asset('storage/' . $product->image) : null,
                        'category_name' => $product->category ? $product->category->category_name : null,
                        'is_available' => !$product->track_inventory || $product->current_stock > 0
                    ];
                });

            // Get categories for filter
            $categories = \App\Models\ProductCategory::where('is_active', true)
                ->where('business_section_id', auth()->user()->business_section_id ?? 1)
                ->orderBy('category_name')
                ->get(['id', 'category_name']);

            return response()->json([
                'success' => true,
                'products' => $products,
                'categories' => $categories
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching scanner products: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to load products'
            ], 500);
        }
    }
/**
 * Process a custom amount purchase (legacy support)
 */
public function processCustomPurchase(Request $request)
{
    $request->validate([
        'card_id' => 'required|exists:card_accounts,id',
        'amount' => 'required|numeric|min:1',
        'description' => 'nullable|string|max:255'
    ]);

    $card = CardAccount::find($request->card_id);
    $student = $card->student;

    if (!$student) {
        return response()->json([
            'success' => false,
            'message' => 'Student not found'
        ], 404);
    }

    if (!$card->is_active) {
        return response()->json([
            'success' => false,
            'message' => 'Card is inactive'
        ], 400);
    }

    if ($card->is_blocked) {
        return response()->json([
            'success' => false,
            'message' => 'Card is blocked: ' . ($card->blocked_reason ?? 'Contact admin')
        ], 400);
    }

    if ($card->is_locked) {
        return response()->json([
            'success' => false,
            'message' => 'Card is locked'
        ], 400);
    }

    if ($card->balance < $request->amount) {
        return response()->json([
            'success' => false,
            'message' => 'Insufficient balance. Available: KES ' . number_format($card->balance, 2)
        ], 400);
    }

    $balanceBefore = $card->balance;
    $card->balance -= $request->amount;
    $card->total_spent += $request->amount;
    $card->today_spent += $request->amount;
    $card->today_transactions += 1;
    $card->last_used_at = now();
    
    if (!$card->today_first_used_at) {
        $card->today_first_used_at = now();
    }
    $card->save();

    $reference = CardTransaction::generateReference('CAF');

    $transaction = CardTransaction::create([
        'card_account_id' => $card->id,
        'high_school_student_id' => $student->id,
        'transaction_type' => 'purchase',
        'amount' => $request->amount,
        'balance_before' => $balanceBefore,
        'balance_after' => $card->balance,
        'description' => $request->description ?? 'Cafeteria purchase (custom)',
        'reference' => $reference,
        'status' => 'completed',
        'processed_at' => now(),
        'metadata' => json_encode([
            'scanned_by' => auth()->id(),
            'scanned_by_name' => auth()->user()->name ?? 'System',
            'location' => 'cafeteria',
            'is_custom_amount' => true,
        ])
    ]);

    CardAuditLog::log(
        $card->id,
        'purchase_made',
        "Cafeteria purchase: KES {$request->amount} - {$request->description}",
        $balanceBefore,
        $card->balance,
        [
            'amount' => $request->amount,
            'description' => $request->description,
            'transaction_id' => $transaction->id,
            'scanned_by' => auth()->id()
        ]
    );

    return response()->json([
        'success' => true,
        'message' => 'Payment successful',
        'new_balance' => $card->balance,
        'transaction' => [
            'id' => $transaction->id,
            'amount' => $transaction->amount,
            'description' => $transaction->description,
            'balance_before' => $transaction->balance_before,
            'balance_after' => $transaction->balance_after,
            'student_name' => $student->full_name,
            'reference' => $transaction->reference,
            'created_at' => $transaction->created_at->toDateTimeString()
        ]
    ]);
}
    /**
     * Lookup student by admission number or card number
     */
    public function lookup(Request $request)
    {
        $request->validate([
            'query' => 'required|string'
        ]);

        $query = $request->query('query');

        // Try to find by admission number
        $student = HighSchoolStudent::where('admission_number', $query)
            ->with('cardAccount')
            ->first();

        if (!$student) {
            // Try to find by card number
            $card = CardAccount::where('card_number', $query)->first();
            if ($card) {
                $student = $card->student;
            }
        }

        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Student not found'
            ], 404);
        }

        if (!$student->cardAccount) {
            return response()->json([
                'success' => false,
                'message' => 'Student has no card'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'student_id' => $student->id,
                'card_id' => $student->cardAccount->id,
                'student_name' => $student->full_name,
                'admission_number' => $student->admission_number,
                'class' => $student->class,
                'balance' => $student->cardAccount->balance,
                'photo' => $student->profile_picture ? asset('storage/' . $student->profile_picture) : null,
                'card_status' => $student->cardAccount->is_active ? 'active' : 'inactive',
                'is_blocked' => $student->cardAccount->is_blocked,
                'is_locked' => $student->cardAccount->is_locked,
            ]
        ]);
    }

    /**
     * Decode QR code from image
     */
    public function decodeQR(Request $request)
    {
        $request->validate([
            'image' => 'required|string'
        ]);

        try {
            $imageData = $request->image;
            
            // Remove data:image/png;base64, prefix if present
            if (strpos($imageData, 'base64,') !== false) {
                $imageData = explode('base64,', $imageData)[1];
            }
            
            $imageBinary = base64_decode($imageData);
            $tempPath = storage_path('app/temp/qr_' . time() . '.png');
            $dir = dirname($tempPath);
            if (!is_dir($dir)) {
                mkdir($dir, 0777, true);
            }
            file_put_contents($tempPath, $imageBinary);
            
            $qrResult = null;
            
            // Try using the QR reader if available
            if (class_exists(\Zxing\QrReader::class)) {
                $qrReader = new \Zxing\QrReader($tempPath);
                $qrResult = $qrReader->text();
            }
            
            // Clean up temp file
            @unlink($tempPath);
            
            if ($qrResult) {
                return response()->json([
                    'success' => true,
                    'qr_data' => $qrResult
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'No QR code found in image'
                ]);
            }
            
        } catch (\Exception $e) {
            Log::error('QR decode error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to decode QR code'
            ], 400);
        }
    }

    /**
     * Scan QR code - handles encrypted QR data from cards
     */
    public function scan(Request $request)
    {
        $request->validate([
            'qr_data' => 'required|string'
        ]);

        try {
            $qrData = $request->qr_data;
            $card = null;
            
            // Check if it's our encrypted format: encrypted|hash
            if (strpos($qrData, '|') !== false) {
                $parts = explode('|', $qrData);
                if (count($parts) == 2) {
                    try {
                        $encryptedAccount = $parts[0];
                        $hash = $parts[1];
                        $accountNumber = decrypt($encryptedAccount);
                        
                        $card = CardAccount::where('account_number', $accountNumber)->first();
                        
                        if ($card) {
                            $expectedHash = substr(md5($card->id . $card->card_number), 0, 6);
                            if ($hash !== $expectedHash) {
                                $card = null;
                            }
                        }
                    } catch (\Exception $e) {
                        Log::warning('QR decryption failed: ' . $e->getMessage());
                    }
                }
            }
            
            // If not found, try as plain text
            if (!$card) {
                $card = CardAccount::where('card_number', $qrData)->first();
            }
            
            if (!$card) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid QR code'
                ], 404);
            }

            $student = $card->student;
            
            if (!$student) {
                return response()->json([
                    'success' => false,
                    'message' => 'No student associated with this card'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'student_id' => $student->id,
                    'card_id' => $card->id,
                    'student_name' => $student->full_name,
                    'admission_number' => $student->admission_number,
                    'class' => $student->class,
                    'balance' => $card->balance,
                    'photo' => $student->profile_picture ? asset('storage/' . $student->profile_picture) : null,
                    'card_status' => $card->is_active ? 'active' : 'inactive',
                    'is_blocked' => $card->is_blocked,
                    'is_locked' => $card->is_locked,
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('QR scan error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Invalid QR code'
            ], 400);
        }
    }

    /**
     * Process QR scan
     */
    public function process(Request $request)
    {
        return $this->scan($request);
    }

    /**
     * ✅ NEW: Process a card payment with product items
     * This replaces the old processPurchase method
     */
    public function processPurchase(Request $request)
    {
        $request->validate([
            'card_id' => 'required|exists:card_accounts,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1'
        ]);

        $card = CardAccount::find($request->card_id);
        $student = $card->student;

        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Student not found'
            ], 404);
        }

        // Check if card is active
        if (!$card->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Card is inactive'
            ], 400);
        }

        if ($card->is_blocked) {
            return response()->json([
                'success' => false,
                'message' => 'Card is blocked: ' . ($card->blocked_reason ?? 'Contact admin')
            ], 400);
        }

        if ($card->is_locked) {
            return response()->json([
                'success' => false,
                'message' => 'Card is locked'
            ], 400);
        }

        DB::beginTransaction();

        try {
            $totalAmount = 0;
            $itemsData = [];
            $stockService = app(StockService::class);
            $shopId = auth()->user()->shop_id ?? 1;

            // Process each item
            foreach ($request->items as $item) {
                $product = Product::findOrFail($item['product_id']);

                // Check stock
                if ($product->track_inventory) {
                    if ($product->current_stock < $item['quantity']) {
                        throw new \Exception("Insufficient stock for product: {$product->product_name}. Available: {$product->current_stock}");
                    }
                }

                $itemTotal = $product->selling_price * $item['quantity'];
                $totalAmount += $itemTotal;

                $itemsData[] = [
                    'product_id' => $product->id,
                    'product_name' => $product->product_name,
                    'product_code' => $product->product_code,
                    'quantity' => $item['quantity'],
                    'unit_price' => $product->selling_price,
                    'total_price' => $itemTotal,
                    'unit' => $product->unit,
                ];
            }

            // Check balance
            if ($card->balance < $totalAmount) {
                return response()->json([
                    'success' => false,
                    'message' => 'Insufficient balance. Available: KES ' . number_format($card->balance, 2) . ', Required: KES ' . number_format($totalAmount, 2)
                ], 400);
            }

            // Process the transaction
            $balanceBefore = $card->balance;
            $card->balance -= $totalAmount;
            $card->total_spent += $totalAmount;
            $card->today_spent += $totalAmount;
            $card->today_transactions += 1;
            $card->last_used_at = now();
            if (!$card->today_first_used_at) {
                $card->today_first_used_at = now();
            }
            $card->save();

            // Create card transaction
            $reference = CardTransaction::generateReference('CAF');
            $itemNames = collect($itemsData)->pluck('product_name')->join(', ');

            $transaction = CardTransaction::create([
                'card_account_id' => $card->id,
                'high_school_student_id' => $student->id,
                'transaction_type' => 'purchase',
                'amount' => $totalAmount,
                'balance_before' => $balanceBefore,
                'balance_after' => $card->balance,
                'description' => 'Cafeteria: ' . $itemNames,
                'reference' => $reference,
                'status' => 'completed',
                'processed_at' => now(),
                'metadata' => json_encode([
                    'scanned_by' => auth()->id(),
                    'scanned_by_name' => auth()->user()->name ?? 'System',
                    'location' => 'cafeteria',
                    'items' => $itemsData,
                ])
            ]);

            // Update inventory for each item
            foreach ($itemsData as $itemData) {
                $product = Product::find($itemData['product_id']);
                if ($product && $product->track_inventory) {
                    $stockResult = $stockService->updateStock(
                        $product->id,
                        $shopId,
                        $itemData['quantity'],
                        'sale',
                        'Scanner sale: ' . $reference,
                        'Student: ' . $student->full_name,
                        auth()->id()
                    );

                    if (!$stockResult['success']) {
                        throw new \Exception("Failed to update stock for {$product->product_name}: " . $stockResult['message']);
                    }
                }
            }

            // Log audit
            CardAuditLog::log(
                $card->id,
                'purchase_made',
                "Cafeteria purchase: KES {$totalAmount} - {$itemNames}",
                $balanceBefore,
                $card->balance,
                [
                    'amount' => $totalAmount,
                    'items' => $itemsData,
                    'transaction_id' => $transaction->id,
                    'scanned_by' => auth()->id()
                ]
            );

            DB::commit();

            Log::info('Card purchase processed with products', [
                'card_id' => $card->id,
                'student_id' => $student->id,
                'amount' => $totalAmount,
                'items' => count($itemsData),
                'new_balance' => $card->balance,
                'reference' => $reference
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Payment successful',
                'new_balance' => $card->balance,
                'transaction' => [
                    'id' => $transaction->id,
                    'amount' => $transaction->amount,
                    'description' => $transaction->description,
                    'balance_before' => $transaction->balance_before,
                    'balance_after' => $transaction->balance_after,
                    'student_name' => $student->full_name,
                    'reference' => $transaction->reference,
                    'created_at' => $transaction->created_at->toDateTimeString(),
                    'items' => $itemsData
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Purchase processing failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Get recent transactions for a student
     */
    public function transactions(Request $request)
    {
        $request->validate([
            'card_id' => 'required|exists:card_accounts,id'
        ]);

        $card = CardAccount::find($request->card_id);
        
        $transactions = CardTransaction::where('card_account_id', $card->id)
            ->where('transaction_type', 'purchase')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function($transaction) {
                $metadata = json_decode($transaction->metadata, true);
                return [
                    'id' => $transaction->id,
                    'amount' => $transaction->amount,
                    'description' => $transaction->description,
                    'balance_after' => $transaction->balance_after,
                    'reference' => $transaction->reference,
                    'created_at' => $transaction->created_at->toDateTimeString(),
                    'status' => $transaction->status,
                    'items' => $metadata['items'] ?? null
                ];
            });

        return response()->json([
            'success' => true,
            'transactions' => $transactions
        ]);
    }

    /**
     * Get QR code for a student
     */
    public function getQrCode(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:high_school_students,id'
        ]);

        $student = HighSchoolStudent::find($request->student_id);
        $card = $student->cardAccount;

        if (!$card) {
            return response()->json([
                'success' => false,
                'message' => 'Student has no card'
            ], 404);
        }

        $qrData = encrypt($card->account_number);

        return response()->json([
            'success' => true,
            'qr_data' => $qrData
        ]);
    }
}