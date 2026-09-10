<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Receipt - {{ $receipt_number }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #B91C1C;
            padding-bottom: 20px;
        }
        .header h1 {
            color: #B91C1C;
            margin: 0;
            font-size: 24px;
        }
        .header h2 {
            color: #666;
            margin: 5px 0;
            font-size: 16px;
        }
        .receipt-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }
        .info-box {
            flex: 1;
        }
        .info-box h3 {
            color: #B91C1C;
            margin-bottom: 10px;
            font-size: 14px;
        }
        .info-box p {
            margin: 5px 0;
            font-size: 12px;
        }
        .amount-box {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            text-align: center;
        }
        .amount-box h3 {
            color: #666;
            margin: 0 0 10px 0;
            font-size: 14px;
        }
        .amount-box .amount {
            font-size: 32px;
            font-weight: bold;
            color: #B91C1C;
        }
        .details-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        .details-table th,
        .details-table td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        .details-table th {
            background: #f8f9fa;
            font-weight: bold;
        }
        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 11px;
            color: #666;
        }
        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 80px;
            color: rgba(185, 28, 28, 0.1);
            pointer-events: none;
            z-index: -1;
        }
        .signature-section {
            margin-top: 40px;
            display: flex;
            justify-content: space-between;
        }
        .signature-box {
            text-align: center;
            flex: 1;
        }
        .signature-line {
            width: 200px;
            border-top: 1px solid #333;
            margin: 20px auto;
        }
    </style>
</head>
<body>
    <div class="watermark">KTVTC</div>

    <div class="header">
        <h1>KITUI TVET COLLEGE</h1>
        <h2>OFFICIAL PAYMENT RECEIPT</h2>
        <p>P.O. Box 1234-90100, Kitui | Tel: 0712 345 678 | Email: info@kituitvet.ac.ke</p>
    </div>

    <div class="receipt-info">
        <div class="info-box">
            <h3>RECEIPT DETAILS</h3>
            <p><strong>Receipt No:</strong> {{ $receipt_number }}</p>
            <p><strong>Transaction ID:</strong> {{ $transaction_id }}</p>
            <p><strong>Date:</strong> {{ $date }}</p>
            <p><strong>Time:</strong> {{ $time }}</p>
        </div>

        <div class="info-box">
            <h3>STUDENT INFORMATION</h3>
            <p><strong>Name:</strong> {{ $student_name }}</p>
            <p><strong>Student No:</strong> {{ $student_number }}</p>
            <p><strong>Registration No:</strong> {{ $registration_number }}</p>
            <p><strong>Course:</strong> {{ $course }}</p>
            <p><strong>Campus:</strong> {{ $campus }}</p>
        </div>
    </div>

    <div class="amount-box">
        <h3>AMOUNT PAID</h3>
        <div class="amount">{{ $amount }}</div>
    </div>

    <table class="details-table">
        <thead>
            <tr>
                <th>Description</th>
                <th>Details</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Fee Description</td>
                <td>{{ $fee_description }}</td>
            </tr>
            <tr>
                <td>Payment Method</td>
                <td>{{ $payment_method }}</td>
            </tr>
            <tr>
                <td>Payer Name</td>
                <td>{{ $payer_name }}</td>
            </tr>
            <tr>
                <td>Payer Phone</td>
                <td>{{ $payer_phone }}</td>
            </tr>
            <tr>
                <td>Balance Before</td>
                <td>{{ $balance_before }}</td>
            </tr>
            <tr>
                <td>Balance After</td>
                <td>{{ $balance_after }}</td>
            </tr>
        </tbody>
    </table>

    <div class="signature-section">
        <div class="signature-box">
            <p>___________________________</p>
            <p><strong>Student/Payer Signature</strong></p>
        </div>
        <div class="signature-box">
            <p>___________________________</p>
            <p><strong>Finance Officer</strong></p>
        </div>
        <div class="signature-box">
            <p>___________________________</p>
            <p><strong>College Stamp</strong></p>
        </div>
    </div>

    <div class="footer">
        <p><strong>IMPORTANT:</strong> Please keep this receipt for your records. It is proof of payment.</p>
        <p>For any inquiries, contact: finance@kituitvet.ac.ke | Tel: 0712 345 678</p>
        <p>Generated by: {{ $processed_by }} | Generated on: {{ date('d/m/Y H:i:s') }}</p>
        <p>Receipt ID: {{ $receipt_number }} | Transaction ID: {{ $transaction_id }}</p>
    </div>
</body>
</html>
