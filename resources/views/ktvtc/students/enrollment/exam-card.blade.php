<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Exam Card — {{ $student->student_number }}</title>
    <style>
        * { box-sizing: border-box; }
        @page { margin: 20mm 15mm; }

        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            color: #2b2b2b;
            margin: 0;
            padding: 0;
        }

        /* ============ CARD SHELL ============ */
        .card {
            width: 100%;
            border: 1px solid #cccccc;
            border-radius: 8px;
            overflow: hidden;
            page-break-inside: avoid;
        }

        .card + .card { margin-top: 20mm; }

        /* ============ FRONT HEADER ============ */
        .header {
            background: #7a1e2b;
            color: #ffffff;
            padding: 14px 20px;
            position: relative;
        }

        .header .title {
            font-size: 22px;
            font-weight: bold;
            letter-spacing: 1px;
        }

        .header .college {
            position: absolute;
            top: 12px;
            right: 20px;
            text-align: right;
            font-size: 9px;
            line-height: 1.35;
        }

        .header .college strong { font-size: 11px; }

        .header .logo-mini {
            position: absolute;
            top: 8px;
            right: 160px;
            width: 42px;
            height: 42px;
        }

        /* ============ FRONT BODY ============ */
        .body {
            display: table;
            width: 100%;
            min-height: 250px;
            background: #ffffff;
        }

        .body-left {
            display: table-cell;
            width: 65%;
            padding: 26px 30px;
            vertical-align: top;
        }

        .body-right {
            display: table-cell;
            width: 35%;
            background: #7a1e2b;
            color: #ffffff;
            padding: 26px 20px;
            vertical-align: top;
            text-align: center;
            position: relative;
        }

        .body-right .college-name {
            font-size: 12px;
            font-weight: bold;
            line-height: 1.5;
            margin-top: 20px;
        }

        .body-right .address {
            font-size: 9px;
            margin-top: 14px;
            line-height: 1.5;
            opacity: 0.9;
        }

        .body-right .stamp {
            position: absolute;
            bottom: 20px;
            left: 0; right: 0;
            font-size: 8px;
            opacity: 0.75;
        }

        .field {
            margin-bottom: 22px;
        }

        .field .label {
            font-size: 11px;
            font-weight: bold;
            color: #7a1e2b;
            margin-bottom: 4px;
        }

        .field .value {
            font-size: 14px;
            padding-bottom: 4px;
            border-bottom: 1px solid #7a1e2b;
            min-height: 22px;
        }

        .field-row {
            display: table;
            width: 100%;
        }

        .field-row .field { display: table-cell; width: 50%; }
        .field-row .field + .field { padding-left: 20px; }

        /* ============ BACK ============ */
        .back {
            background: #ffffff;
            padding: 40px 30px;
            text-align: center;
        }

        .back .big-logo {
            width: 120px;
            height: 120px;
            margin: 0 auto 18px;
        }

        .back .big-name {
            font-size: 22px;
            font-weight: bold;
            color: #7a1e2b;
            letter-spacing: 1px;
            margin-bottom: 6px;
        }

        .back .big-name-sub {
            font-size: 14px;
            color: #555555;
            margin-bottom: 26px;
        }

        .back .motto {
            font-family: 'DejaVu Serif', Georgia, serif;
            font-style: italic;
            font-size: 16px;
            color: #7a1e2b;
            margin-bottom: 40px;
        }

        .back .stamp-box {
            display: inline-block;
            border: 2px solid #7a1e2b;
            padding: 10px 20px;
            font-size: 11px;
            color: #7a1e2b;
            border-radius: 4px;
        }

        .back .issue-date {
            margin-top: 26px;
            font-size: 10px;
            color: #666666;
        }

        /* ============ FOOTER NOTE ============ */
        .note {
            font-size: 9px;
            color: #777777;
            text-align: center;
            margin-top: 8mm;
            line-height: 1.5;
        }

        .divider {
            border-top: 1px dashed #cccccc;
            margin: 12px 0;
        }
    </style>
</head>
<body>

    {{-- ═══════════════════════════════════════════ --}}
    {{-- FRONT SIDE --}}
    {{-- ═══════════════════════════════════════════ --}}
    <div class="card">
        <div class="header">
            <img src="{{ $logo_path }}" class="logo-mini" alt="Logo">
            <div class="title">EXAM CARD</div>
            <div class="college">
                <strong>KENSWED</strong><br>
                Technical &amp; Vocational Training College<br>
                <em>Educate and Elevate</em>
            </div>
        </div>

        <div class="body">
            <div class="body-left">
                <div class="field">
                    <div class="label">STUDENT NAME</div>
                    <div class="value">{{ $student->full_name }}</div>
                </div>

                <div class="field">
                    <div class="label">REG. NUMBER</div>
                    <div class="value">{{ $student->student_number }}</div>
                </div>

                <div class="field">
                    <div class="label">COURSE</div>
                    <div class="value">
                        {{ $enrollment->course_name }}
                        @if($enrollment->course_code)
                            ({{ $enrollment->course_code }})
                        @endif
                    </div>
                </div>

                <div class="field-row">
                    <div class="field">
                        <div class="label">EXAM PERIOD</div>
                        <div class="value">
                            {{ $term?->name ?? $enrollment->intake_month . ' ' . $enrollment->intake_year }}
                        </div>
                    </div>
                    <div class="field">
                        <div class="label">YEAR</div>
                        <div class="value">
                            {{ $term?->academic_year ?? $enrollment->intake_year }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="body-right">
                <div class="college-name">
                    KENSWED<br>
                    TECHNICAL &amp; VOCATIONAL<br>
                    TRAINING COLLEGE
                </div>
                <div class="address">
                    P.O. Box 59440 - 00200<br>
                    Nairobi, Kenya
                </div>
                <div class="stamp">
                    Issued: {{ $issued_at->format('d M Y') }}
                </div>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════ --}}
    {{-- BACK SIDE --}}
    {{-- ═══════════════════════════════════════════ --}}
    <div class="card">
        <div class="back">
            <img src="{{ $logo_path }}" class="big-logo" alt="Logo">
            <div class="big-name">KENSWED</div>
            <div class="big-name-sub">Technical &amp; Vocational Training College</div>
            <div class="motto">"{{ $college['motto'] }}"</div>

            <div class="stamp-box">
                <strong>KTVTC</strong> · OFFICIAL STAMP<br>
                <span style="font-size: 9px;">Issued {{ $issued_at->format('d M Y') }}</span>
            </div>

            <div class="issue-date">
                {{ $college['address'] }}
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════ --}}
    {{-- FOOTER NOTE --}}
    {{-- ═══════════════════════════════════════════ --}}
    <div class="note">
        This exam card must be presented for every exam session. It is non-transferable.
        Fees for the term must be fully paid to keep this card valid.
        <div class="divider"></div>
        Generated on {{ $issued_at->format('d M Y H:i') }} —
        {{ $college['name'] }}
    </div>

</body>
</html>