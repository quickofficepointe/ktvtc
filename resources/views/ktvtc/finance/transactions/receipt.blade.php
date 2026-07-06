<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Transaction Receipt - {{ $transaction->transaction_number }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Courier New', monospace;
            background: #fff;
            padding: 20px;
            font-size: 14px;
        }
        .receipt {
            max-width: 350px;
            margin: 0 auto;
            border: 1px solid #ddd;
            padding: 20px;
            border-radius: 8px;
        }
        .header {
            text-align: center;
            border-bottom: 2px dashed #333;
            padding-bottom: 15px;
            margin-bottom: 15px;
        }
        .header h1 {
            font-size: 20px;
            color: #059669;
            text-transform: uppercase;
            letter-spacing: 2px;
        }
        .header p {
            font-size: 12px;
            color: #666;
            margin-top: 2px;
        }
        .receipt-number {
            text-align: center;
            background: #f3f4f6;
            padding: 8px;
            border-radius: 4px;
            margin: 10px 0;
            font-weight: bold;
            font-size: 16px;
        }
        .row {
            display: flex;
            justify-content: space-between;
            padding: 4px 0;
            border-bottom: 1px dotted #eee;
        }
        .row:last-child { border-bottom: none; }
        .label { color: #666; }
        .value { font-weight: bold; }
        .amount {
            font-size: 24px;
            text-align: center;
            padding: 15px 0;
            color: #059669;
            font-weight: bold;
        }
        .footer {
            margin-top: 20px;
            padding-top: 15px;
            border-top: 2px dashed #333;
            text-align: center;
            font-size: 12px;
            color: #666;
        }
        .status {
            text-align: center;
            padding: 8px;
            border-radius: 4px;
            margin: 10px 0;
            font-weight: bold;
        }
        .status-completed { background: #d1fae5; color: #065f46; }
        .status-pending { background: #fef3c7; color: #92400e; }
        .status-failed { background: #fee2e2; color: #991b1b; }
        .print-btn {
            display: block;
            width: 100%;
            padding: 12px;
            background: #059669;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
            margin-top: 15px;
        }
        .print-btn:hover { background: #047857; }
        @media print {
            .no-print { display: none !important; }
            body { padding: 0; }
            .receipt { border: none; border-radius: 0; }
        }
    </style>
</head>
<body>
    <div class="receipt" id="receipt">
        <div class="header">
            <h1>KTVTC</h1>
            <p>Kenswed Technical & Vocational Training College</p>
            <p style="font-size: 11px;">P.O. Box 12345, Nairobi</p>
            <p style="font-size: 11px;">Tel: 0700 000 000</p>
        </div>

        <div class="receipt-number">
            TRANSACTION: {{ $transaction->transaction_number }}
        </div>

        <div style="margin: 10px 0;">
            <div class="row">
                <span class="label">Date:</span>
                <span class="value">{{ $transaction->created_at->format('d M Y H:i') }}</span>
            </div>
            <div class="row">
                <span class="label">Invoice:</span>
                <span class="value">{{ $transaction->sale->invoice_number ?? 'N/A' }}</span>
            </div>
            <div class="row">
                <span class="label">Customer:</span>
                <span class="value">{{ $transaction->sale->customer_name ?? 'N/A' }}</span>
            </div>
            <div class="row">
                <span class="label">Phone:</span>
                <span class="value">{{ $transaction->sale->customer_phone ?? $transaction->phone_number ?? 'N/A' }}</span>
            </div>
            <div class="row">
                <span class="label">Payment Method:</span>
                <span class="value">{{ strtoupper($transaction->payment_method) }}</span>
            </div>
            @if($transaction->mpesa_receipt)
            <div class="row">
                <span class="label">M-Pesa Receipt:</span>
                <span class="value">{{ $transaction->mpesa_receipt }}</span>
            </div>
            @endif
            @if($transaction->sale && $transaction->sale->shop)
            <div class="row">
                <span class="label">Shop:</span>
                <span class="value">{{ $transaction->sale->shop->shop_name }}</span>
            </div>
            @endif
        </div>

        <div class="amount">
            KES {{ number_format($transaction->amount, 2) }}
        </div>

        <div class="status status-{{ $transaction->status }}">
            {{ strtoupper($transaction->status) }}
        </div>

        @if($transaction->sale && $transaction->sale->items)
        <div style="margin: 10px 0; padding: 10px 0; border-top: 1px dotted #ddd;">
            <p style="font-weight: bold; margin-bottom: 5px;">Items:</p>
            @foreach($transaction->sale->items as $item)
                <div class="row" style="font-size: 12px;">
                    <span>{{ $item->quantity }}x {{ $item->product_name }}</span>
                    <span>KES {{ number_format($item->final_price, 2) }}</span>
                </div>
            @endforeach
            <div class="row" style="font-weight: bold; border-top: 1px solid #ddd; margin-top: 5px; padding-top: 5px;">
                <span>Total</span>
                <span>KES {{ number_format($transaction->sale->total_amount, 2) }}</span>
            </div>
        </div>
        @endif

        <div class="footer">
            <p>Thank you for your business</p>
            <p style="font-size: 10px; margin-top: 5px;">This is a system generated receipt</p>
            <p style="font-size: 10px;">Printed on: {{ now()->format('d M Y H:i:s') }}</p>
        </div>
    </div>

    <button class="print-btn no-print" onclick="window.print()">
        <i class="fas fa-print"></i> Print Receipt
    </button>

    <script>
        // Auto print if requested
        @if(request('print'))
            window.print();
        @endif
    </script>
</body>
</html>
