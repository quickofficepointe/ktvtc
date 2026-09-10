{{--
    resources/views/ktvtc/finance/high-school/cards/partials/id-card-preview.blade.php
    ID Card Preview - FINAL CORRECTED VERSION
--}}

@php
    $student = $card->student;
    $photoUrl = $student && $student->profile_picture
        ? asset('storage/' . $student->profile_picture)
        : asset('images/default/avatar.png');

    $qrUrl = $card->qr_code && Storage::disk('public')->exists($card->qr_code)
        ? asset('storage/' . $card->qr_code)
        : null;
@endphp

<style>
    .id-card-preview {
        font-family: Arial, Helvetica, sans-serif;
        color: #1a1a1a;
        background: #ffffff;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.12);
        max-width: 1000px;
        margin: 0 auto;
        position: relative;
    }

    /* ==========================================
       HEADER SECTION
    ========================================== */

    .id-card-preview .card-header {
        position: relative;
        display: flex;
        align-items: center;
        height: 130px;
        background: #ffffff;
        overflow: hidden;
    }

    /* LEFT SIDE - School Branding */
    .id-card-preview .header-left {
        display: flex;
        align-items: center;
        gap: 16px;
        padding: 0 24px;
        z-index: 10;
        position: relative;
        width: 55%;
        flex-shrink: 0;
    }

    .id-card-preview .header-left .logo {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid #e5e7eb;
        background: #ffffff;
        flex-shrink: 0;
    }

    .id-card-preview .school-details {
        display: flex;
        flex-direction: column;
    }

    /* ✅ Kenswed on line 1, Secondary School on line 2 */
    .id-card-preview .school-name {
        font-size: 22px;
        font-weight: 900;
        text-transform: uppercase;
        line-height: 1.1;
        color: #1a1a1a;
    }

    .id-card-preview .school-name .line1 {
        display: block;
    }

    .id-card-preview .school-name .line2 {
        display: block;
    }

    /* ✅ Motto - stays in left section, does NOT stretch */
    .id-card-preview .slogan-badge {
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
        max-width: 100%;
    }

    .id-card-preview .slogan-badge .line {
        display: inline-block;
        width: 12px;
        height: 2px;
        background: #ffffff;
    }

    /* RIGHT SIDE - Red Banner with BACKGROUND IMAGE VISIBLE */
    .id-card-preview .header-right {
        position: absolute;
        right: 0;
        top: 0;
        height: 100%;
        width: 45%;
        /* ✅ ONLY the image - NO red overlay */
        background: url('{{ asset('images/id-card/svg-bg.png') }}') center/cover no-repeat;
        display: flex;
        align-items: center;
        justify-content: center;
        padding-left: 20px;
        z-index: 1;
    }

    /* ✅ NO ::before overlay - removed completely */

    /* ✅ Text on ONE line */
    .id-card-preview .header-right h2 {
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
       BODY SECTION
    ========================================== */

    .id-card-preview .card-body {
        display: grid;
        grid-template-columns: 1fr 2fr 1fr;
        gap: 24px;
        padding: 28px 36px;
        align-items: center;
        background: #ffffff;
        position: relative;
        z-index: 2;
    }

    .id-card-preview .photo-wrapper {
        display: flex;
        justify-content: center;
    }

    .id-card-preview .photo-wrapper img {
        width: 100%;
        max-width: 160px;
        aspect-ratio: 1/1;
        object-fit: cover;
        border-radius: 35px;
        border: 5px solid #d81524;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .id-card-preview .student-info h3 {
        font-size: 20px;
        font-weight: 700;
        text-transform: uppercase;
        margin-bottom: 6px;
        color: #1a1a1a;
    }

    .id-card-preview .info-grid {
        display: grid;
        grid-template-columns: auto auto 1fr;
        gap: 2px 12px;
        font-size: 13px;
        font-weight: 500;
    }

    .id-card-preview .info-grid .label {
        color: #6b7280;
    }

    .id-card-preview .info-grid .value {
        color: #d81524;
    }

    .id-card-preview .qr-wrapper {
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .id-card-preview .qr-wrapper .qr-box {
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

    .id-card-preview .qr-wrapper .qr-box img {
        width: 100%;
        height: 100%;
        object-fit: contain;
    }

    .id-card-preview .qr-wrapper .qr-box .no-qr {
        text-align: center;
        color: #9ca3af;
        font-size: 12px;
    }

    .id-card-preview .qr-wrapper .qr-box .no-qr i {
        font-size: 32px;
        display: block;
        margin-bottom: 4px;
    }

    /* ==========================================
       FOOTER SECTION
    ========================================== */

    .id-card-preview .card-footer {
        display: flex;
        flex-direction: row;
        width: 100%;
        height: 130px;
        position: relative;
        z-index: 2;
        background: #ffffff;
    }

    .id-card-preview .footer-left {
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

    .id-card-preview .footer-left::before {
        content: '';
        position: absolute;
        inset: 0;
        background: rgba(185, 28, 28, 0.85);
        border-top-right-radius: 70px;
        z-index: 1;
    }

    .id-card-preview .footer-left .contact-row,
    .id-card-preview .footer-left .contact-item {
        position: relative;
        z-index: 2;
    }

    .id-card-preview .footer-left .contact-row {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 12px 24px;
    }

    .id-card-preview .footer-left .contact-item {
        display: flex;
        align-items: center;
        gap: 8px;
        color: #ffffff;
        font-size: 12px;
        font-weight: 500;
    }

    .id-card-preview .footer-left .contact-item .icon-circle {
        width: 26px;
        height: 26px;
        background: #000000;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .id-card-preview .footer-left .contact-item .icon-circle i {
        color: #d81524;
        font-size: 11px;
    }

    .id-card-preview .footer-right {
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

    .id-card-preview .footer-right .signature {
        width: 100px;
        height: 35px;
    }

    .id-card-preview .footer-right .signature-line {
        width: 75%;
        height: 2px;
        background: #000000;
        margin-top: 4px;
    }

    .id-card-preview .footer-right .principal-label {
        font-weight: 700;
        text-transform: uppercase;
        font-size: 10px;
        letter-spacing: 0.5px;
        margin-top: 4px;
        color: #1a1a1a;
    }

    /* ==========================================
       BACK OF CARD
    ========================================== */

    .id-card-preview .card-back {
        border-top: 1px solid #e5e7eb;
        margin-top: 4px;
        background: #ffffff;
        position: relative;
        z-index: 2;
    }

    .id-card-preview .card-back .back-content {
        padding: 28px 40px;
        min-height: 180px;
    }

    .id-card-preview .card-back .back-content h2 {
        color: #d81524;
        font-size: 24px;
        font-weight: 700;
        text-transform: uppercase;
        margin-bottom: 16px;
    }

    .id-card-preview .card-back .back-content ul {
        list-style: none;
        padding: 0;
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .id-card-preview .card-back .back-content ul li {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        font-size: 13px;
        color: #374151;
        line-height: 1.5;
    }

    .id-card-preview .card-back .back-content ul li .bullet {
        width: 8px;
        height: 8px;
        background: #d81524;
        border-radius: 2px;
        flex-shrink: 0;
        margin-top: 5px;
    }

    .id-card-preview .card-back .back-footer {
        display: flex;
        flex-direction: row;
        width: 100%;
        height: 130px;
        border-top: 1px solid #e5e7eb;
        background: #ffffff;
    }

    /* ==========================================
       RESPONSIVE STYLES
    ========================================== */

    @media (max-width: 768px) {
        .id-card-preview .card-header {
            height: 100px;
        }

        .id-card-preview .header-left {
            padding: 0 12px;
            gap: 10px;
            width: 55%;
        }

        .id-card-preview .header-left .logo {
            width: 70px;
            height: 70px;
        }

        .id-card-preview .school-name {
            font-size: 16px;
        }

        .id-card-preview .header-right {
            width: 45%;
            padding-left: 15px;
        }

        .id-card-preview .header-right h2 {
            font-size: 14px;
            padding: 0 12px;
        }

        .id-card-preview .card-body {
            grid-template-columns: 1fr;
            padding: 20px;
            gap: 16px;
        }

        .id-card-preview .photo-wrapper img {
            max-width: 120px;
            margin: 0 auto;
        }

        .id-card-preview .qr-wrapper .qr-box {
            width: 130px;
            height: 130px;
            margin: 0 auto;
        }

        .id-card-preview .card-footer {
            flex-direction: column;
            height: auto;
        }

        .id-card-preview .footer-right {
            width: 100%;
            padding: 12px;
            flex-direction: row;
            justify-content: center;
            align-items: center;
            gap: 16px;
            border-top: 1px solid #e5e7eb;
        }

        .id-card-preview .footer-left {
            border-top-right-radius: 40px;
            padding: 14px 18px;
        }

        .id-card-preview .card-back .back-content {
            padding: 16px 20px;
            min-height: auto;
        }

        .id-card-preview .card-back .back-content h2 {
            font-size: 18px;
        }

        .id-card-preview .card-back .back-footer {
            flex-direction: column;
            height: auto;
        }

        .id-card-preview .card-back .back-footer .footer-right {
            width: 100%;
        }
    }

    @media (max-width: 480px) {
        .id-card-preview .header-left {
            gap: 8px;
            padding: 0 8px;
            width: 58%;
        }

        .id-card-preview .header-left .logo {
            width: 50px;
            height: 50px;
        }

        .id-card-preview .school-name {
            font-size: 13px;
        }

        .id-card-preview .header-right {
            width: 42%;
            padding-left: 10px;
        }

        .id-card-preview .header-right h2 {
            font-size: 11px;
            padding: 0 8px;
        }

        .id-card-preview .info-grid {
            font-size: 11px;
            gap: 1px 6px;
        }

        .id-card-preview .student-info h3 {
            font-size: 14px;
        }

        .id-card-preview .qr-wrapper .qr-box {
            width: 100px;
            height: 100px;
        }

        .id-card-preview .slogan-badge {
            font-size: 6px;
            padding: 2px 8px;
        }
    }
</style>

<div class="id-card-preview" id="idCardPreview">

    <!-- ==================== FRONT ==================== -->
    <div class="card-header">

        <!-- LEFT SIDE -->
        <div class="header-left">

            <img
                src="{{ asset('images/logo/main-logo.png') }}"
                alt="School Logo"
                class="logo"
                onerror="this.src='{{ asset('images/default/avatar.png') }}'"
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

        <!-- RIGHT SIDE - Background Image ONLY (NO red overlay) -->
        <div class="header-right">
            <h2>Students' Identity Card</h2>
        </div>

    </div>

    <!-- Middle -->
    <div class="card-body">

        <!-- Photo -->
        <div class="photo-wrapper">
            <img src="{{ $photoUrl }}" alt="{{ $card->student_name }}">
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
                    <img src="{{ $qrUrl }}" alt="QR Code">
                @else
                    <div class="no-qr">
                        <i class="fas fa-qrcode"></i>
                        No QR Code
                    </div>
                @endif
            </div>
        </div>

    </div>

    <!-- Footer -->
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
            <svg class="signature" viewBox="0 0 200 80" xmlns="http://www.w3.org/2000/svg">
                <path d="M10,40 C40,80 80,-10 110,50 C140,110 170,20 190,40" fill="none" stroke="#1a1a1a" stroke-width="3" stroke-linecap="round"/>
            </svg>
            <div class="signature-line"></div>
            <span class="principal-label">Principal</span>
        </div>
    </div>

    <!-- ==================== BACK ==================== -->
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

        <!-- Back Footer -->
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
                <svg class="signature" viewBox="0 0 200 80" xmlns="http://www.w3.org/2000/svg">
                    <path d="M10,40 C40,80 80,-10 110,50 C140,110 170,20 190,40" fill="none" stroke="#1a1a1a" stroke-width="3" stroke-linecap="round"/>
                </svg>
                <div class="signature-line"></div>
                <span class="principal-label">Principal</span>
            </div>
        </div>
    </div>

</div>