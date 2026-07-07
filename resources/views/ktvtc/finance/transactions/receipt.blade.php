<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Transaction Receipt - {{ $transaction->transaction_number ?? 'N/A' }}</title>

    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: Arial, sans-serif;
            background: #f8f9fa;
            padding: 20px;
            font-size: 14px;
            color: #111827;
        }

        .receipt {
            max-width: 380px;
            margin: 0 auto;
            background: #fff;
            border: 1px solid #e5e7eb;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
        }

        .header {
            text-align: center;
            border-bottom: 2px dashed #991B1B;
            padding-bottom: 15px;
            margin-bottom: 15px;
        }

        .header h1 {
            font-size: 22px;
            color: #B91C1C;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .header p {
            font-size: 12px;
            color: #6b7280;
            margin-top: 3px;
        }

        .receipt-number {
            text-align: center;
            background: #FEE2E2;
            color: #991B1B;
            padding: 10px;
            border-radius: 6px;
            margin: 12px 0;
            font-weight: bold;
            font-size: 15px;
        }

        .row {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            padding: 6px 0;
            border-bottom: 1px dotted #e5e7eb;
        }

        .label {
            color: #6b7280;
            flex-shrink: 0;
        }

        .value {
            font-weight: bold;
            text-align: right;
            word-break: break-word;
        }

        .amount {
            font-size: 24px;
            text-align: center;
            padding: 18px 0;
            color: #B91C1C;
            font-weight: bold;
        }

        .status {
            text-align: center;
            padding: 9px;
            border-radius: 6px;
            margin: 10px 0;
            font-weight: bold;
            font-size: 13px;
            text-transform: uppercase;
        }

        .status-completed {
            background: #D1FAE5;
            color: #065F46;
        }

        .status-pending {
            background: #FEF3C7;
            color: #92400E;
        }

        .status-failed,
        .status-reversed {
            background: #FEE2E2;
            color: #991B1B;
        }

        .items {
            margin: 12px 0;
            padding: 10px 0;
            border-top: 1px dotted #ddd;
        }

        .footer {
            margin-top: 20px;
            padding-top: 15px;
            border-top: 2px dashed #991B1B;
            text-align: center;
            font-size: 12px;
            color: #6b7280;
        }

        .print-btn {
            display: block;
            max-width: 380px;
            width: 100%;
            margin: 15px auto 0;
            padding: 12px;
            background: #B91C1C;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 15px;
            cursor: pointer;
            font-weight: bold;
        }

        .print-btn:hover {
            background: #991B1B;
        }

        @media print {
            body {
                background: #fff;
                padding: 0;
            }

            .receipt {
                border: none;
                box-shadow: none;
                border-radius: 0;
                max-width: 100%;
            }

            .no-print {
                display: none !important;
            }
        }
    </style>
</head>

<body>
    @php
        $status = strtolower($transaction->status ?? 'pending');
    @endphp

    <div class="receipt">
        <div class="header">
            <h1>KTVTC</h1>
            <p>Kenswed Technical & Vocational Training College</p>
            <p>P.O. Box 12345, Nairobi</p>
            <p>Tel: 0700 000 000</p>
        </div>

        <div class="receipt-number">
            TRANSACTION: {{ $transaction->transaction_number ?? 'N/A' }}
        </div>

        <div>
            <div class="row">
                <span class="label">Date:</span>
                <span class="value">
                    {{ optional($transaction->created_at)->format('d M Y H:i') ?? 'N/A' }}
                </span>
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
                <span class="value">
                    {{ $transaction->sale->customer_phone ?? $transaction->phone_number ?? 'N/A' }}
                </span>
            </div>

            <div class="row">
                <span class="label">Payment Method:</span>
                <span class="value">{{ strtoupper($transaction->payment_method ?? 'N/A') }}</span>
            </div>

            @if(!empty($transaction->mpesa_receipt))
                <div class="row">
                    <span class="label">M-Pesa Receipt:</span>
                    <span class="value">{{ $transaction->mpesa_receipt }}</span>
                </div>
            @endif

            @if($transaction->sale && $transaction->sale->shop)
                <div class="row">
                    <span class="label">Shop:</span>
                    <span class="value">{{ $transaction->sale->shop->shop_name ?? 'N/A' }}</span>
                </div>
            @endif
        </div>

        <div class="amount">
            KES {{ number_format($transaction->amount ?? 0, 2) }}
        </div>

        <div class="status status-{{ in_array($status, ['completed', 'pending', 'failed', 'reversed']) ? $status : 'pending' }}">
            {{ strtoupper($status ?: 'N/A') }}
        </div>

        @if($transaction->sale && $transaction->sale->items && $transaction->sale->items->count() > 0)
            <div class="items">
                <p style="font-weight: bold; margin-bottom: 5px;">Items:</p>

                @foreach($transaction->sale->items as $item)
                    <div class="row" style="font-size: 12px;">
                        <span>{{ $item->quantity ?? 1 }}x {{ $item->product_name ?? 'Item' }}</span>
                        <span>KES {{ number_format($item->final_price ?? 0, 2) }}</span>
                    </div>
                @endforeach

                <div class="row" style="font-weight: bold; border-top: 1px solid #ddd; margin-top: 5px; padding-top: 5px;">
                    <span>Total</span>
                    <span>KES {{ number_format($transaction->sale->total_amount ?? 0, 2) }}</span>
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
        Print Receipt
    </button>

    @if(request('print'))
        <script>
            window.onload = function () {
                window.print();
            };
        </script>
    @endif
</body>
</html>
