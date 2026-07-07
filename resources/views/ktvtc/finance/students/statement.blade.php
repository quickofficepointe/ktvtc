<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Student Statement - {{ $student->full_name ?? 'Student' }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: Arial, sans-serif;
            background: #fff;
            padding: 30px;
            font-size: 13px;
            color: #111827;
        }

        .statement {
            max-width: 900px;
            margin: 0 auto;
            border: 1px solid #e5e7eb;
            padding: 35px;
            border-radius: 8px;
        }

        .header {
            text-align: center;
            border-bottom: 2px solid #B91C1C;
            padding-bottom: 20px;
            margin-bottom: 20px;
        }

        .header h1 {
            font-size: 24px;
            color: #B91C1C;
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

        .label { color: #6b7280; font-size: 12px; }
        .value { font-weight: bold; color: #1f2937; }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }

        th {
            background: #B91C1C;
            color: white;
            padding: 10px 12px;
            text-align: left;
            font-size: 12px;
            text-transform: uppercase;
        }

        td {
            padding: 8px 12px;
            border-bottom: 1px solid #e5e7eb;
        }

        .total-row {
            background: #f3f4f6;
            font-weight: bold;
        }

        .total-row td {
            border-top: 2px solid #B91C1C;
            padding: 12px;
        }

        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 2px solid #B91C1C;
            text-align: center;
            font-size: 12px;
            color: #6b7280;
        }

        .badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: bold;
        }

        .badge-verified { background: #d1fae5; color: #065f46; }
        .badge-pending { background: #fef3c7; color: #92400e; }
        .badge-reversed { background: #fee2e2; color: #991b1b; }

        .print-btn {
            display: block;
            width: 100%;
            max-width: 220px;
            margin: 20px auto;
            padding: 12px;
            background: #B91C1C;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 15px;
            cursor: pointer;
            font-weight: bold;
        }

        .print-btn:hover { background: #991B1B; }

        @media print {
            .no-print { display: none !important; }
            body { padding: 15px; }
            .statement { border: none; padding: 0; }
        }
    </style>
</head>
<body>
@php
    $totalPaid = collect($payments ?? [])->where('status', 'completed')->sum('amount');
    $totalFees = $totalFees ?? \App\Models\Enrollment::where('student_id', $student->id)->sum('total_fees');
    $balance = $totalFees - $totalPaid;
@endphp

<div class="statement">
    <div class="header">
        <h1>KTVTC</h1>
        <p>Kenswed Technical & Vocational Training College</p>
        <p>P.O. Box 12345, Nairobi | Tel: 0700 000 000</p>
        <h2 style="margin-top: 10px; font-size: 18px; color: #1f2937;">
            Student Fee Statement
        </h2>
    </div>

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

    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Receipt #</th>
                <th>Course</th>
                <th>Method</th>
                <th style="text-align: right;">Amount</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($payments ?? [] as $payment)
                <tr>
                    <td>{{ optional($payment->payment_date)->format('d M Y') ?? 'N/A' }}</td>
                    <td>{{ $payment->receipt_number ?? 'N/A' }}</td>
                    <td>{{ $payment->enrollment->course->name ?? 'N/A' }}</td>
                    <td>{{ strtoupper($payment->payment_method ?? 'N/A') }}</td>
                    <td style="text-align: right; font-weight: bold;">
                        KES {{ number_format($payment->amount ?? 0, 2) }}
                    </td>
                    <td>
                        @if(($payment->status ?? '') === 'reversed')
                            <span class="badge badge-reversed">Reversed</span>
                        @elseif($payment->is_verified)
                            <span class="badge badge-verified">Verified</span>
                        @else
                            <span class="badge badge-pending">Pending</span>
                        @endif
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
            <tr class="total-row">
                <td colspan="4" style="text-align: right;">Total Paid</td>
                <td style="text-align: right; color: #065f46;">
                    KES {{ number_format($totalPaid, 2) }}
                </td>
                <td></td>
            </tr>
            <tr class="total-row">
                <td colspan="4" style="text-align: right;">Total Fees</td>
                <td style="text-align: right; color: #92400e;">
                    KES {{ number_format($totalFees, 2) }}
                </td>
                <td></td>
            </tr>
            <tr class="total-row" style="background: {{ $balance > 0 ? '#fee2e2' : '#d1fae5' }};">
                <td colspan="4" style="text-align: right; font-size: 16px;">Balance</td>
                <td style="text-align: right; font-size: 16px; color: {{ $balance > 0 ? '#991b1b' : '#065f46' }};">
                    KES {{ number_format(abs($balance), 2) }}
                </td>
                <td>
                    <span class="badge {{ $balance > 0 ? 'badge-pending' : 'badge-verified' }}">
                        {{ $balance > 0 ? 'Outstanding' : 'Fully Paid' }}
                    </span>
                </td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        <p>This is a computer generated statement. For any queries, please contact the Finance Office.</p>
        <p style="margin-top: 5px;">Generated on: {{ now()->format('d M Y H:i:s') }}</p>
    </div>
</div>

<button class="print-btn no-print" onclick="window.print()">Print Statement</button>

@if(request('print'))
    <script>
        window.onload = function () {
            window.print();
        };
    </script>
@endif
</body>
</html>
