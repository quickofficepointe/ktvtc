<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt - {{ $payment->receipt_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        @media print {
            body {
                margin: 0;
                padding: 0;
                background: white;
            }
            .no-print {
                display: none;
            }
            .receipt {
                box-shadow: none;
                margin: 0;
                border-radius: 0;
            }
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background: #f3f4f6;
            padding: 40px 20px;
        }

        .receipt {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            box-shadow: 0 20px 35px -10px rgba(0, 0, 0, 0.15);
            border-radius: 8px;
            overflow: hidden;
        }

        /* ========== LETTERHEAD ========== */
        .letterhead {
            padding: 30px 35px 20px;
            border-bottom: 3px solid #B91C1C;
            background: #ffffff;
        }

        .logo-section {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 15px;
        }

        .institution-name {
            font-size: 24px;
            font-weight: 700;
            color: #B91C1C;
            letter-spacing: -0.3px;
            margin-bottom: 5px;
        }

        .institution-tagline {
            font-size: 11px;
            color: #6b7280;
            letter-spacing: 0.3px;
            text-transform: uppercase;
        }

        .receipt-badge {
            display: inline-block;
            background: #B91C1C;
            color: white;
            padding: 5px 14px;
            border-radius: 20px;
            font-size: 10px;
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        .address-block {
            font-size: 11px;
            color: #6b7280;
            line-height: 1.6;
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px solid #f3f4f6;
        }

        .contact-row {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-top: 6px;
        }

        .contact-item {
            font-size: 11px;
            color: #6b7280;
        }

        .contact-label {
            color: #B91C1C;
            font-weight: 600;
            margin-right: 4px;
        }

        /* ========== RECEIPT HEADER ========== */
        .receipt-header {
            background: #B91C1C;
            color: white;
            padding: 22px 35px;
            text-align: center;
        }

        .receipt-title {
            font-size: 24px;
            font-weight: 700;
            letter-spacing: 1px;
            margin-bottom: 6px;
        }

        .receipt-subtitle {
            font-size: 11px;
            opacity: 0.85;
            text-transform: uppercase;
        }

        .receipt-number-box {
            background: rgba(255, 255, 255, 0.12);
            display: inline-block;
            padding: 6px 20px;
            border-radius: 30px;
            margin-top: 12px;
        }

        .receipt-number-box span {
            font-size: 10px;
            opacity: 0.8;
            letter-spacing: 0.5px;
        }

        .receipt-number-box strong {
            font-size: 16px;
            font-family: monospace;
            letter-spacing: 0.5px;
        }

        /* ========== CONTENT ========== */
        .content {
            padding: 25px 35px;
        }

        /* Status Badge */
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
        }

        .status-badge.verified {
            background: #d1fae5;
            color: #065f46;
        }

        .status-badge.pending {
            background: #fed7aa;
            color: #9a3412;
        }

        /* Info Grid */
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 25px;
        }

        .info-card {
            background: #fafafa;
            border-radius: 8px;
            padding: 16px 18px;
            border: 1px solid #f0f0f0;
        }

        .info-card h3 {
            font-size: 12px;
            font-weight: 700;
            color: #B91C1C;
            margin-bottom: 12px;
            padding-bottom: 8px;
            border-bottom: 1px solid #fee2e2;
            display: flex;
            align-items: center;
            gap: 6px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            font-size: 12px;
        }

        .info-label {
            color: #6b7280;
            font-weight: 500;
        }

        .info-value {
            color: #1f2937;
            font-weight: 600;
        }

        /* Amount Section */
        .amount-section {
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-radius: 8px;
            padding: 18px 22px;
            margin-bottom: 25px;
        }

        .amount-row {
            display: flex;
            justify-content: space-between;
            align-items: baseline;
        }

        .amount-label {
            font-size: 14px;
            font-weight: 700;
            color: #991b1b;
        }

        .amount-value {
            font-size: 28px;
            font-weight: 800;
            color: #B91C1C;
        }

        .amount-in-words {
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px dashed #fecaca;
            font-size: 11px;
            color: #4b5563;
        }

        /* Fee Breakdown */
        .fee-breakdown {
            background: #fafafa;
            border-radius: 8px;
            padding: 16px 18px;
            margin-bottom: 25px;
            border: 1px solid #f0f0f0;
        }

        .fee-breakdown h4 {
            font-size: 12px;
            font-weight: 700;
            color: #B91C1C;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .fee-item {
            display: flex;
            justify-content: space-between;
            font-size: 12px;
            padding: 6px 0;
            border-bottom: 1px dotted #e5e7eb;
        }

        .fee-item:last-child {
            border-bottom: none;
        }

        .balance-positive {
            color: #B91C1C;
            font-weight: 700;
        }

        .balance-zero {
            color: #059669;
            font-weight: 700;
        }

        /* Footer */
        .footer {
            margin-top: 25px;
            padding-top: 18px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
        }

        .footer p {
            font-size: 9px;
            color: #9ca3af;
            margin: 3px 0;
        }

        /* Signature */
        .signature {
            display: flex;
            justify-content: space-between;
            margin-top: 35px;
            padding-top: 10px;
        }

        .signature-line {
            width: 180px;
            border-top: 1px solid #d1d5db;
            margin-top: 20px;
        }

        .signature p {
            font-size: 9px;
            color: #6b7280;
            margin-top: 6px;
        }

        /* Actions */
        .actions {
            margin-top: 0;
            text-align: center;
            padding: 20px 35px 30px;
            background: white;
        }

        .print-button {
            background: #B91C1C;
            color: white;
            border: none;
            padding: 10px 24px;
            border-radius: 30px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .print-button:hover {
            background: #991b1b;
        }

        .close-button {
            background: #6b7280;
            margin-left: 10px;
        }

        .close-button:hover {
            background: #4b5563;
        }

        .text-center {
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="receipt">
        <!-- Letterhead -->
        <div class="letterhead">
            <div class="logo-section">
                <div>
                    <div class="institution-name">KENSWED COLLEGE</div>
                    <div class="institution-tagline">Skilling Youth for Sustainable Livelihoods</div>
                </div>
                <div>
                    <span class="receipt-badge">OFFICIAL RECEIPT</span>
                </div>
            </div>
            <div class="address-block">
                <div>Kikiko, Ngong, Kajiado West, Kenya</div>
                <div class="contact-row">
                    <span class="contact-item"><span class="contact-label">Tel:</span> +254 790 148 509</span>
                    <span class="contact-item"><span class="contact-label">Email:</span> info@ktvtc.ac.ke</span>
                    <span class="contact-item"><span class="contact-label">Website:</span> ktvtc.ac.ke</span>
                </div>
            </div>
        </div>

        <!-- Receipt Header -->
        <div class="receipt-header">
            <div class="receipt-title">PAYMENT RECEIPT</div>
            <div class="receipt-subtitle">Electronic Transaction Receipt</div>
            <div class="receipt-number-box">
                <span>RECEIPT NUMBER</span><br>
                <strong>{{ $payment->receipt_number }}</strong>
            </div>
        </div>

        <!-- Content -->
        <div class="content">
            <!-- Status -->
            <div style="text-align: center; margin-bottom: 20px;">
                @if($payment->is_verified)
                    <span class="status-badge verified">VERIFIED PAYMENT</span>
                @else
                    <span class="status-badge pending">PENDING VERIFICATION</span>
                @endif
            </div>

            <!-- Student & Payment Info -->
            <div class="info-grid">
                <div class="info-card">
                    <h3>STUDENT INFORMATION</h3>
                    <div class="info-row">
                        <span class="info-label">Full Name:</span>
                        <span class="info-value">{{ $payment->student->full_name ?? 'N/A' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Student ID:</span>
                        <span class="info-value">{{ $payment->student->student_number ?? 'N/A' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Course:</span>
                        <span class="info-value">{{ $payment->enrollment->course->name ?? $payment->enrollment->course_name ?? 'N/A' }}</span>
                    </div>
                </div>

                <div class="info-card">
                    <h3>PAYMENT INFORMATION</h3>
                    <div class="info-row">
                        <span class="info-label">Amount:</span>
                        <span class="info-value">KES {{ number_format($payment->amount, 2) }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Method:</span>
                        <span class="info-value">
                            @if($payment->payment_method == 'mpesa') M-Pesa
                            @elseif($payment->payment_method == 'cash') Cash
                            @elseif($payment->payment_method == 'bank') Bank Transfer
                            @elseif($payment->payment_method == 'kcb') KCB Bank
                            @else {{ ucfirst($payment->payment_method) }}
                            @endif
                        </span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Date:</span>
                        <span class="info-value">{{ $payment->payment_date->format('d/m/Y') }}</span>
                    </div>
                    @if($payment->transaction_code)
                    <div class="info-row">
                        <span class="info-label">Transaction ID:</span>
                        <span class="info-value">{{ $payment->transaction_code }}</span>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Amount Box -->
            <div class="amount-section">
                <div class="amount-row">
                    <span class="amount-label">TOTAL AMOUNT PAID</span>
                    <span class="amount-value">KES {{ number_format($payment->amount, 2) }}</span>
                </div>
                <div class="amount-in-words">
                    <strong>Amount in words:</strong> {{ number_format($payment->amount, 2) }} Shillings Only
                </div>
            </div>

            <!-- Fee Breakdown -->
            <div class="fee-breakdown">
                <h4>FEE BREAKDOWN</h4>
                <div class="fee-item">
                    <span>Total Course Fees:</span>
                    <span><strong>KES {{ number_format($payment->enrollment->total_fees ?? 0, 2) }}</strong></span>
                </div>
                <div class="fee-item">
                    <span>Total Paid to Date:</span>
                    <span><strong>KES {{ number_format($payment->enrollment->amount_paid ?? 0, 2) }}</strong></span>
                </div>
                <div class="fee-item">
                    <span>Outstanding Balance:</span>
                    <span>
                        @php $balance = ($payment->enrollment->total_fees ?? 0) - ($payment->enrollment->amount_paid ?? 0); @endphp
                        <strong class="{{ $balance > 0 ? 'balance-positive' : 'balance-zero' }}">
                            KES {{ number_format($balance, 2) }}
                        </strong>
                    </span>
                </div>
            </div>

            <!-- Payer Info (if available) -->
            @if($payment->payer_name)
            <div class="info-card" style="margin-bottom: 25px;">
                <h3>PAYER INFORMATION</h3>
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
                    <span class="info-label">Relationship:</span>
                    <span class="info-value">{{ ucfirst($payment->payer_type ?? 'Student') }}</span>
                </div>
            </div>
            @endif

            <!-- Footer -->
            <div class="footer">
                <p>This is a computer-generated receipt and requires no signature.</p>
                <p>For any inquiries, please contact the Finance Office.</p>
                <p>Issued on: {{ now()->format('F j, Y \a\t h:i A') }}</p>
            </div>

            <!-- Signature Lines -->
            <div class="signature">
                <div class="text-center">
                    <div class="signature-line"></div>
                    <p>Student's Signature</p>
                </div>
                <div class="text-center">
                    <div class="signature-line"></div>
                    <p>Finance Officer's Signature</p>
                </div>
            </div>
        </div>

        <!-- Print Button -->
        <div class="actions no-print">
            <button onclick="window.print()" class="print-button">
                Print Receipt
            </button>
            <button onclick="window.close()" class="print-button close-button">
                Close
            </button>
        </div>
    </div>
</body>
</html>
