{{--
    resources/views/ktvtc/finance/high-school/cards/pdf-id-card.blade.php
    PDF ID Card - Optimized for DomPDF (Barryvdh)
    FINAL: Social icons using text abbreviations (most reliable for DomPDF)
--}}

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>ID Card - {{ $card->student_name }}</title>

    @php
        $student = $card->student;

        // ============================================================
        // GET IMAGE PATHS (Use public_path for DomPDF)
        // ============================================================

        $logoPath = public_path('images/logo/main-logo.png');
        $logoBase64 = file_exists($logoPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath)) : '';

        $photoPath = $student && $student->profile_picture && file_exists(public_path('storage/' . $student->profile_picture))
            ? public_path('storage/' . $student->profile_picture)
            : public_path('images/default/avatar.png');
        $photoBase64 = file_exists($photoPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($photoPath)) : '';

        $qrPath = $card->qr_code && file_exists(public_path('storage/' . $card->qr_code))
            ? public_path('storage/' . $card->qr_code)
            : null;
        $qrBase64 = $qrPath && file_exists($qrPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($qrPath)) : null;

        // Header background image
        $headerBgPath = public_path('images/id-card/svg-bg.png');
        $headerBgBase64 = file_exists($headerBgPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($headerBgPath)) : '';

        // Footer background image
        $footerBgPath = public_path('images/id-card/footer-bg.png');
        $footerBgBase64 = file_exists($footerBgPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($footerBgPath)) : '';

        // Student data with fallbacks
        $stream = $student && $student->stream ? $student->stream : 'N/A';
        $dob = $student && $student->date_of_birth ? $student->date_of_birth->format('d/m/Y') : 'N/A';
        $gender = $student ? ucfirst($student->gender) : 'N/A';
        $studentName = $card->student_name;
        $admNo = $card->student_admission_number;
        $class = $card->student_class;
        $cardNo = $card->card_number;
    @endphp

    <style>
        /* ===== RESET ===== */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            background: #ffffff;
            padding: 0;
            margin: 0;
        }

        /* ===== CARD CONTAINER ===== */
        .id-card {
            width: 100%;
            max-width: 800px;
            margin: 0 auto;
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            overflow: hidden;
        }

        /* ==========================================
           FRONT PAGE
        ========================================== */

        .card-front {
            page-break-after: always;
        }

        /* ----- HEADER ----- */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            background: #ffffff;
            height: 120px;
        }

        .header-left {
            width: 55%;
            padding: 10px 20px;
            vertical-align: middle;
        }

        .header-left .logo-cell {
            width: 80px;
            vertical-align: middle;
        }

        .header-left .logo {
            width: 75px;
            height: 75px;
            border-radius: 50%;
            border: 2px solid #e5e7eb;
            object-fit: cover;
            display: block;
        }

        .header-left .school-cell {
            vertical-align: middle;
            padding-left: 12px;
        }

        .header-left .school-name {
            font-size: 20px;
            font-weight: 900;
            text-transform: uppercase;
            color: #1a1a1a;
            line-height: 1.2;
        }

        .header-left .school-name .line1 {
            display: block;
        }
        .header-left .school-name .line2 {
            display: block;
        }

        .header-left .slogan-badge {
            background: #d81524;
            color: #ffffff;
            font-size: 7px;
            font-weight: 700;
            text-transform: uppercase;
            padding: 3px 12px;
            border-radius: 4px;
            display: inline-block;
            margin-top: 4px;
            letter-spacing: 0.5px;
        }

        .header-left .slogan-badge .line {
            display: inline-block;
            width: 10px;
            height: 2px;
            background: #ffffff;
            margin: 0 4px;
            vertical-align: middle;
        }

        .header-right {
            width: 45%;
            background-image: url('{{ $headerBgBase64 }}');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            text-align: center;
            vertical-align: middle;
            padding: 10px 20px;
            position: relative;
        }

        .header-right h2 {
            color: #ffffff;
            font-size: 20px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 2px;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.7), 0 0 30px rgba(0, 0, 0, 0.3);
            position: relative;
            z-index: 2;
        }

        /* ----- BODY ----- */
        .body-table {
            width: 100%;
            border-collapse: collapse;
            padding: 0;
            background: #ffffff;
        }

        .body-table .photo-cell {
            width: 28%;
            text-align: center;
            vertical-align: middle;
            padding: 16px 12px;
        }

        .body-table .photo-cell img {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border-radius: 30px;
            border: 5px solid #d81524;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            display: block;
            margin: 0 auto;
        }

        .body-table .info-cell {
            width: 48%;
            vertical-align: middle;
            padding: 12px 16px;
        }

        .body-table .info-cell .student-name {
            font-size: 18px;
            font-weight: 700;
            text-transform: uppercase;
            color: #1a1a1a;
            margin-bottom: 6px;
        }

        .body-table .info-cell .info-row {
            font-size: 12px;
            padding: 2px 0;
            line-height: 1.6;
        }

        .body-table .info-cell .info-row .label {
            display: inline-block;
            width: 70px;
            color: #6b7280;
            font-weight: 500;
        }

        .body-table .info-cell .info-row .value {
            color: #d81524;
            font-weight: 600;
        }

        .body-table .info-cell .info-row .value.mono {
            font-family: monospace;
            font-size: 11px;
        }

        .body-table .qr-cell {
            width: 24%;
            text-align: center;
            vertical-align: middle;
            padding: 12px;
        }

        .body-table .qr-cell .qr-box {
            width: 110px;
            height: 110px;
            margin: 0 auto;
            border: 2px dashed #9ca3af;
            border-radius: 12px;
            background: #f9fafb;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 10px;
        }

        .body-table .qr-cell .qr-box img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            display: block;
        }

        .body-table .qr-cell .qr-box .no-qr {
            text-align: center;
            color: #9ca3af;
            font-size: 10px;
        }

        /* ----- FOOTER ----- */
        .footer-table {
            width: 100%;
            border-collapse: collapse;
            height: 100px;
            background: #ffffff;
        }

        .footer-left {
            width: 75%;
            background-image: url('{{ $footerBgBase64 }}');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            padding: 14px 24px;
            vertical-align: middle;
            color: #ffffff;
            border-top-right-radius: 50px;
            position: relative;
        }

        .footer-left::before {
            content: '';
            position: absolute;
            inset: 0;
            background: rgba(185, 28, 28, 0.85);
            border-top-right-radius: 50px;
            z-index: 1;
        }

        .footer-left .contact-row,
        .footer-left .contact-item {
            position: relative;
            z-index: 2;
        }

        .footer-left .contact-row {
            margin-bottom: 4px;
        }

        .footer-left .contact-item {
            display: inline-block;
            margin-right: 20px;
            color: #ffffff;
            font-size: 11px;
            font-weight: 500;
            vertical-align: middle;
        }

        .footer-left .contact-item .icon-circle {
            display: inline-block;
            width: 26px;
            height: 26px;
            background: #000000;
            border-radius: 50%;
            text-align: center;
            line-height: 26px;
            color: #d81524;
            font-size: 10px;
            margin-right: 8px;
            vertical-align: middle;
            font-weight: 800;
            font-family: Arial, Helvetica, sans-serif;
        }

        .footer-left .contact-item .icon-text {
            vertical-align: middle;
        }

        .footer-left .contact-item.full-width {
            display: block;
            margin-right: 0;
            margin-top: 2px;
        }

        .footer-right {
            width: 25%;
            background: #ffffff;
            text-align: center;
            vertical-align: bottom;
            padding: 0 12px 12px 12px;
        }

        .footer-right .signature-line {
            width: 70%;
            height: 2px;
            background: #000000;
            margin: 4px auto 0;
        }

        .footer-right .principal-label {
            font-weight: 700;
            text-transform: uppercase;
            font-size: 10px;
            letter-spacing: 0.5px;
            margin-top: 4px;
            color: #1a1a1a;
        }

        .footer-right .signature-text {
            font-family: 'Courier New', Courier, monospace;
            font-size: 18px;
            color: #1a1a1a;
            letter-spacing: 2px;
            margin-bottom: 2px;
        }

        /* ==========================================
           BACK PAGE
        ========================================== */

        .card-back {
            background: #ffffff;
        }

        .back-header {
            background: #d81524;
            padding: 14px 24px;
            border-bottom: 3px solid #b91c1c;
        }

        .back-header h2 {
            color: #ffffff;
            font-size: 20px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .back-content {
            padding: 20px 28px;
            min-height: 160px;
        }

        .back-content .policy-item {
            padding: 4px 0;
            font-size: 12px;
            color: #374151;
            line-height: 1.5;
        }

        .back-content .policy-item .bullet {
            display: inline-block;
            width: 7px;
            height: 7px;
            background: #d81524;
            border-radius: 2px;
            margin-right: 10px;
            vertical-align: middle;
            flex-shrink: 0;
        }

        .back-content .policy-item .policy-text {
            vertical-align: middle;
        }

        .back-footer {
            width: 100%;
            border-collapse: collapse;
            height: 100px;
            border-top: 1px solid #e5e7eb;
            background: #ffffff;
        }

        .back-footer .footer-left {
            width: 75%;
            background-image: url('{{ $footerBgBase64 }}');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            padding: 14px 24px;
            vertical-align: middle;
            color: #ffffff;
            border-top-right-radius: 50px;
            position: relative;
        }

        .back-footer .footer-left::before {
            content: '';
            position: absolute;
            inset: 0;
            background: rgba(185, 28, 28, 0.85);
            border-top-right-radius: 50px;
            z-index: 1;
        }

        .back-footer .footer-left .contact-row,
        .back-footer .footer-left .contact-item {
            position: relative;
            z-index: 2;
        }

        .back-footer .footer-left .contact-row {
            margin-bottom: 4px;
        }

        .back-footer .footer-left .contact-item {
            display: inline-block;
            margin-right: 20px;
            color: #ffffff;
            font-size: 11px;
            font-weight: 500;
            vertical-align: middle;
        }

        .back-footer .footer-left .contact-item .icon-circle {
            display: inline-block;
            width: 26px;
            height: 26px;
            background: #000000;
            border-radius: 50%;
            text-align: center;
            line-height: 26px;
            color: #d81524;
            font-size: 10px;
            margin-right: 8px;
            vertical-align: middle;
            font-weight: 800;
            font-family: Arial, Helvetica, sans-serif;
        }

        .back-footer .footer-left .contact-item .icon-text {
            vertical-align: middle;
        }

        .back-footer .footer-left .contact-item.full-width {
            display: block;
            margin-right: 0;
            margin-top: 2px;
        }

        .back-footer .footer-right {
            width: 25%;
            background: #ffffff;
            text-align: center;
            vertical-align: bottom;
            padding: 0 12px 12px 12px;
        }

        .back-footer .footer-right .signature-line {
            width: 70%;
            height: 2px;
            background: #000000;
            margin: 4px auto 0;
        }

        .back-footer .footer-right .principal-label {
            font-weight: 700;
            text-transform: uppercase;
            font-size: 10px;
            letter-spacing: 0.5px;
            margin-top: 4px;
            color: #1a1a1a;
        }

        .back-footer .footer-right .signature-text {
            font-family: 'Courier New', Courier, monospace;
            font-size: 18px;
            color: #1a1a1a;
            letter-spacing: 2px;
            margin-bottom: 2px;
        }

        /* ==========================================
           PRINT / PDF STYLES
        ========================================== */

        @page {
            margin: 0;
            size: A4 portrait;
        }

        @media print {
            body {
                background: #ffffff;
                padding: 0;
                margin: 0;
            }
            .id-card {
                border: none;
                max-width: 100%;
                border-radius: 0;
            }
            .card-front {
                page-break-after: always;
            }
            .card-back {
                page-break-before: auto;
            }
            .header-right,
            .footer-left,
            .back-header,
            .header-left .slogan-badge,
            .footer-left::before,
            .back-footer .footer-left::before {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                color-adjust: exact !important;
            }
            .body-table .photo-cell img {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
        }
    </style>
</head>
<body>

<div class="id-card">

    <!-- ==========================================
    FRONT PAGE
    ========================================== -->
    <div class="card-front">

        <!-- HEADER -->
        <table class="header-table">
            <tr>
                <td class="header-left">
                    <table style="width:100%;border-collapse:collapse;">
                        <tr>
                            <td class="logo-cell">
                                @if($logoBase64)
                                    <img src="{{ $logoBase64 }}" alt="Logo" class="logo">
                                @endif
                            </td>
                            <td class="school-cell">
                                <div class="school-name">
                                    <span class="line1">Kenswed</span>
                                    <span class="line2">Secondary School</span>
                                </div>
                                <div class="slogan-badge">
                                    <span class="line"></span>
                                    it seems impossible until it's done
                                    <span class="line"></span>
                                </div>
                            </td>
                        </tr>
                    </table>
                </td>
                <td class="header-right">
                    <h2>Students' Identity Card</h2>
                </td>
            </tr>
        </table>

        <!-- BODY -->
        <table class="body-table">
            <tr>
                <td class="photo-cell">
                    @if($photoBase64)
                        <img src="{{ $photoBase64 }}" alt="{{ $studentName }}">
                    @endif
                </td>

                <td class="info-cell">
                    <div class="student-name">{{ $studentName }}</div>

                    <div class="info-row">
                        <span class="label">Adm No</span>
                        <span class="value">: {{ $admNo }}</span>
                    </div>
                    <div class="info-row">
                        <span class="label">Class</span>
                        <span class="value">: {{ $class }}</span>
                    </div>
                    <div class="info-row">
                        <span class="label">Ass. No</span>
                        <span class="value">: {{ $stream }}</span>
                    </div>
                    <div class="info-row">
                        <span class="label">Date of Birth</span>
                        <span class="value">: {{ $dob }}</span>
                    </div>
                    <div class="info-row">
                        <span class="label">Gender</span>
                        <span class="value">: {{ $gender }}</span>
                    </div>
                    <div class="info-row">
                        <span class="label">Card No</span>
                        <span class="value mono">: {{ $cardNo }}</span>
                    </div>
                </td>

                <td class="qr-cell">
                    <div class="qr-box">
                        @if($qrBase64)
                            <img src="{{ $qrBase64 }}" alt="QR Code">
                        @else
                            <div class="no-qr">
                                No QR Code
                            </div>
                        @endif
                    </div>
                </td>
            </tr>
        </table>

        <!-- FOOTER -->
        <table class="footer-table">
            <tr>
                <td class="footer-left">
                    <div class="contact-row">
                        <span class="contact-item">
                            <span class="icon-circle">P</span>
                            <span class="icon-text">+254 743 237 432</span>
                        </span>
                        <span class="contact-item">
                            <span class="icon-circle">W</span>
                            <span class="icon-text">www.kenswed.org</span>
                        </span>
                    </div>
                    <span class="contact-item full-width">
                        <span class="icon-circle">L</span>
                        <span class="icon-text">Kibiko, Off Maragara road, Kajiado West</span>
                    </span>
                </td>
                <td class="footer-right">
                    <div class="signature-text">_ _ _ _ _ _ _ _ _ _</div>
                    <div class="signature-line"></div>
                    <div class="principal-label">Principal</div>
                </td>
            </tr>
        </table>

    </div>

    <!-- ==========================================
    BACK PAGE
    ========================================== -->
    <div class="card-back">

        <div class="back-header">
            <h2>School Policies</h2>
        </div>

        <div class="back-content">
            <div class="policy-item">
                <span class="bullet"></span>
                <span class="policy-text">Present this card upon request by any school official or security personnel.</span>
            </div>
            <div class="policy-item">
                <span class="bullet"></span>
                <span class="policy-text">This ID is non-transferable and remains the property of Kenswed Sec School.</span>
            </div>
            <div class="policy-item">
                <span class="bullet"></span>
                <span class="policy-text">Misuse or alteration of this card may lead to disciplinary action.</span>
            </div>
            <div class="policy-item">
                <span class="bullet"></span>
                <span class="policy-text">Loss must be reported immediately to the school Administration office.</span>
            </div>
            <div class="policy-item">
                <span class="bullet"></span>
                <span class="policy-text">Replacement cards are available at a fee.</span>
            </div>
            <div class="policy-item">
                <span class="bullet"></span>
                <span class="policy-text">All students are expected to adhere to the code of conduct available on the school website.</span>
            </div>
        </div>

        <!-- BACK FOOTER -->
        <table class="back-footer">
            <tr>
                <td class="footer-left">
                    <div class="contact-row">
                        <span class="contact-item">
                            <span class="icon-circle">P</span>
                            <span class="icon-text">+254 743 237 432</span>
                        </span>
                        <span class="contact-item">
                            <span class="icon-circle">W</span>
                            <span class="icon-text">www.kenswed.org</span>
                        </span>
                    </div>
                    <span class="contact-item full-width">
                        <span class="icon-circle">L</span>
                        <span class="icon-text">Kibiko, Off Maragara road, Kajiado West</span>
                    </span>
                </td>
                <td class="footer-right">
                    <div class="signature-text">_ _ _ _ _ _ _ _ _ _</div>
                    <div class="signature-line"></div>
                    <div class="principal-label">Principal</div>
                </td>
            </tr>
        </table>

    </div>

</div>

</body>
</html>