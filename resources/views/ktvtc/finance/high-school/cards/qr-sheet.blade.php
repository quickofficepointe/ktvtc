<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>QR Code Sheet</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
            background: #fff;
        }
        .page {
            max-width: 1100px;
            margin: 0 auto;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #B91C1C;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .header h1 {
            font-size: 24px;
            color: #B91C1C;
        }
        .header p {
            color: #666;
            font-size: 14px;
        }
        .qr-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 15px;
        }
        .qr-item {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 10px;
            text-align: center;
            background: #fafafa;
        }
        .qr-item img {
            width: 120px;
            height: 120px;
            object-fit: contain;
        }
        .qr-item .name {
            font-weight: bold;
            font-size: 12px;
            margin-top: 5px;
        }
        .qr-item .details {
            font-size: 10px;
            color: #666;
        }
        .qr-item .card-number {
            font-size: 10px;
            color: #999;
            font-family: monospace;
        }
        .footer {
            text-align: center;
            margin-top: 20px;
            padding-top: 15px;
            border-top: 1px solid #ddd;
            font-size: 12px;
            color: #999;
        }
        @media print {
            body { padding: 10px; }
            .no-print { display: none !important; }
        }
        .no-print {
            display: block;
            margin: 20px auto;
            padding: 12px 24px;
            background: #B91C1C;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
        }
        .no-print:hover { background: #991B1B; }
    </style>
</head>
<body>
    <div class="page">
        <div class="header">
            <h1>KTVTC - Cafeteria Cards QR Codes</h1>
            <p>Generated on {{ now()->format('d M Y H:i:s') }}</p>
            <p style="font-size: 12px; color: #999;">Total: {{ $cards->count() }} cards</p>
        </div>

        <div class="qr-grid">
            @foreach($cards as $card)
                <div class="qr-item">
                    @if($card->qr_code)
                        <img src="{{ asset('storage/' . $card->qr_code) }}" alt="QR">
                    @else
                        <div style="width:120px;height:120px;border:1px solid #ddd;display:flex;align-items:center;justify-content:center;margin:0 auto;color:#999;font-size:12px;">No QR</div>
                    @endif
                    <div class="name">{{ $card->student_name }}</div>
                    <div class="details">{{ $card->student_admission_number }} | {{ $card->student_class }}</div>
                    <div class="card-number">{{ $card->card_number }}</div>
                </div>
            @endforeach
        </div>

        <div class="footer">
            <p>Kenswed Technical & Vocational Training College</p>
            <p style="font-size: 10px;">Scan QR code at cafeteria for payment</p>
        </div>
    </div>

    <button class="no-print" onclick="window.print()">
        <i class="fas fa-print"></i> Print All QR Codes
    </button>
</body>
</html>
