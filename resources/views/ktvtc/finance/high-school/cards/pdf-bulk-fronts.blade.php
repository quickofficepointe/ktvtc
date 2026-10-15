<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>ID Cards — Fronts</title>
<style>
    /* ============================================================
       PAGE SETUP
       A4 = 210mm x 297mm
       Cards: 88.9 x 50.8mm, 2 columns x 5 rows = 10 cards
       Gap between cards: 4mm (H & V)
    ============================================================ */
    @page {
        size: A4 portrait;
        margin: 0;
    }

    * { margin: 0; padding: 0; box-sizing: border-box; }

    html, body {
        width: 210mm;
        height: 297mm;
        font-family: Arial, Helvetica, sans-serif;
        color: #1a1a1a;
    }

    /* The whole page is a positioning canvas */
    .page {
        position: relative;
        width: 210mm;
        height: 297mm;
        page-break-after: always;
    }
    .page:last-child {
        page-break-after: auto;
    }

    /* ============================================================
       CARD POSITIONS
       Column X:  14.1mm (left card)   /   107mm (right card = 14.1 + 88.9 + 4)
       Row Y:     13.5 / 68.3 / 123.1 / 177.9 / 232.7
                  (each row: prev + 50.8 + 4)
    ============================================================ */
    .card {
        position: absolute;
        width: 88.9mm;
        height: 50.8mm;
        border: 0.3mm solid #c7ccd1;
        border-radius: 1.5mm;
        overflow: hidden;
        background: #ffffff;
    }

    /* Row 1 */ .card.r1c1 { top: 13.5mm; left: 14.1mm; }
    .card.r1c2            { top: 13.5mm; left: 107mm; }
    /* Row 2 */ .card.r2c1 { top: 68.3mm; left: 14.1mm; }
    .card.r2c2            { top: 68.3mm; left: 107mm; }
    /* Row 3 */ .card.r3c1 { top: 123.1mm; left: 14.1mm; }
    .card.r3c2            { top: 123.1mm; left: 107mm; }
    /* Row 4 */ .card.r4c1 { top: 177.9mm; left: 14.1mm; }
    .card.r4c2            { top: 177.9mm; left: 107mm; }
    /* Row 5 */ .card.r5c1 { top: 232.7mm; left: 14.1mm; }
    .card.r5c2            { top: 232.7mm; left: 107mm; }

    /* ============================================================
       CARD INTERNALS — absolutely positioned
    ============================================================ */
    .hdr-logo {
        position: absolute;
        top: 1.2mm;
        left: 1.5mm;
        width: 10mm;
        height: 10mm;
        border-radius: 50%;
        border: 0.3mm solid #e5e7eb;
    }

    .hdr-name {
        position: absolute;
        top: 2mm;
        left: 13mm;
        width: 30mm;
        font-size: 7.5px;
        font-weight: 900;
        text-transform: uppercase;
        line-height: 1;
        color: #1a1a1a;
    }

    .hdr-slogan {
        position: absolute;
        top: 8.5mm;
        left: 13mm;
        background: #d81524;
        color: #ffffff;
        font-size: 4.5px;
        font-weight: 700;
        text-transform: uppercase;
        padding: 0.5mm 1.2mm;
        border-radius: 0.4mm;
    }

    .hdr-banner {
        position: absolute;
        top: 0;
        right: 0;
        width: 40mm;
        height: 13mm;
        background-image: url('{{ $header_bg }}');
        background-size: cover;
        background-position: center;
        background-color: #b91c1c;
        color: #ffffff;
        text-align: center;
        font-size: 7px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        line-height: 13mm;
    }

    .photo {
        position: absolute;
        top: 15mm;
        left: 2.5mm;
        width: 16mm;
        height: 16mm;
        border-radius: 3mm;
        border: 1mm solid #d81524;
    }

    .student-name {
        position: absolute;
        top: 14.5mm;
        left: 21.5mm;
        width: 44mm;
        font-size: 8px;
        font-weight: 700;
        text-transform: uppercase;
        color: #1a1a1a;
        line-height: 1.1;
    }

    .info {
        position: absolute;
        top: 18mm;
        left: 21.5mm;
        width: 44mm;
        font-size: 6px;
        line-height: 1.45;
    }
    .info .row { display: block; }
    .info .label {
        display: inline-block;
        width: 12mm;
        color: #6b7280;
    }
    .info .value {
        display: inline;
        color: #d81524;
        font-weight: 600;
    }
    .info .value.mono {
        font-family: monospace;
        font-size: 5.5px;
    }

    .qr {
        position: absolute;
        top: 15mm;
        right: 2.5mm;
        width: 16mm;
        height: 16mm;
    }
    .qr img {
        width: 16mm;
        height: 16mm;
        display: block;
    }

    .footer-left {
        position: absolute;
        bottom: 0;
        left: 0;
        width: 68mm;
        height: 10.8mm;
        background-image: url('{{ $footer_bg }}');
        background-size: cover;
        background-position: center;
        background-color: #b91c1c;
        color: #ffffff;
        font-size: 5.5px;
        padding: 1.5mm 2mm 0 3mm;
        border-top-right-radius: 6mm;
        line-height: 1.5;
    }

    .footer-right {
        position: absolute;
        bottom: 0;
        right: 0;
        width: 20.9mm;
        height: 10.8mm;
        background: #ffffff;
        text-align: center;
        padding-top: 3.5mm;
    }

    .sig-line {
        width: 14mm;
        height: 0.3mm;
        background: #000000;
        margin: 0 auto 0.6mm auto;
    }

    .sig-label {
        font-size: 5px;
        font-weight: 700;
        text-transform: uppercase;
        color: #1a1a1a;
    }
