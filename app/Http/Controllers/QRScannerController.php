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
     *
     * ✅ RANKED BY HIGH SCHOOL STUDENT PURCHASE FREQUENCY (last 30 days)
     *    - Products students buy most appear first
     *    - Falls back to alphabetical when no sales exist
     *    - Filters strictly to high school students via card_accounts
     */
    public function getProducts(Request $request)
    {
        try {
            $user      = auth()->user();
            $shopId    = $user->shop_id ?? null;
            $sectionId = $user->business_section_id ?? null;

            Log::info('Scanner getProducts called', [
                'user_id'    => $user->id,
                'shop_id'    => $shopId ?? 'null',
                'section_id' => $sectionId ?? 'null',
            ]);

            $query = Product::with(['category'])
                ->where('is_active', true);

            // Filter by shop when known; else by section; else show all
            if ($shopId) {
                $query->where('shop_id', $shopId);
            } elseif ($sectionId) {
                $query->where('business_section_id', $sectionId);
            }

            if ($request->filled('category_id')) {
                $query->where('category_id', $request->category_id);
            }

            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('product_name', 'like', "%{$search}%")
                      ->orWhere('product_code', 'like', "%{$search}%");
                });
            }

            if ($request->boolean('in_stock_only')) {
                $query->where(function ($q) {
                    $q->where('track_inventory', false)
                      ->orWhere('current_stock', '>', 0);
                });
            }

            // ============================================================
            // ✅ RANK BY HIGH SCHOOL STUDENT PURCHASE FREQUENCY
            // ============================================================
            // - Only counts purchases linked to a high_school_student
            // - Last 30 days window (adjustable below)
            // - Handles both clean JSON and legacy double-encoded JSON
            // ============================================================
            $query->orderByRaw("
                (
                    SELECT COALESCE(SUM(jt.quantity), 0)
                    FROM card_transactions ct
                    INNER JOIN card_accounts ca ON ca.id = ct.card_account_id
                    JOIN JSON_TABLE(
                        JSON_UNQUOTE(ct.metadata),
                        '$.items[*]' COLUMNS (
                            product_id INT PATH '$.product_id',
                            quantity   INT PATH '$.quantity'
                        )
                    ) AS jt
                    WHERE ct.transaction_type = 'purchase'
                      AND ct.status = 'completed'
                      AND ca.high_school_student_id IS NOT NULL
                      AND jt.product_id = products.id
                      AND ct.created_at >= ?
                ) DESC
            ", [now()->subDays(30)])
            ->orderBy('product_name');

            $products = $query->get()
                ->map(function ($product) {
                    return [
                        'id'              => $product->id,
                        'product_code'    => $product->product_code,
                        'product_name'    => $product->product_name,
                        'selling_price'   => (float) $product->selling_price,
                        'current_stock'   => (int) $product->current_stock,
                        'track_inventory' => (bool) $product->track_inventory,
                        'unit'            => $product->unit,
                        'image'           => $product->image ? asset('storage/' . $product->image) : null,
                        'category_name'   => $product->category ? $product->category->category_name : null,
                        'is_available'    => !$product->track_inventory || $product->current_stock > 0,
                    ];
                });

            // Categories: only filter by section if we know it
            $categoriesQuery = \App\Models\ProductCategory::where('is_active', true);

            if ($sectionId) {
                $categoriesQuery->where('business_section_id', $sectionId);
            }

            $categories = $categoriesQuery
                ->orderBy('category_name')
                ->get(['id', 'category_name']);

            Log::info('Scanner getProducts returning', [
                'product_count'  => $products->count(),
                'category_count' => $categories->count(),
            ]);

            return response()->json([
                'success'    => true,
                'products'   => $products,
                'categories' => $categories,
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching scanner products: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to load products: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Lookup student by admission number, card number OR qr_token.
     */
    public function lookup(Request $request)
    {
        $request->validate([
            'query' => 'required|string',
        ]);

        $query = trim($request->query('query'));

        // 1. Try admission number
        $student = HighSchoolStudent::where('admission_number', $query)
            ->with('cardAccount')
            ->first();

        // 2. Try card number, account number OR qr_token
        if (!$student) {
            $card = CardAccount::where('card_number', $query)
                ->orWhere('account_number', $query)
                ->orWhere('qr_token', $query)
                ->with('student')
                ->first();

            if ($card) {
                $student = $card->student;
            }
        }

        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Student not found',
            ], 404);
        }

        if (!$student->cardAccount) {
            return response()->json([
                'success' => false,
                'message' => 'Student has no card',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => [
                'student_id'       => $student->id,
                'card_id'          => $student->cardAccount->id,
                'student_name'     => $student->full_name,
                'admission_number' => $student->admission_number,
                'class'            => $student->class,
                'balance'          => $student->cardAccount->balance,
                'photo'            => $student->profile_picture ? asset('storage/' . $student->profile_picture) : null,
                'card_status'      => $student->cardAccount->is_active ? 'active' : 'inactive',
                'is_blocked'       => $student->cardAccount->is_blocked,
                'is_locked'        => $student->cardAccount->is_locked,
            ],
        ]);
    }

    /**
     * Decode QR code from image
     */
    public function decodeQR(Request $request)
    {
        $request->validate([
            'image' => 'required|string',
        ]);

        try {
            $imageData = $request->image;

            if (strpos($imageData, 'base64,') !== false) {
                $imageData = explode('base64,', $imageData)[1];
            }

            $imageBinary = base64_decode($imageData);
            $tempDir = storage_path('app/temp');
            if (!is_dir($tempDir)) {
                mkdir($tempDir, 0777, true);
            }
            $tempPath = $tempDir . '/qr_' . time() . '_' . uniqid() . '.png';
            file_put_contents($tempPath, $imageBinary);

            $qrResult = null;

            if (class_exists(\Zxing\QrReader::class)) {
                $qrReader = new \Zxing\QrReader($tempPath);
                $qrResult = $qrReader->text();
            }

            @unlink($tempPath);

            if ($qrResult) {
                return response()->json([
                    'success' => true,
                    'qr_data' => trim($qrResult),
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'No QR code found in image',
            ]);

        } catch (\Exception $e) {
            Log::error('QR decode error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to decode QR code',
            ], 400);
        }
    }

    /**
     * Scan QR code.
     */
    public function scan(Request $request)
    {
        $request->validate([
            'qr_data' => 'required|string',
        ]);

        try {
            $qrData = trim($request->qr_data);

            Log::info('QR scan received', [
                'qr_data_preview' => substr($qrData, 0, 40),
                'length'          => strlen($qrData),
                'user_id'         => auth()->id(),
            ]);

            $card = null;

            // 1. NEW FORMAT: short qr_token
            if (!$card && strlen($qrData) <= 64 && preg_match('/^[A-Za-z0-9_\-]+$/', $qrData)) {
                $card = CardAccount::where('qr_token', $qrData)->first();
            }

            // 2. LEGACY: encrypted|hash
            if (!$card && strpos($qrData, '|') !== false) {
                $parts = explode('|', $qrData);
                if (count($parts) === 2) {
                    try {
                        $encryptedAccount = $parts[0];
                        $hash = $parts[1];
                        $accountNumber = decrypt($encryptedAccount);

                        $candidate = CardAccount::where('account_number', $accountNumber)->first();

                        if ($candidate) {
                            $expectedHash = substr(md5($candidate->id . $candidate->card_number), 0, 6);
                            if (hash_equals($expectedHash, $hash)) {
                                $card = $candidate;
                            }
                        }
                    } catch (\Exception $e) {
                        Log::warning('Legacy QR decryption failed: ' . $e->getMessage());
                    }
                }
            }

            // 3. FALLBACK: raw card_number / account_number
            if (!$card) {
                $card = CardAccount::where('card_number', $qrData)
                    ->orWhere('account_number', $qrData)
                    ->first();
            }

            if (!$card) {
                Log::warning('QR scan: no match', [
                    'qr_data_preview' => substr($qrData, 0, 40),
                    'length'          => strlen($qrData),
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Invalid QR code — card not found',
                ], 404);
            }

            $student = $card->student;

            if (!$student) {
                return response()->json([
                    'success' => false,
                    'message' => 'No student associated with this card',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data'    => [
                    'student_id'       => $student->id,
                    'card_id'          => $card->id,
                    'student_name'     => $student->full_name,
                    'admission_number' => $student->admission_number,
                    'class'            => $student->class,
                    'balance'          => $card->balance,
                    'photo'            => $student->profile_picture ? asset('storage/' . $student->profile_picture) : null,
                    'card_status'      => $card->is_active ? 'active' : 'inactive',
                    'is_blocked'       => $card->is_blocked,
                    'is_locked'        => $card->is_locked,
                ],
            ]);

        } catch (\Exception $e) {
            Log::error('QR scan error: ' . $e->getMessage(), [
                'qr_data_preview' => substr($request->qr_data ?? '', 0, 40),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Invalid QR code',
            ], 400);
        }
    }

    /**
     * Process QR scan (alias for scan)
     */
    public function process(Request $request)
    {
        return $this->scan($request);
    }

    /**
     * Process a card payment with product items
     *
     * ✅ FIX: metadata is now stored as a clean array (no manual json_encode)
     *    so JSON_TABLE queries can read it properly.
     */
    public function processPurchase(Request $request)
    {
        $request->validate([
            'card_id'            => 'required|exists:card_accounts,id',
            'items'              => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity'   => 'required|integer|min:1',
        ]);

        $card = CardAccount::find($request->card_id);
        $student = $card->student;

        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Student not found',
            ], 404);
        }

        if (!$card->is_active) {
            return response()->json(['success' => false, 'message' => 'Card is inactive'], 400);
        }
        if ($card->is_blocked) {
            return response()->json([
                'success' => false,
                'message' => 'Card is blocked: ' . ($card->blocked_reason ?? 'Contact admin'),
            ], 400);
        }
        if ($card->is_locked) {
            return response()->json(['success' => false, 'message' => 'Card is locked'], 400);
        }

        DB::beginTransaction();

        try {
            $totalAmount  = 0;
            $itemsData    = [];
            $stockService = app(StockService::class);
            $shopId       = auth()->user()->shop_id ?? 1;

            foreach ($request->items as $item) {
                $product = Product::findOrFail($item['product_id']);

                if ($product->track_inventory && $product->current_stock < $item['quantity']) {
                    throw new \Exception("Insufficient stock for {$product->product_name}. Available: {$product->current_stock}");
                }

                $itemTotal = $product->selling_price * $item['quantity'];
                $totalAmount += $itemTotal;

                $itemsData[] = [
                    'product_id'   => $product->id,
                    'product_name' => $product->product_name,
                    'product_code' => $product->product_code,
                    'quantity'     => $item['quantity'],
                    'unit_price'   => $product->selling_price,
                    'total_price'  => $itemTotal,
                    'unit'         => $product->unit,
                ];
            }

            if ($card->balance < $totalAmount) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Insufficient balance. Available: KES ' . number_format($card->balance, 2) . ', Required: KES ' . number_format($totalAmount, 2),
                ], 400);
            }

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

            $reference = CardTransaction::generateReference('CAF');
            $itemNames = collect($itemsData)->pluck('product_name')->join(', ');

            $transaction = CardTransaction::create([
                'card_account_id'  => $card->id,
                'transaction_type' => 'purchase',
                'amount'           => $totalAmount,
                'balance_before'   => $balanceBefore,
                'balance_after'    => $card->balance,
                'description'      => 'Cafeteria: ' . $itemNames,
                'reference'        => $reference,
                'status'           => 'completed',
                'processed_at'     => now(),
                // ✅ CLEAN ARRAY — the model's 'array' cast handles JSON encoding
                'metadata'         => [
                    'scanned_by'      => auth()->id(),
                    'scanned_by_name' => auth()->user()->name ?? 'System',
                    'location'        => 'cafeteria',
                    'items'           => $itemsData,
                ],
            ]);

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

            CardAuditLog::log(
                $card->id,
                'purchase_made',
                "Cafeteria purchase: KES {$totalAmount} - {$itemNames}",
                $balanceBefore,
                $card->balance,
                [
                    'amount'         => $totalAmount,
                    'items'          => $itemsData,
                    'transaction_id' => $transaction->id,
                    'scanned_by'     => auth()->id(),
                ]
            );

            DB::commit();

            Log::info('Card purchase processed with products', [
                'card_id'     => $card->id,
                'student_id'  => $student->id,
                'amount'      => $totalAmount,
                'items'       => count($itemsData),
                'new_balance' => $card->balance,
                'reference'   => $reference,
            ]);

            return response()->json([
                'success'     => true,
                'message'     => 'Payment successful',
                'new_balance' => $card->balance,
                'transaction' => [
                    'id'             => $transaction->id,
                    'amount'         => $transaction->amount,
                    'description'    => $transaction->description,
                    'balance_before' => $transaction->balance_before,
                    'balance_after'  => $transaction->balance_after,
                    'student_name'   => $student->full_name,
                    'reference'      => $transaction->reference,
                    'created_at'     => $transaction->created_at->toDateTimeString(),
                    'items'          => $itemsData,
                ],
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Purchase processing failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Process a custom amount purchase (legacy support)
     *
     * ✅ FIX: metadata is now stored as a clean array (no manual json_encode)
     */
    public function processCustomPurchase(Request $request)
    {
        $request->validate([
            'card_id'     => 'required|exists:card_accounts,id',
            'amount'      => 'required|numeric|min:1',
            'description' => 'nullable|string|max:255',
        ]);

        $card = CardAccount::find($request->card_id);
        $student = $card->student;

        if (!$student) {
            return response()->json(['success' => false, 'message' => 'Student not found'], 404);
        }
        if (!$card->is_active) {
            return response()->json(['success' => false, 'message' => 'Card is inactive'], 400);
        }
        if ($card->is_blocked) {
            return response()->json([
                'success' => false,
                'message' => 'Card is blocked: ' . ($card->blocked_reason ?? 'Contact admin'),
            ], 400);
        }
        if ($card->is_locked) {
            return response()->json(['success' => false, 'message' => 'Card is locked'], 400);
        }
        if ($card->balance < $request->amount) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient balance. Available: KES ' . number_format($card->balance, 2),
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
            'card_account_id'  => $card->id,
            'transaction_type' => 'purchase',
            'amount'           => $request->amount,
            'balance_before'   => $balanceBefore,
            'balance_after'    => $card->balance,
            'description'      => $request->description ?? 'Cafeteria purchase (custom)',
            'reference'        => $reference,
            'status'           => 'completed',
            'processed_at'     => now(),
            // ✅ CLEAN ARRAY
            'metadata'         => [
                'scanned_by'       => auth()->id(),
                'scanned_by_name'  => auth()->user()->name ?? 'System',
                'location'         => 'cafeteria',
                'is_custom_amount' => true,
            ],
        ]);

        CardAuditLog::log(
            $card->id,
            'purchase_made',
            "Cafeteria purchase: KES {$request->amount} - {$request->description}",
            $balanceBefore,
            $card->balance,
            [
                'amount'         => $request->amount,
                'description'    => $request->description,
                'transaction_id' => $transaction->id,
                'scanned_by'     => auth()->id(),
            ]
        );

        return response()->json([
            'success'     => true,
            'message'     => 'Payment successful',
            'new_balance' => $card->balance,
            'transaction' => [
                'id'             => $transaction->id,
                'amount'         => $transaction->amount,
                'description'    => $transaction->description,
                'balance_before' => $transaction->balance_before,
                'balance_after'  => $transaction->balance_after,
                'student_name'   => $student->full_name,
                'reference'      => $transaction->reference,
                'created_at'     => $transaction->created_at->toDateTimeString(),
            ],
        ]);
    }

    /**
     * Get recent transactions for a student
     */
    public function transactions(Request $request)
    {
        $request->validate([
            'card_id' => 'required|exists:card_accounts,id',
        ]);

        $card = CardAccount::find($request->card_id);

        $transactions = CardTransaction::where('card_account_id', $card->id)
            ->where('transaction_type', 'purchase')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($transaction) {
                $metadata = is_array($transaction->metadata)
                    ? $transaction->metadata
                    : json_decode($transaction->metadata, true);

                return [
                    'id'            => $transaction->id,
                    'amount'        => $transaction->amount,
                    'description'   => $transaction->description,
                    'balance_after' => $transaction->balance_after,
                    'reference'     => $transaction->reference,
                    'created_at'    => $transaction->created_at->toDateTimeString(),
                    'status'        => $transaction->status,
                    'items'         => $metadata['items'] ?? null,
                ];
            });

        return response()->json([
            'success'      => true,
            'transactions' => $transactions,
        ]);
    }

    /**
     * Get QR code for a student
     */
    public function getQrCode(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:high_school_students,id',
        ]);

        $student = HighSchoolStudent::find($request->student_id);
        $card = $student->cardAccount;

        if (!$card) {
            return response()->json([
                'success' => false,
                'message' => 'Student has no card',
            ], 404);
        }

        $qrData = $card->qr_token ?: $card->card_number;

        return response()->json([
            'success' => true,
            'qr_data' => $qrData,
        ]);
    }
}