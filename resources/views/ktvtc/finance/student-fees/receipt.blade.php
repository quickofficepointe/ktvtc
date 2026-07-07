<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Payment Receipt - {{ $payment->receipt_number }}</title>

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
        }

        .status-verified {
            background: #D1FAE5;
            color: #065F46;
        }

        .status-pending {
            background: #FEF3C7;
            color: #92400E;
        }

        .summary {
            margin: 12px 0;
            padding: 10px 0;
            border-top: 1px dotted #ddd;
        }

        .balance-row {
            font-weight: bold;
            font-size: 16px;
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
    <div class="receipt">
        <div class="header">
            <h1>KTVTC</h1>
            <p>Kenswed Technical & Vocational Training College</p>
            <p>P.O. Box 12345, Nairobi</p>
            <p>Tel: 0700 000 000</p>
        </div>

        <div class="receipt-number">
            RECEIPT: {{ $payment->receipt_number }}
        </div>

        <div>
            <div class="row">
                <span class="label">Date:</span>
                <span class="value">
                    {{ optional($payment->payment_date)->format('d M Y H:i') ?? 'N/A' }}
                </span>
            </div>

            <div class="row">
                <span class="label">Student:</span>
                <span class="value">{{ $payment->student->full_name ?? 'N/A' }}</span>
            </div>

            <div class="row">
                <span class="label">Student No:</span>
                <span class="value">{{ $payment->student->student_number ?? 'N/A' }}</span>
            </div>

            <div class="row">
                <span class="label">Course:</span>
                <span class="value">{{ $payment->enrollment->course->name ?? 'N/A' }}</span>
            </div>

            <div class="row">
                <span class="label">Payment Method:</span>
                <span class="value">{{ strtoupper($payment->payment_method ?? 'N/A') }}</span>
            </div>

            @if(!empty($payment->transaction_code))
                <div class="row">
                    <span class="label">Transaction Code:</span>
                    <span class="value">{{ $payment->transaction_code }}</span>
                </div>
            @endif

            @if(!empty($payment->payer_name))
                <div class="row">
                    <span class="label">Payer:</span>
                    <span class="value">{{ $payment->payer_name }}</span>
                </div>
            @endif
        </div>

        <div class="amount">
            KES {{ number_format($payment->amount ?? 0, 2) }}
        </div>

        <div class="status {{ $payment->is_verified ? 'status-verified' : 'status-pending' }}">
            {{ $payment->is_verified ? 'VERIFIED' : 'PENDING VERIFICATION' }}
        </div>

        <div class="summary">
            <div class="row">
                <span class="label">Total Fees:</span>
                <span class="value">KES {{ number_format($payment->enrollment->total_fees ?? 0, 2) }}</span>
            </div>

            <div class="row">
                <span class="label">Amount Paid:</span>
                <span class="value">KES {{ number_format($payment->enrollment->amount_paid ?? 0, 2) }}</span>
            </div>

            <div class="row balance-row">
                <span class="label">Balance:</span>
                <span class="value">KES {{ number_format($payment->enrollment->balance ?? 0, 2) }}</span>
            </div>
        </div>

        <div class="footer">
            <p>Thank you for your payment</p>
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