</style>
</head>
<body>

@php
    // Chunk into pages of 10 cards (2 cols x 5 rows)
    $pages = $cards->chunk(10);

    // Predefined position classes in reading order
    $positions = [
        'r1c1','r1c2',
        'r2c1','r2c2',
        'r3c1','r3c2',
        'r4c1','r4c2',
        'r5c1','r5c2',
    ];
@endphp

@foreach($pages as $pageCards)
    <div class="page">
        @foreach($pageCards->values() as $idx => $card)
            @php
                $pos = $positions[$idx] ?? 'r1c1';
                $photo = $card->photo_base64 ?? null;
                $qr = $card->qr_base64 ?? null;
            @endphp
            <div class="card {{ $pos }}">

                {{-- HEADER --}}
                @if($logo)
                    <img src="{{ $logo }}" class="hdr-logo">
                @endif

                <div class="hdr-name">Kenswed<br>Secondary School</div>
                <div class="hdr-slogan">it seems impossible until it's done</div>
                <div class="hdr-banner">Students' Identity Card</div>

                {{-- BODY --}}
                @if($photo)
                    <img src="{{ $photo }}" class="photo">
                @endif

                <div class="student-name">{{ $card->student_name }}</div>

                <div class="info">
                    <span class="row">
                        <span class="label">Adm No</span>
                        <span class="value">: {{ $card->student_admission_number }}</span>
                    </span>
                    <span class="row">
                        <span class="label">Class</span>
                        <span class="value">: {{ $card->student_class }}</span>
                    </span>
                    <span class="row">
                        <span class="label">Ass. No</span>
                        <span class="value">: {{ $card->student->stream ?? 'N/A' }}</span>
                    </span>
                    <span class="row">
                        <span class="label">D.O.B</span>
                        <span class="value">: {{ $card->student && $card->student->date_of_birth ? $card->student->date_of_birth->format('d/m/Y') : 'N/A' }}</span>
                    </span>
                    <span class="row">
                        <span class="label">Gender</span>
                        <span class="value">: {{ $card->student ? ucfirst($card->student->gender) : 'N/A' }}</span>
                    </span>
                    <span class="row">
                        <span class="label">Card No</span>
                        <span class="value mono">: {{ $card->card_number }}</span>
                    </span>
                </div>

                @if($qr)
                    <div class="qr">
                        <img src="{{ $qr }}">
                    </div>
                @endif

                {{-- FOOTER --}}
                <div class="footer-left">
                    <div>P: +254 743 237 432 &nbsp;www.kenswed.org</div>
                    <div>L: Kibiko, Off Maragara road, Kajiado West</div>
                </div>
                <div class="footer-right">
                    <div class="sig-line"></div>
                    <div class="sig-label">Principal</div>
                </div>

            </div>
        @endforeach
    </div>
@endforeach

</body>
</html>