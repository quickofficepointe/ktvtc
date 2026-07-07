<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>QR Code - {{ $card->card_number }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: Arial, sans-serif;
            background: #fff;
            padding: 20px;
        }
        .qr-container {
            max-width: 400px;
            margin: 0 auto;
            text-align: center;
            border: 1px solid #ddd;
            padding: 30px;
            border-radius: 12px;
        }
        .header {
            border-bottom: 2px solid #B91C1C;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .header h1 {
            font-size: 22px;
            color: #B91C1C;
            text-transform: uppercase;
        }
        .header p {
            font-size: 13px;
            color: #666;
            margin-top: 2px;
        }
        .qr-code {
            margin: 20px 0;
        }
        .qr-code img {
            width: 250px;
            height: 250px;
            object-fit: contain;
            border: 1px solid #eee;
            border-radius: 8px;
            padding: 10px;
        }
        .info {
            margin: 15px 0;
            padding: 15px;
            background: #f9fafb;
            border-radius: 6px;
        }
        .info .label {
            color: #6b7280;
            font-size: 12px;
        }
        .info .value {
            font-weight: bold;
            font-size: 16px;
            color: #1f2937;
        }
        .info .row {
            padding: 4px 0;
        }
        .footer {
            margin-top: 20px;
            padding-top: 15px;
            border-top: 2px solid #ddd;
            font-size: 12px;
            color: #6b7280;
        }
        .print-btn {
            display: block;
            width: 100%;
            max-width: 200px;
            margin: 20px auto;
            padding: 12px;
            background: #B91C1C;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
        }
        .print-btn:hover { background: #991B1B; }
        @media print {
            .no-print { display: none !important; }
            body { padding: 0; }
            .qr-container { border: none; border-radius: 0; }
        }
    </style>
</head>
<body>
    <div class="qr-container" id="qrContainer">
        <div class="header">
            <h1>KTVTC</h1>
            <p>Kenswed Technical & Vocational Training College</p>
            <p style="font-size: 11px; color: #059669;">Cafeteria Card QR Code</p>
        </div>

        <div class="qr-code">
            @if($card->qr_code)
                <img src="{{ asset('storage/' . $card->qr_code) }}" alt="QR Code">
            @else
                <div style="width:250px;height:250px;border:1px solid #ddd;display:flex;align-items:center;justify-content:center;color:#999;margin:0 auto;">
                    No QR Code
                </div>
            @endif
        </div>

        <div class="info">
            <div class="row">
                <span class="label">Card Number</span><br>
                <span class="value">{{ $card->card_number }}</span>
            </div>
            <div class="row">
                <span class="label">Student</span><br>
                <span class="value">{{ $card->student_name }}</span>
            </div>
            <div class="row">
                <span class="label">Admission Number</span><br>
                <span class="value">{{ $card->student_admission_number }}</span>
            </div>
            <div class="row">
                <span class="label">Class</span><br>
                <span class="value">{{ $card->student_class }}</span>
            </div>
        </div>

        <div class="footer">
            <p>Scan this QR code at the cafeteria</p>
            <p style="font-size: 10px; margin-top: 5px;">Generated on: {{ now()->format('d M Y H:i:s') }}</p>
        </div>
    </div>

    <button class="print-btn no-print" onclick="window.print()">
        <i class="fas fa-print"></i> Print QR Code
    </button>

    <script>
        // Auto print if requested
        @if(request('print'))
            window.print();
        @endif
    </script>
</body>
</html>
