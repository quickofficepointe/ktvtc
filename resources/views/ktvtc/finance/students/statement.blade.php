<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Student Statement - {{ $student->full_name ?? 'Student' }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: Arial, sans-serif;
            background: #fff;
            padding: 40px;
            font-size: 13px;
        }
        .statement {
            max-width: 900px;
            margin: 0 auto;
            border: 1px solid #ddd;
            padding: 40px;
            border-radius: 8px;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #059669;
            padding-bottom: 20px;
            margin-bottom: 20px;
        }
        .header h1 {
            font-size: 24px;
            color: #059669;
            text-transform: uppercase;
        }
        .header p {
            font-size: 13px;
            color: #666;
            margin-top: 4px;
        }
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px 30px;
            margin: 20px 0;
            padding: 15px;
            background: #f9fafb;
            border-radius: 6px;
        }
        .info-grid .label { color: #6b7280; font-size: 12px; }
        .info-grid .value { font-weight: bold; color: #1f2937; }
        .table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        .table th {
            background: #059669;
            color: white;
            padding: 10px 12px;
            text-align: left;
            font-size: 12px;
            text-transform: uppercase;
        }
        .table td { padding: 8px 12px; border-bottom: 1px solid #e5e7eb; }
        .table .total-row { background: #f3f4f6; font-weight: bold; }
        .table .total-row td { border-top: 2px solid #059669; padding: 12px; }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 2px solid #059669;
            text-align: center;
            font-size: 12px;
            color: #6b7280;
        }
        .badge {
            display: inline-block;
            padding: 2px 10px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: bold;
        }
        .badge-verified { background: #d1fae5; color: #065f46; }
        .badge-pending { background: #fef3c7; color: #92400e; }
        .badge-reversed { background: #fee2e2; color: #991b1b; }
        .print-btn {
            display: block;
            width: 100%;
            max-width: 200px;
            margin: 20px auto;
            padding: 12px;
            background: #059669;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
        }
        .print-btn:hover { background: #047857; }
        @media print {
            .no-print { display: none !important; }
            body { padding: 20px; }
            .statement { border: none; border-radius: 0; }
        }
    </style>
</head>
<body>
    <div class="statement" id="statement">
        <!-- Header -->
        <div class="header">
            <h1>KTVTC</h1>
            <p>Kenswed Technical & Vocational Training College</p>
            <p style="font-size: 12px;">P.O. Box 12345, Nairobi | Tel: 0700 000 000</p>
            <h2 style="margin-top: 10px; font-size: 18px; color: #1f2937;">Student Fee Statement</h2>
        </div>

        <!-- Student Info -->
        <div class="info-grid">
            <div>
                <p class="label">Student Name</p>
                <p class="value">{{ $student->full_name ?? 'N/A' }}</p>
            </div>
            <div>
                <p class="label">Student Number</p>
                <p class="value">{{ $student->student_number ?? 'N/A' }}</p>
            </div>
            <div>
                <p class="label">Email</p>
                <p class="value">{{ $student->email ?? 'N/A' }}</p>
            </div>
            <div>
                <p class="label">Phone</p>
                <p class="value">{{ $student->phone ?? 'N/A' }}</p>
            </div>
            <div>
                <p class="label">Statement Date</p>
                <p class="value">{{ now()->format('d M Y H:i') }}</p>
            </div>
            <div>
                <p class="label">Campus</p>
                <p class="value">{{ $student->campus->name ?? 'N/A' }}</p>
            </div>
        </div>

        <!-- Statement Table -->
        <table class="table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Receipt #</th>
                    <th>Course</th>
                    <th>Payment Method</th>
                    <th style="text-align: right;">Amount (KES)</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($payments ?? [] as $payment)
                    <tr>
                        <td>{{ $payment->payment_date->format('d M Y') }}</td>
                        <td>{{ $payment->receipt_number }}</td>
                        <td>{{ $payment->enrollment->course->name ?? 'N/A' }}</td>
                        <td>{{ strtoupper($payment->payment_method) }}</td>
                        <td style="text-align: right; font-weight: bold;">
                            {{ number_format($payment->amount, 2) }}
                        </td>
                        <td>
                            <span class="badge badge-{{ $payment->is_verified ? 'verified' : 'pending' }}">
                                {{ $payment->is_verified ? 'Verified' : 'Pending' }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 30px; color: #6b7280;">
                            No payment records found
                        </td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot>
                @php
                    $totalPaid = $payments->sum('amount');
                    $totalFees = \App\Models\Enrollment::where('student_id', $student->id)->sum('total_fees');
                    $balance = $totalFees - $totalPaid;
                @endphp
                <tr class="total-row">
                    <td colspan="4" style="text-align: right; font-size: 14px;">Total Paid</td>
                    <td style="text-align: right; font-size: 14px; color: #059669;">
                        KES {{ number_format($totalPaid, 2) }}
                    </td>
                    <td></td>
                </tr>
                <tr class="total-row" style="background: #fef3c7;">
                    <td colspan="4" style="text-align: right; font-size: 14px;">Total Fees</td>
                    <td style="text-align: right; font-size: 14px; color: #92400e;">
                        KES {{ number_format($totalFees, 2) }}
                    </td>
                    <td></td>
                </tr>
                <tr class="total-row" style="background: {{ $balance > 0 ? '#fee2e2' : '#d1fae5' }};">
                    <td colspan="4" style="text-align: right; font-size: 16px; font-weight: bold;">Balance</td>
                    <td style="text-align: right; font-size: 16px; font-weight: bold; color: {{ $balance > 0 ? '#991b1b' : '#065f46' }};">
                        {{ $balance > 0 ? 'KES ' : 'KES ' }}{{ number_format(abs($balance), 2) }}
                    </td>
                    <td>
                        <span class="badge badge-{{ $balance > 0 ? 'pending' : 'verified' }}">
                            {{ $balance > 0 ? 'Outstanding' : 'Fully Paid' }}
                        </span>
                    </td>
                </tr>
            </tfoot>
        </table>

        <!-- Footer -->
        <div class="footer">
            <p>This is a computer generated statement. For any queries, please contact the Finance Office.</p>
            <p style="margin-top: 5px;">Generated on: {{ now()->format('d M Y H:i:s') }}</p>
        </div>
    </div>

    <button class="print-btn no-print" onclick="window.print()">
        <i class="fas fa-print"></i> Print Statement
    </button>

    <script>
        // Auto print if requested
        @if(request('print'))
            window.print();
        @endif
    </script>
</body>
</html>
