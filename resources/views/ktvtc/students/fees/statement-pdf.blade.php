<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fee Statement - {{ $student->student_number ?? 'Student' }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', 'Helvetica', sans-serif;
            background: white;
            padding: 40px;
            color: #333;
        }

        .container {
            max-width: 1000px;
            margin: 0 auto;
            background: white;
        }

        /* Header Section */
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 3px solid #E63946;
        }

        .logo {
            margin-bottom: 15px;
        }

        .logo h1 {
            color: #E63946;
            font-size: 28px;
            letter-spacing: 1px;
        }

        .logo p {
            color: #666;
            font-size: 12px;
            margin-top: 5px;
        }

        .title {
            font-size: 24px;
            font-weight: bold;
            color: #333;
            margin-top: 10px;
        }

        /* Info Section */
        .info-section {
            margin-bottom: 30px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
        }

        .info-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
            border-bottom: 1px dashed #ddd;
        }

        .info-label {
            font-weight: bold;
            color: #555;
        }

        .info-value {
            color: #333;
        }

        /* Summary Cards */
        .summary {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }

        .summary-card {
            padding: 15px;
            border-radius: 8px;
            text-align: center;
        }

        .summary-card.total-fees {
            background: #e3f2fd;
            border-left: 4px solid #2196F3;
        }

        .summary-card.total-paid {
            background: #e8f5e9;
            border-left: 4px solid #4CAF50;
        }

        .summary-card.balance {
            background: #ffebee;
            border-left: 4px solid #f44336;
        }

        .summary-card .label {
            font-size: 12px;
            text-transform: uppercase;
            color: #666;
            margin-bottom: 8px;
        }

        .summary-card .amount {
            font-size: 24px;
            font-weight: bold;
        }

        .total-fees .amount { color: #2196F3; }
        .total-paid .amount { color: #4CAF50; }
        .balance .amount { color: #f44336; }

        /* Table Styles */
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }

        .table th {
            background: #E63946;
            color: white;
            padding: 12px;
            text-align: left;
            font-size: 13px;
            font-weight: 600;
        }

        .table td {
            padding: 10px 12px;
            border-bottom: 1px solid #eee;
            font-size: 12px;
        }

        .table tr:hover {
            background: #f9f9f9;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .font-bold {
            font-weight: bold;
        }

        .status-active {
            color: #4CAF50;
        }

        .status-completed {
            color: #2196F3;
        }

        .status-dropped {
            color: #f44336;
        }

        .payment-method {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: bold;
        }

        .method-kcb { background: #e3f2fd; color: #1976D2; }
        .method-mpesa { background: #e8f5e9; color: #2E7D32; }
        .method-cash { background: #fff3e0; color: #E65100; }
        .method-bank { background: #f3e5f5; color: #7B1FA2; }

        /* Footer */
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            text-align: center;
            font-size: 10px;
            color: #999;
        }

        .signature {
            margin-top: 30px;
            padding-top: 20px;
            display: flex;
            justify-content: space-between;
        }

        .signature-line {
            text-align: center;
            width: 200px;
        }

        .signature-line .line {
            border-top: 1px solid #333;
            margin: 10px 0 5px;
        }

        .page-break {
            page-break-before: always;
        }

        @media print {
            body {
                padding: 20px;
            }
            .no-print {
                display: none;
            }
            .table th {
                background: #E63946;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .summary-card {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="logo">
                <h1>KENSWED COLLEGE</h1>
                <p>Technical & Vocational Training</p>
                <p>P.O. Box 1234-00100, Nairobi, Kenya | Tel: +254 790 148 509 | Email: info@ktvtc.ac.ke</p>
            </div>
            <div class="title">
                FEE STATEMENT
            </div>
        </div>

        <!-- Student Information -->
        <div class="info-section">
            <div class="info-grid">
                <div class="info-item">
                    <span class="info-label">Student Name:</span>
                    <span class="info-value">{{ $student->full_name ?? $student->first_name . ' ' . $student->last_name }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Student Number:</span>
                    <span class="info-value">{{ $student->student_number ?? 'N/A' }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">ID Number:</span>
                    <span class="info-value">{{ $student->id_number ?? 'N/A' }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Phone:</span>
                    <span class="info-value">{{ $student->phone ?? 'N/A' }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Email:</span>
                    <span class="info-value">{{ $student->email ?? 'N/A' }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Statement Date:</span>
                    <span class="info-value">{{ now()->format('d/m/Y H:i:s') }}</span>
                </div>
            </div>
        </div>

        <!-- Financial Summary -->
        <div class="summary">
            <div class="summary-card total-fees">
                <div class="label">TOTAL FEES</div>
                <div class="amount">KES {{ number_format($totalFees, 2) }}</div>
            </div>
            <div class="summary-card total-paid">
                <div class="label">TOTAL PAID</div>
                <div class="amount">KES {{ number_format($totalPaid, 2) }}</div>
            </div>
            <div class="summary-card balance">
                <div class="label">OUTSTANDING BALANCE</div>
                <div class="amount">KES {{ number_format($totalBalance, 2) }}</div>
            </div>
        </div>

        <!-- Course Enrollments -->
        <h3 style="margin-bottom: 15px; color: #E63946;">Course Enrollments</h3>
        <table class="table">
            <thead>
                <tr>
                    <th>Course Name</th>
                    <th>Course Code</th>
                    <th>Intake</th>
                    <th class="text-right">Total Fees (KES)</th>
                    <th class="text-right">Paid (KES)</th>
                    <th class="text-right">Balance (KES)</th>
                    <th class="text-center">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($enrollments as $enrollment)
                <tr>
                    <td>{{ $enrollment->course_name }}</td>
                    <td>{{ $enrollment->course_code ?? 'N/A' }}</td>
                    <td>{{ $enrollment->intake_month }} {{ $enrollment->intake_year }}</td>
                    <td class="text-right">{{ number_format($enrollment->total_fees, 2) }}</td>
                    <td class="text-right">{{ number_format($enrollment->amount_paid, 2) }}</td>
                    <td class="text-right font-bold {{ $enrollment->balance > 0 ? 'status-dropped' : 'status-active' }}">
                        {{ number_format($enrollment->balance, 2) }}
                    </td>
                    <td class="text-center">
                        <span class="status-{{ $enrollment->status }}">
                            {{ ucfirst($enrollment->status) }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center">No enrollments found</td>
                </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3" class="text-right font-bold">TOTALS:</td>
                    <td class="text-right font-bold">{{ number_format($totalFees, 2) }}</td>
                    <td class="text-right font-bold">{{ number_format($totalPaid, 2) }}</td>
                    <td class="text-right font-bold">{{ number_format($totalBalance, 2) }}</td>
                    <td></td>
                </tr>
            </tfoot>
        </table>

        <!-- Payment History -->
        @if(isset($payments) && $payments->count() > 0)
        <h3 style="margin-bottom: 15px; color: #E63946; margin-top: 30px;">Payment History</h3>
        <table class="table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Receipt Number</th>
                    <th>Course</th>
                    <th class="text-right">Amount (KES)</th>
                    <th>Payment Method</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($payments as $payment)
                <tr>
                    <td>{{ $payment->payment_date->format('d/m/Y') }}</td>
                    <td>{{ $payment->receipt_number }}</td>
                    <td>{{ $payment->enrollment->course_name ?? 'N/A' }}</td>
                    <td class="text-right">{{ number_format($payment->amount, 2) }}</td>
                    <td>
                        <span class="payment-method method-{{ $payment->payment_method }}">
                            {{ strtoupper($payment->payment_method) }}
                        </span>
                    </td>
                    <td>
                        @if($payment->status == 'completed')
                            <span style="color: #4CAF50;">✓ Completed</span>
                        @else
                            <span style="color: #f44336;">{{ ucfirst($payment->status) }}</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3" class="text-right font-bold">TOTAL PAID:</td>
                    <td class="text-right font-bold">{{ number_format($payments->sum('amount'), 2) }}</td>
                    <td colspan="2"></td>
                </tr>
            </tfoot>
        </table>
        @endif

        <!-- Payment Instructions for Outstanding Balance -->
        @if($totalBalance > 0)
        <div style="background: #fff3cd; padding: 15px; border-radius: 8px; margin: 20px 0;">
            <h4 style="color: #856404; margin-bottom: 10px;">Payment Instructions</h4>
            <p style="font-size: 12px; color: #856404;">
                To clear your outstanding balance of <strong>KES {{ number_format($totalBalance, 2) }}</strong>, please use the following payment methods:
            </p>
            <ul style="font-size: 12px; color: #856404; margin-top: 10px; margin-left: 20px;">
                <li><strong>KCB M-Pesa Paybill:</strong> 7664166</li>
                <li><strong>Account Number:</strong> {{ $student->student_number ?? 'Your Student Number' }}</li>
                <li><strong>Bank Deposit:</strong> KCB Bank - Account Name: Kenswed College, Account Number: 1234567890</li>
            </ul>
            <p style="font-size: 11px; color: #856404; margin-top: 10px;">
                For inquiries, please contact the Finance Office at +254 790 148 509 or email finance@ktvtc.ac.ke
            </p>
        </div>
        @else
        <div style="background: #d4edda; padding: 15px; border-radius: 8px; margin: 20px 0;">
            <p style="font-size: 12px; color: #155724; text-align: center;">
                ✓ Your fees are fully paid. Thank you for your timely payment!
            </p>
        </div>
        @endif

        <!-- Footer -->
        <div class="footer">
            <p>This is a computer-generated statement and does not require a signature.</p>
            <p>Kenswed Technical and Vocational Training College - Quality Education for Sustainable Development</p>
            <p>Generated on: {{ now()->format('d/m/Y H:i:s') }}</p>
        </div>

        <!-- Signature Section -->
        <div class="signature">
            <div class="signature-line">
                <div class="line"></div>
                <p>Student's Signature</p>
                <p style="font-size: 10px; color: #999;">Date: _____________</p>
            </div>
            <div class="signature-line">
                <div class="line"></div>
                <p>Finance Officer's Signature</p>
                <p style="font-size: 10px; color: #999;">Date: _____________</p>
            </div>
            <div class="signature-line">
                <div class="line"></div>
                <p>Registrar's Signature</p>
                <p style="font-size: 10px; color: #999;">Date: _____________</p>
            </div>
        </div>
    </div>
</body>
</html>
