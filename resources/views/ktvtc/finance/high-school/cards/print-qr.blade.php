{{--
    resources/views/ktvtc/finance/high-school/cards/print-id.blade.php
    Full ID Card Print View - Uses jsPDF + html2canvas for high-quality PDF generation
    FIXED: No distortion, proper scaling, background images preserved
--}}

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student ID Card - {{ $card->student_name }}</title>

    {{-- Font Awesome for icons --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

    {{-- Use jsPDF + html2canvas directly for better control --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

    @php
        $student = $card->student;
        $photoUrl = $student && $student->profile_picture && file_exists(public_path('storage/' . $student->profile_picture))
            ? asset('storage/' . $student->profile_picture)
            : asset('images/default/avatar.png');

        $qrUrl = $card->qr_code && Storage::disk('public')->exists($card->qr_code)
            ? asset('storage/' . $card->qr_code)
            : null;

        $signatureExists = file_exists(public_path('images/signatures/principal.png'));
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
            background: #f3f4f6;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        /* ===== CARD WRAPPER ===== */
        .id-card {
            max-width: 1000px;
            width: 100%;
            background: #ffffff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.12);
            color: #1a1a1a;
            page-break-inside: avoid;
            transform-origin: top left;
            image-rendering: -webkit-optimize-contrast;
            image-rendering: crisp-edges;
        }

        /* Fix for background images rendering */
        .id-card .header-right,
        .id-card .footer-left,
        .id-card .card-back .back-footer .footer-left {
            -webkit-transform: translateZ(0);
            transform: translateZ(0);
            will-change: transform;
        }

        /* ==========================================
           HEADER - MATCHES PREVIEW
        ========================================== */

        .id-card .card-header {
            position: relative;
            display: flex;
            align-items: center;
            height: 130px;
            background: #ffffff;
            overflow: hidden;
        }

        .id-card .header-left {
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 0 24px;
            z-index: 10;
            position: relative;
            width: 55%;
            flex-shrink: 0;
        }

        .id-card .header-left .logo {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #e5e7eb;
            background: #ffffff;
            flex-shrink: 0;
        }

        .id-card .school-details {
            display: flex;
            flex-direction: column;
        }

        .id-card .school-name {
            font-size: 22px;
            font-weight: 900;
            text-transform: uppercase;
            line-height: 1.1;
            color: #1a1a1a;
        }

        .id-card .school-name .line1 {
            display: block;
        }

        .id-card .school-name .line2 {
            display: block;
        }

        .id-card .slogan-badge {
            background: #d81524;
            color: #ffffff;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 3px 12px;
            font-size: 8px;
            font-weight: 700;
            text-transform: uppercase;
            border-radius: 4px;
            margin-top: 5px;
            white-space: nowrap;
            align-self: flex-start;
        }

        .id-card .slogan-badge .line {
            display: inline-block;
            width: 12px;
            height: 2px;
            background: #ffffff;
        }

        /* RIGHT SIDE - Background Image ONLY */
        .id-card .header-right {
            position: absolute;
            right: 0;
            top: 0;
            height: 100%;
            width: 45%;
            background: url('{{ asset('images/id-card/svg-bg.png') }}') center/cover no-repeat;
            display: flex;
            align-items: center;
            justify-content: center;
            padding-left: 20px;
            z-index: 1;
        }

        .id-card .header-right h2 {
            position: relative;
            z-index: 2;
            color: #ffffff;
            font-size: 22px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 2px;
            text-align: center;
            padding: 0 20px;
            line-height: 1.2;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.7), 0 0 30px rgba(0, 0, 0, 0.3);
        }

        /* ==========================================
           BODY - MATCHES PREVIEW
        ========================================== */

        .id-card .card-body {
            display: grid;
            grid-template-columns: 1fr 2fr 1fr;
            gap: 24px;
            padding: 28px 36px;
            align-items: center;
            background: #ffffff;
            position: relative;
            z-index: 2;
        }

        .id-card .photo-wrapper {
            display: flex;
            justify-content: center;
        }

        .id-card .photo-wrapper img {
            width: 100%;
            max-width: 160px;
            aspect-ratio: 1/1;
            object-fit: cover;
            border-radius: 35px;
            border: 5px solid #d81524;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .id-card .student-info h3 {
            font-size: 20px;
            font-weight: 700;
            text-transform: uppercase;
            margin-bottom: 6px;
            color: #1a1a1a;
        }

        .id-card .info-grid {
            display: grid;
            grid-template-columns: auto auto 1fr;
            gap: 2px 12px;
            font-size: 13px;
            font-weight: 500;
        }

        .id-card .info-grid .label {
            color: #6b7280;
        }

        .id-card .info-grid .value {
            color: #d81524;
        }

        .id-card .qr-wrapper {
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .id-card .qr-wrapper .qr-box {
            width: 160px;
            height: 160px;
            border: 2px dashed #9ca3af;
            border-radius: 12px;
            background: #f9fafb;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 12px;
        }

        .id-card .qr-wrapper .qr-box img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .id-card .qr-wrapper .qr-box .no-qr {
            text-align: center;
            color: #9ca3af;
            font-size: 12px;
        }

        .id-card .qr-wrapper .qr-box .no-qr i {
            font-size: 32px;
            display: block;
            margin-bottom: 4px;
        }

        /* ==========================================
           FOOTER - MATCHES PREVIEW (with background image)
        ========================================== */

        .id-card .card-footer {
            display: flex;
            flex-direction: row;
            width: 100%;
            height: 130px;
            position: relative;
            z-index: 2;
            background: #ffffff;
        }

        .id-card .footer-left {
            flex: 1;
            background: url('{{ asset('images/id-card/footer-bg.png') }}') center/cover no-repeat;
            border-top-right-radius: 70px;
            padding: 18px 28px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            gap: 6px;
            position: relative;
        }

        .id-card .footer-left::before {
            content: '';
            position: absolute;
            inset: 0;
            background: rgba(185, 28, 28, 0.85);
            border-top-right-radius: 70px;
            z-index: 1;
        }

        .id-card .footer-left .contact-row,
        .id-card .footer-left .contact-item {
            position: relative;
            z-index: 2;
        }

        .id-card .footer-left .contact-row {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 12px 24px;
        }

        .id-card .footer-left .contact-item {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #ffffff;
            font-size: 12px;
            font-weight: 500;
        }

        .id-card .footer-left .contact-item .icon-circle {
            width: 26px;
            height: 26px;
            background: #000000;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .id-card .footer-left .contact-item .icon-circle i {
            color: #d81524;
            font-size: 11px;
        }

        .id-card .footer-right {
            width: 25%;
            background: #ffffff;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-end;
            padding: 0 12px 16px 12px;
            position: relative;
            z-index: 2;
        }

        .id-card .footer-right .signature {
            width: 100px;
            height: 35px;
        }

        .id-card .footer-right .signature img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .id-card .footer-right .signature-line {
            width: 75%;
            height: 2px;
            background: #000000;
            margin-top: 4px;
        }

        .id-card .footer-right .principal-label {
            font-weight: 700;
            text-transform: uppercase;
            font-size: 10px;
            letter-spacing: 0.5px;
            margin-top: 4px;
            color: #1a1a1a;
        }

        /* ==========================================
           BACK OF CARD - MATCHES PREVIEW (with background image)
        ========================================== */

        .id-card .card-back {
            border-top: 1px solid #e5e7eb;
            margin-top: 4px;
            background: #ffffff;
            page-break-inside: avoid;
            page-break-before: always;
        }

        .id-card .card-back .back-content {
            padding: 28px 40px;
            min-height: 180px;
        }

        .id-card .card-back .back-content h2 {
            color: #d81524;
            font-size: 24px;
            font-weight: 700;
            text-transform: uppercase;
            margin-bottom: 16px;
        }

        .id-card .card-back .back-content ul {
            list-style: none;
            padding: 0;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .id-card .card-back .back-content ul li {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            font-size: 13px;
            color: #374151;
            line-height: 1.5;
        }

        .id-card .card-back .back-content ul li .bullet {
            width: 8px;
            height: 8px;
            background: #d81524;
            border-radius: 2px;
            flex-shrink: 0;
            margin-top: 5px;
        }

        .id-card .card-back .back-footer {
            display: flex;
            flex-direction: row;
            width: 100%;
            height: 130px;
            border-top: 1px solid #e5e7eb;
            background: #ffffff;
        }

        .id-card .card-back .back-footer .footer-left {
            flex: 1;
            background: url('{{ asset('images/id-card/footer-bg.png') }}') center/cover no-repeat;
            border-top-right-radius: 70px;
            padding: 18px 28px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            gap: 6px;
            position: relative;
        }

        .id-card .card-back .back-footer .footer-left::before {
            content: '';
            position: absolute;
            inset: 0;
            background: rgba(185, 28, 28, 0.85);
            border-top-right-radius: 70px;
            z-index: 1;
        }

        .id-card .card-back .back-footer .footer-left .contact-row,
        .id-card .card-back .back-footer .footer-left .contact-item {
            position: relative;
            z-index: 2;
        }

        .id-card .card-back .back-footer .footer-left .contact-row {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 12px 24px;
        }

        .id-card .card-back .back-footer .footer-left .contact-item {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #ffffff;
            font-size: 12px;
            font-weight: 500;
        }

        .id-card .card-back .back-footer .footer-left .contact-item .icon-circle {
            width: 26px;
            height: 26px;
            background: #000000;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .id-card .card-back .back-footer .footer-left .contact-item .icon-circle i {
            color: #d81524;
            font-size: 11px;
        }

        .id-card .card-back .back-footer .footer-right {
            width: 25%;
            background: #ffffff;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-end;
            padding: 0 12px 16px 12px;
            position: relative;
            z-index: 2;
        }

        .id-card .card-back .back-footer .footer-right .signature {
            width: 100px;
            height: 35px;
        }

        .id-card .card-back .back-footer .footer-right .signature img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .id-card .card-back .back-footer .footer-right .signature-line {
            width: 75%;
            height: 2px;
            background: #000000;
            margin-top: 4px;
        }

        .id-card .card-back .back-footer .footer-right .principal-label {
            font-weight: 700;
            text-transform: uppercase;
            font-size: 10px;
            letter-spacing: 0.5px;
            margin-top: 4px;
            color: #1a1a1a;
        }

        /* ==========================================
           BUTTONS
        ========================================== */

        .actions {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 20px;
            justify-content: center;
        }

        .actions button {
            padding: 12px 32px;
            border: none;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .actions .btn-pdf {
            background: #d81524;
            color: #ffffff;
        }

        .actions .btn-pdf:hover {
            background: #b91c1c;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(216, 21, 36, 0.3);
        }

        .actions .btn-pdf:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        .actions .btn-print {
            background: #4b5563;
            color: #ffffff;
        }

        .actions .btn-print:hover {
            background: #374151;
            transform: translateY(-1px);
        }

        .actions .btn-close {
            background: #e5e7eb;
            color: #374151;
        }

        .actions .btn-close:hover {
            background: #d1d5db;
        }

        /* ===== LOADING OVERLAY ===== */
        #loadingOverlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.6);
            z-index: 9999;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            gap: 16px;
        }

        #loadingOverlay.active {
            display: flex;
        }

        #loadingOverlay .spinner {
            width: 50px;
            height: 50px;
            border: 4px solid #ffffff;
            border-top-color: #d81524;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }

        #loadingOverlay p {
            color: #ffffff;
            font-size: 18px;
            font-weight: 600;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        /* ==========================================
           PRINT STYLES
        ========================================== */

        @media print {
            html, body {
                background: #ffffff !important;
                padding: 0 !important;
                margin: 0 !important;
                min-height: auto !important;
                display: block !important;
            }

            .id-card {
                max-width: 100% !important;
                border-radius: 0 !important;
                box-shadow: none !important;
                page-break-inside: avoid !important;
                margin: 0 auto !important;
                border: none !important;
            }

            .id-card .card-back {
                page-break-before: always !important;
                page-break-inside: avoid !important;
            }

            /* Ensure backgrounds print properly */
            .id-card .header-right,
            .id-card .footer-left,
            .id-card .footer-left::before,
            .id-card .card-back .back-footer .footer-left,
            .id-card .card-back .back-footer .footer-left::before,
            .id-card .slogan-badge {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                color-adjust: exact !important;
            }

            /* Fix for image rendering in print */
            .id-card img,
            .id-card .logo,
            .id-card .photo-wrapper img,
            .id-card .qr-wrapper .qr-box img {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            .actions {
                display: none !important;
            }

            #loadingOverlay {
                display: none !important;
            }
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 768px) {
            .id-card .card-header {
                height: 100px;
            }

            .id-card .header-left {
                padding: 0 12px;
                gap: 10px;
                width: 55%;
            }

            .id-card .header-left .logo {
                width: 70px;
                height: 70px;
            }

            .id-card .school-name {
                font-size: 16px;
            }

            .id-card .header-right {
                width: 45%;
                padding-left: 15px;
            }

            .id-card .header-right h2 {
                font-size: 14px;
                padding: 0 12px;
            }

            .id-card .card-body {
                grid-template-columns: 1fr;
                padding: 20px;
                gap: 16px;
            }

            .id-card .photo-wrapper img {
                max-width: 120px;
                margin: 0 auto;
            }

            .id-card .qr-wrapper .qr-box {
                width: 130px;
                height: 130px;
                margin: 0 auto;
            }

            .id-card .card-footer {
                flex-direction: column;
                height: auto;
            }

            .id-card .footer-right {
                width: 100%;
                padding: 12px;
                flex-direction: row;
                justify-content: center;
                align-items: center;
                gap: 16px;
                border-top: 1px solid #e5e7eb;
            }

            .id-card .footer-left {
                border-top-right-radius: 40px;
                padding: 14px 18px;
            }

            .id-card .card-back .back-content {
                padding: 16px 20px;
                min-height: auto;
            }

            .id-card .card-back .back-content h2 {
                font-size: 18px;
            }

            .id-card .card-back .back-footer {
                flex-direction: column;
                height: auto;
            }

            .id-card .card-back .back-footer .footer-right {
                width: 100%;
            }

            .id-card .card-back .back-footer .footer-left {
                border-top-right-radius: 40px;
                padding: 14px 18px;
            }
        }

        @media (max-width: 480px) {
            .id-card .header-left {
                gap: 8px;
                padding: 0 8px;
                width: 58%;
            }

            .id-card .header-left .logo {
                width: 50px;
                height: 50px;
            }

            .id-card .school-name {
                font-size: 13px;
            }

            .id-card .header-right {
                width: 42%;
                padding-left: 10px;
            }

            .id-card .header-right h2 {
                font-size: 11px;
                padding: 0 8px;
            }

            .id-card .info-grid {
                font-size: 11px;
                gap: 1px 6px;
            }

            .id-card .student-info h3 {
                font-size: 14px;
            }

            .id-card .qr-wrapper .qr-box {
                width: 100px;
                height: 100px;
            }

            .id-card .slogan-badge {
                font-size: 6px;
                padding: 2px 8px;
            }
        }
    </style>
</head>
<body>

    <!-- ===== LOADING OVERLAY ===== -->
    <div id="loadingOverlay">
        <div class="spinner"></div>
        <p>Generating PDF...</p>
        <p style="font-size:14px;font-weight:400;color:#d1d5db;">Please wait, this may take a moment</p>
    </div>

    <!-- ===== ID CARD ===== -->
    <div class="id-card" id="idCard">

        <!-- ==================== FRONT (Page 1) ==================== -->
        <div class="card-front">

            <div class="card-header">

                <!-- LEFT SIDE -->
                <div class="header-left">

                    <img
                        src="{{ asset('images/logo/main-logo.png') }}"
                        alt="School Logo"
                        class="logo"
                        onerror="this.src='{{ asset('images/default/avatar.png') }}'"
                        crossorigin="anonymous"
                    >

                    <div class="school-details">

                        <div class="school-name">
                            <span class="line1">Kenswed</span>
                            <span class="line2">Secondary School</span>
                        </div>

                        <div class="slogan-badge">
                            <span class="line"></span>
                            it seems impossible until it's done
                            <span class="line"></span>
                        </div>

                    </div>

                </div>

                <!-- RIGHT SIDE - Background Image ONLY -->
                <div class="header-right">
                    <h2>Students' Identity Card</h2>
                </div>

            </div>

            <!-- Middle -->
            <div class="card-body">

                <!-- Photo -->
                <div class="photo-wrapper">
                    <img src="{{ $photoUrl }}" alt="{{ $card->student_name }}" crossorigin="anonymous">
                </div>

                <!-- Student Info -->
                <div class="student-info">
                    <h3>{{ $card->student_name }}</h3>
                    <div class="info-grid">
                        <span class="label">Adm No</span>
                        <span>:</span>
                        <span class="value">{{ $card->student_admission_number }}</span>

                        <span class="label">Class</span>
                        <span>:</span>
                        <span class="value">{{ $card->student_class }}</span>

                        <span class="label">Ass. No</span>
                        <span>:</span>
                        <span class="value">{{ $student && $student->stream ? $student->stream : 'N/A' }}</span>

                        <span class="label">Date of Birth</span>
                        <span>:</span>
                        <span class="value">{{ $student && $student->date_of_birth ? $student->date_of_birth->format('d/m/Y') : 'N/A' }}</span>

                        <span class="label">Gender</span>
                        <span>:</span>
                        <span class="value">{{ $student ? ucfirst($student->gender) : 'N/A' }}</span>

                        <span class="label">Card No</span>
                        <span>:</span>
                        <span class="value" style="font-family: monospace; font-size: 12px;">{{ $card->card_number }}</span>
                    </div>
                </div>

                <!-- QR Code -->
                <div class="qr-wrapper">
                    <div class="qr-box">
                        @if($qrUrl)
                            <img src="{{ $qrUrl }}" alt="QR Code" crossorigin="anonymous">
                        @else
                            <div class="no-qr">
                                <i class="fas fa-qrcode"></i>
                                No QR Code
                            </div>
                        @endif
                    </div>
                </div>

            </div>

            <!-- Footer with background image -->
            <div class="card-footer">
                <div class="footer-left">
                    <div class="contact-row">
                        <div class="contact-item">
                            <div class="icon-circle">
                                <i class="fas fa-phone"></i>
                            </div>
                            <span>+254 743 237 432</span>
                        </div>
                        <div class="contact-item">
                            <div class="icon-circle">
                                <i class="fas fa-globe"></i>
                            </div>
                            <span>www.kenswed.org</span>
                        </div>
                    </div>
                    <div class="contact-item">
                        <div class="icon-circle">
                            <i class="fas fa-location-dot"></i>
                        </div>
                        <span>Kibiko, Off Maragara road, Kajiado West</span>
                    </div>
                </div>
                <div class="footer-right">
                    @if($signatureExists)
                        <div class="signature">
                            <img src="{{ asset('images/signatures/principal.png') }}" alt="Principal Signature" crossorigin="anonymous">
                        </div>
                    @else
                        <svg class="signature" viewBox="0 0 200 80" xmlns="http://www.w3.org/2000/svg">
                            <path d="M10,40 C40,80 80,-10 110,50 C140,110 170,20 190,40" fill="none" stroke="#1a1a1a" stroke-width="3" stroke-linecap="round"/>
                        </svg>
                    @endif
                    <div class="signature-line"></div>
                    <span class="principal-label">Principal</span>
                </div>
            </div>

        </div>

        <!-- ==================== BACK (Page 2) ==================== -->
        <div class="card-back">

            <div class="back-content">
                <h2>School Policies</h2>
                <ul>
                    <li>
                        <span class="bullet"></span>
                        <span>Present this card upon request by any school official or security personnel.</span>
                    </li>
                    <li>
                        <span class="bullet"></span>
                        <span>This ID is non-transferable and remains the property of Kenswed Sec School.</span>
                    </li>
                    <li>
                        <span class="bullet"></span>
                        <span>Misuse or alteration of this card may lead to disciplinary action.</span>
                    </li>
                    <li>
                        <span class="bullet"></span>
                        <span>Loss must be reported immediately to the school Administration office.</span>
                    </li>
                    <li>
                        <span class="bullet"></span>
                        <span>Replacement cards are available at a fee.</span>
                    </li>
                    <li>
                        <span class="bullet"></span>
                        <span>All students are expected to adhere to the code of conduct available on the school website.</span>
                    </li>
                </ul>
            </div>

            <!-- Back Footer with background image -->
            <div class="back-footer">
                <div class="footer-left">
                    <div class="contact-row">
                        <div class="contact-item">
                            <div class="icon-circle">
                                <i class="fas fa-phone"></i>
                            </div>
                            <span>+254 743 237 432</span>
                        </div>
                        <div class="contact-item">
                            <div class="icon-circle">
                                <i class="fas fa-globe"></i>
                            </div>
                            <span>www.kenswed.org</span>
                        </div>
                    </div>
                    <div class="contact-item">
                        <div class="icon-circle">
                            <i class="fas fa-location-dot"></i>
                        </div>
                        <span>Kibiko, Off Maragara road, Kajiado West</span>
                    </div>
                </div>
                <div class="footer-right">
                    @if($signatureExists)
                        <div class="signature">
                            <img src="{{ asset('images/signatures/principal.png') }}" alt="Principal Signature" crossorigin="anonymous">
                        </div>
                    @else
                        <svg class="signature" viewBox="0 0 200 80" xmlns="http://www.w3.org/2000/svg">
                            <path d="M10,40 C40,80 80,-10 110,50 C140,110 170,20 190,40" fill="none" stroke="#1a1a1a" stroke-width="3" stroke-linecap="round"/>
                        </svg>
                    @endif
                    <div class="signature-line"></div>
                    <span class="principal-label">Principal</span>
                </div>
            </div>

        </div>

    </div>

    <!-- ===== BUTTONS ===== -->
    <div class="actions">
        <button class="btn-pdf" id="downloadPdfBtn" onclick="downloadPDF()">
            <i class="fas fa-file-pdf"></i> Download PDF
        </button>
        <button class="btn-print" onclick="window.print()">
            <i class="fas fa-print"></i> Print
        </button>
        <button class="btn-close" onclick="window.close()">
            <i class="fas fa-times"></i> Close
        </button>
    </div>

    <!-- ===== JAVASCRIPT ===== -->
    <script>
        function downloadPDF() {
            const element = document.getElementById('idCard');
            const btn = document.getElementById('downloadPdfBtn');
            const overlay = document.getElementById('loadingOverlay');

            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generating...';
            overlay.classList.add('active');

            const cardNumber = '{{ $card->card_number }}';

            // Wait for all images to load before generating PDF
            const images = element.getElementsByTagName('img');
            let loadedImages = 0;
            const totalImages = images.length;

            function generatePDF() {
                // Use html2canvas with high quality settings
                html2canvas(element, {
                    scale: 4, // Higher scale for better quality
                    useCORS: true,
                    backgroundColor: '#ffffff',
                    logging: false,
                    allowTaint: true,
                    width: element.scrollWidth,
                    height: element.scrollHeight,
                    windowWidth: element.scrollWidth,
                    windowHeight: element.scrollHeight,
                    onclone: function(document) {
                        // Ensure all styles are applied in the clone
                        const clonedElement = document.getElementById('idCard');
                        if (clonedElement) {
                            clonedElement.style.transform = 'none';
                        }
                    }
                }).then(canvas => {
                    try {
                        const imgData = canvas.toDataURL('image/jpeg', 1.0);
                        
                        // Create PDF with A4 dimensions
                        const pdf = new jspdf.jsPDF({
                            unit: 'mm',
                            format: 'a4',
                            orientation: 'portrait'
                        });

                        const pdfWidth = pdf.internal.pageSize.getWidth();
                        const pdfHeight = pdf.internal.pageSize.getHeight();
                        const imgWidth = canvas.width;
                        const imgHeight = canvas.height;
                        
                        // Calculate aspect ratio to fit the card on one page
                        const ratio = Math.min(pdfWidth / imgWidth, pdfHeight / imgHeight);
                        const imgX = (pdfWidth - imgWidth * ratio) / 2;
                        const imgY = (pdfHeight - imgHeight * ratio) / 2;
                        
                        pdf.addImage(imgData, 'JPEG', imgX, imgY, imgWidth * ratio, imgHeight * ratio);
                        pdf.save('ID-Card-' + cardNumber + '.pdf');
                        
                        btn.disabled = false;
                        btn.innerHTML = '<i class="fas fa-file-pdf"></i> Download PDF';
                        overlay.classList.remove('active');
                    } catch (error) {
                        console.error('PDF generation failed:', error);
                        btn.disabled = false;
                        btn.innerHTML = '<i class="fas fa-file-pdf"></i> Retry Download';
                        overlay.classList.remove('active');
                        alert('Failed to generate PDF. Please try again or use Print > Save as PDF.');
                    }
                }).catch(error => {
                    console.error('HTML2Canvas failed:', error);
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-file-pdf"></i> Retry Download';
                    overlay.classList.remove('active');
                    alert('Failed to generate PDF. Please try again or use Print > Save as PDF.');
                });
            }

            // Check if all images are loaded
            if (totalImages === 0) {
                generatePDF();
            } else {
                let imagesLoaded = 0;
                for (let img of images) {
                    if (img.complete && img.naturalHeight !== 0) {
                        imagesLoaded++;
                        if (imagesLoaded === totalImages) {
                            generatePDF();
                        }
                    } else {
                        img.onload = function() {
                            imagesLoaded++;
                            if (imagesLoaded === totalImages) {
                                generatePDF();
                            }
                        };
                        img.onerror = function() {
                            imagesLoaded++;
                            if (imagesLoaded === totalImages) {
                                generatePDF();
                            }
                        };
                    }
                }
            }
        }

        // Auto-download if triggered from URL parameter
        @if(request('download'))
            window.onload = function() {
                setTimeout(downloadPDF, 1000);
            };
        @endif
    </script>

</body>
</html>