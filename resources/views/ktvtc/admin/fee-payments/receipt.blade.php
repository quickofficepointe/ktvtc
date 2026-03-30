<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt - {{ $payment->receipt_number }}</title>
    <style>
        @media print {
            body { margin: 0; padding: 20px; }
            .no-print { display: none; }
            .print-only { display: block; }
            .page-break { page-break-after: always; }
        }
        body {
            font-family: 'Inter', Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background: #fff;
        }
        .receipt {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            overflow: hidden;
        }
        .header {
            background: #B91C1C;
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 32px;
            font-weight: 600;
        }
        .header p {
            margin: 5px 0 0;
            opacity: 0.9;
        }
        .content {
            padding: 30px;
        }
        .receipt-title {
            text-align: center;
            margin-bottom: 30px;
        }
        .receipt-title h2 {
            margin: 0;
            color: #374151;
            font-size: 24px;
        }
        .receipt-title .receipt-number {
            font-size: 14px;
            color: #6B7280;
            margin-top: 5px;
            font-family: monospace;
        }
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }
        .info-box {
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 15px;
        }
        .info-box h3 {
            margin: 0 0 15px 0;
            font-size: 16px;
            color: #374151;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 10px;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            font-size: 14px;
        }
        .info-label {
            color: #6B7280;
        }
        .info-value {
            color: #374151;
            font-weight: 500;
        }
        .amount-section {
            background: #F9FAFB;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 30px;
        }
        .amount-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 18px;
        }
        .amount-label {
            color: #374151;
            font-weight: 500;
        }
        .amount-value {
            font-size: 24px;
            font-weight: 700;
            color: #10B981;
        }
        .amount-in-words {
            margin-top: 10px;
            font-size: 14px;
            color: #6B7280;
            font-style: italic;
        }
        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 12px;
            color: #6B7280;
            border-top: 1px dashed #e5e7eb;
            padding-top: 20px;
        }
        .footer p {
            margin: 5px 0;
        }
        .signature {
            display: flex;
            justify-content: space-between;
            margin-top: 40px;
        }
        .signature-line {
            width: 200px;
            border-top: 1px solid #374151;
            margin-top: 40px;
        }
        .actions {
            margin-top: 20px;
            text-align: center;
        }
        .print-button {
            background: #B91C1C;
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
        }
        .print-button:hover {
            background: #7f1d1d;
        }
        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 60px;
            color: rgba(185, 28, 28, 0.05);
            white-space: nowrap;
            pointer-events: none;
            z-index: -1;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        .status-badge.verified {
            background: #D1FAE5;
            color: #065F46;
        }
        .status-badge.pending {
            background: #FEF3C7;
            color: #92400E;
        }
    </style>
</head>
<body>
    <div class="receipt">
        <!-- Header -->
        <div class="header">
            <h1>KISII TVET</h1>
            <p>OFFICIAL RECEIPT</p>
        </div>

        <!-- Content -->
        <div class="content">
            <div class="receipt-title">
                <h2>PAYMENT RECEIPT</h2>
                <div class="receipt-number">Receipt No: {{ $payment->receipt_number }}</div>
                <div style="margin-top: 10px;">
                    @if($payment->is_verified)
                        <span class="status-badge verified">✓ VERIFIED</span>
                    @else
                        <span class="status-badge pending">⏳ PENDING VERIFICATION</span>
                    @endif
                </div>
            </div>

            <!-- Student & Payment Info -->
            <div class="info-grid">
                <div class="info-box">
                    <h3>Student Information</h3>
                    <div class="info-row">
                        <span class="info-label">Name:</span>
                        <span class="info-value">{{ $payment->student->full_name ?? 'N/A' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Student No:</span>
                        <span class="info-value">{{ $payment->student->student_number ?? 'N/A' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Course:</span>
                        <span class="info-value">{{ $payment->enrollment->course->name ?? 'N/A' }}</span>
                    </div>
                </div>

                <div class="info-box">
                    <h3>Payment Information</h3>
                    <div class="info-row">
                        <span class="info-label">Amount:</span>
                        <span class="info-value">KES {{ number_format($payment->amount, 2) }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Method:</span>
                        <span class="info-value">{{ strtoupper($payment->payment_method) }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Date:</span>
                        <span class="info-value">{{ $payment->payment_date->format('d/m/Y') }}</span>
                    </div>
                    @if($payment->transaction_code)
                    <div class="info-row">
                        <span class="info-label">Transaction Code:</span>
                        <span class="info-value">{{ $payment->transaction_code }}</span>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Amount Box -->
            <div class="amount-section">
                <div class="amount-row">
                    <span class="amount-label">Total Amount Paid:</span>
                    <span class="amount-value">KES {{ number_format($payment->amount, 2) }}</span>
                </div>
                <div class="amount-in-words">
                    Amount in words: <strong>{{ numberToWords($payment->amount) }} Shillings Only</strong>
                </div>
            </div>

            <!-- Enrollment Details -->
            <div class="info-grid">
                <div class="info-box">
                    <h3>Enrollment Details</h3>
                    <div class="info-row">
                        <span class="info-label">Intake:</span>
                        <span class="info-value">{{ $payment->enrollment->intake_period ?? '' }} {{ $payment->enrollment->intake_year ?? '' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Total Fees:</span>
                        <span class="info-value">KES {{ number_format($payment->enrollment->total_course_fee ?? 0, 2) }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Balance:</span>
                        <span class="info-value">KES {{ number_format($payment->enrollment->balance ?? 0, 2) }}</span>
                    </div>
                </div>

                @if($payment->payer_name)
                <div class="info-box">
                    <h3>Payer Information</h3>
                    <div class="info-row">
                        <span class="info-label">Name:</span>
                        <span class="info-value">{{ $payment->payer_name }}</span>
                    </div>
                    @if($payment->payer_phone)
                    <div class="info-row">
                        <span class="info-label">Phone:</span>
                        <span class="info-value">{{ $payment->payer_phone }}</span>
                    </div>
                    @endif
                    <div class="info-row">
                        <span class="info-label">Type:</span>
                        <span class="info-value">{{ ucfirst($payment->payer_type ?? 'student') }}</span>
                    </div>
                </div>
                @endif
            </div>

            <!-- Footer -->
            <div class="footer">
                <p>This is a computer generated receipt. No signature required.</p>
                <p>KISII TVET - {{ now()->format('F j, Y') }}</p>
            </div>

            <!-- Signature (for printed copies) -->
            <div class="signature no-print">
                <div>
                    <div class="signature-line"></div>
                    <p class="text-center">Received By</p>
                </div>
                <div>
                    <div class="signature-line"></div>
                    <p class="text-center">Authorized Signatory</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Print Button -->
    <div class="actions no-print">
        <button onclick="window.print()" class="print-button">
            <i class="fas fa-print"></i> Print Receipt
        </button>
        <button onclick="window.close()" class="print-button" style="background: #6B7280; margin-left: 10px;">
            <i class="fas fa-times"></i> Close
        </button>
    </div>
</body>
</html>
