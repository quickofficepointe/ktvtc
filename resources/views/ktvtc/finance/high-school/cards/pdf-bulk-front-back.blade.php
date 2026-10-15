<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>ID Cards — Front + Back</title>
<style>
    @page { margin: 5mm; }
    * { margin: 0; padding: 0; box-sizing: border-box; }

    body {
        font-family: Arial, Helvetica, sans-serif;
        color: #1a1a1a;
        font-size: 8px;
    }

    /* One student row = front card + back card side-by-side
       88.9 + 1.5 + 88.9 = 179.3mm  ✓ fits in 200mm width
       5 rows × (50.8 + 2mm gap) = 264mm  ✓ fits in 287mm height
    */
    table.sheet {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        table-layout: fixed;
    }
    table.sheet tr {
        page-break-inside: avoid;
    }
    table.sheet td.pair {
        padding: 1mm 0;
        vertical-align: top;
    }

    table.pair-inner {
        width: 100%;
        border-collapse: separate;
        border-spacing: 3mm 0;
        table-layout: fixed;
    }
    table.pair-inner > tbody > tr > td {
        width: 88.9mm;
        vertical-align: top;
        padding: 0;
    }

    .card {
        width: 88.9mm;
        height: 50.8mm;
        border: 0.3mm solid #c7ccd1;
        border-radius: 1.5mm;
        overflow: hidden;
        background: #fff;
    }

    table.card-inner {
        width: 100%;
        height: 50.8mm;
        border-collapse: collapse;
        table-layout: fixed;
    }

    /* ------ FRONT ------ */
    tr.hdr td {
        height: 13mm;
        vertical-align: middle;
        padding: 0;
    }
    td.hdr-left {
        width: 55%;
        background: #ffffff;
        padding: 1mm 1.5mm;
    }
    td.hdr-right {
        width: 45%;
        background-image: url('{{ $header_bg }}');
        background-size: cover;
        background-position: center;
        color: #fff;
        text-align: center;
        font-size: 7px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }
    table.hdr-inner { width: 100%; border-collapse: collapse; }
    table.hdr-inner td { vertical-align: middle; padding: 0; }
    .logo-cell { width: 11mm; }
    .logo {
        width: 10mm; height: 10mm;
        border-radius: 50%;
        border: 0.3mm solid #e5e7eb;
        display: block;
    }
    .school-name {
        font-size: 7.5px;
        font-weight: 900;
        text-transform: uppercase;
        line-height: 1;
        color: #1a1a1a;
    }
    .slogan {
        background: #d81524;
        color: #fff;
        font-size: 4.5px;
        font-weight: 700;
        text-transform: uppercase;
        padding: 0.5mm 1.2mm;
        border-radius: 0.4mm;
        display: inline-block;
        margin-top: 0.6mm;
    }

    tr.body td {
        height: 27mm;
        vertical-align: top;
        padding: 0;
    }
    table.body-inner { width: 100%; border-collapse: collapse; }
    table.body-inner > tbody > tr > td {
        vertical-align: top;
        padding: 1.2mm 1.5mm;
    }
    .photo-cell { width: 18mm; text-align: center; padding: 1.5mm !important; }
    .photo {
        width: 16mm; height: 16mm;
        border-radius: 3mm;
        border: 1mm solid #d81524;
        display: block;
    }
    .info-cell { padding-left: 1mm !important; }
    .name {
        font-size: 8px;
        font-weight: 700;
        text-transform: uppercase;
        margin-bottom: 0.6mm;
        color: #1a1a1a;
    }
    table.info { width: 100%; border-collapse: collapse; }
    table.info td {
        font-size: 6px;
        line-height: 1.35;
        padding: 0.1mm 0;
        vertical-align: top;
    }
    .label { color: #6b7280; width: 12mm; }
    .value { color: #d81524; font-weight: 600; }
    .value.mono { font-family: monospace; font-size: 5.5px; }
    .qr-cell { width: 20mm; text-align: center; padding: 1.5mm !important; }
    .qr-cell img { width: 16mm; height: 16mm; display: block; margin: 0 auto; }

    tr.ftr td {
        height: 10.8mm;
        vertical-align: middle;
        padding: 0;
    }
    table.ftr-inner {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
    }
    .ftr-left-cell {
        background-image: url('{{ $footer_bg }}');
        background-size: cover;
        background-position: center;
        color: #fff;
        font-size: 5.5px;
        padding: 1mm 2mm;
        border-top-right-radius: 6mm;
        background-color: #b91c1c;
    }
    .ftr-right-cell {
        width: 22%;
        background: #fff;
        text-align: center;
        padding: 1mm 1mm 0.8mm 1mm;
        vertical-align: bottom;
    }
    .sig-line {
        width: 70%; height: 0.3mm;
        background: #000;
        margin: 0 auto 0.4mm auto;
    }
    .sig-label {
        font-size: 5px;
        font-weight: 700;
        text-transform: uppercase;
        color: #1a1a1a;
    }

    /* ------ BACK ------ */
    tr.back-body td {
        height: 40mm;
        vertical-align: top;
        padding: 3mm 4mm 1mm 4mm;
    }
    .back-title {
        color: #d81524;
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        margin-bottom: 1.5mm;
    }
    .policy {
        font-size: 6.5px;
        color: #374151;
        line-height: 1.5;
        margin-bottom: 0.9mm;
        padding-left: 2.8mm;
        position: relative;
    }
    .policy::before {
        content: '';
        position: absolute;
        left: 0;
        top: 1.6mm;
        width: 1.5mm;
        height: 1.5mm;
        background: #d81524;
        border-radius: 0.3mm;
    }
</style>
</head>
<body>

@php
    $pages = $cards->chunk(5);
@endphp

@foreach($pages as $pageIndex => $pageCards)
    @if($pageIndex > 0)
        <div style="page-break-before: always;"></div>
    @endif

    <table class="sheet">
        @foreach($pageCards as $card)
            @php
                $photo = $card->photo_base64 ?? null;
                $qr = $card->qr_base64 ?? null;
            @endphp
            <tr>
                <td class="pair">
                    <table class="pair-inner">
                        <tr>
                            {{-- ================= FRONT ================= --}}
                            <td>
                                <div class="card">
                                    <table class="card-inner">
                                        <tr class="hdr">
                                            <td class="hdr-left">
                                                <table class="hdr-inner">
                                                    <tr>
                                                        <td class="logo-cell">
                                                            @if($logo)
                                                                <img src="{{ $logo }}" class="logo">
                                                            @endif
                                                        </td>
                                                        <td>
                                                            <div class="school-name">Kenswed<br>Secondary School</div>
                                                            <div class="slogan">it seems impossible until it's done</div>
                                                        </td>
                                                    </tr>
                                                </table>
                                            </td>
                                            <td class="hdr-right">Students' Identity Card</td>
                                        </tr>
                                        <tr class="body">
                                            <td colspan="2">
                                                <table class="body-inner">
                                                    <tr>
                                                        <td class="photo-cell">
                                                            @if($photo)<img src="{{ $photo }}" class="photo">@endif
                                                        </td>
                                                        <td class="info-cell">
                                                            <div class="name">{{ $card->student_name }}</div>
                                                            <table class="info">
                                                                <tr><td class="label">Adm No</td><td class="value">: {{ $card->student_admission_number }}</td></tr>
                                                                <tr><td class="label">Class</td><td class="value">: {{ $card->student_class }}</td></tr>
                                                                <tr><td class="label">Ass. No</td><td class="value">: {{ $card->student->stream ?? 'N/A' }}</td></tr>
                                                                <tr><td class="label">D.O.B</td><td class="value">: {{ $card->student && $card->student->date_of_birth ? $card->student->date_of_birth->format('d/m/Y') : 'N/A' }}</td></tr>
                                                                <tr><td class="label">Gender</td><td class="value">: {{ $card->student ? ucfirst($card->student->gender) : 'N/A' }}</td></tr>
                                                                <tr><td class="label">Card No</td><td class="value mono">: {{ $card->card_number }}</td></tr>
                                                            </table>
                                                        </td>
                                                        <td class="qr-cell">
                                                            @if($qr)<img src="{{ $qr }}">@endif
                                                        </td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>
                                        <tr class="ftr">
                                            <td colspan="2">
                                                <table class="ftr-inner">
                                                    <tr>
                                                        <td class="ftr-left-cell">
                                                            <div>P: +254 743 237 432 &nbsp;&nbsp; W: www.kenswed.org</div>
                                                            <div>L: Kibiko, Off Maragara road, Kajiado West</div>
                                                        </td>
                                                        <td class="ftr-right-cell">
                                                            <div class="sig-line"></div>
                                                            <div class="sig-label">Principal</div>
                                                        </td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </td>

                            {{-- ================= BACK ================= --}}
                            <td>
                                <div class="card">
                                    <table class="card-inner">
                                        <tr class="back-body">
                                            <td colspan="2">
                                                <div class="back-title">School Policies</div>
                                                <div class="policy">Present this card upon request by any school official or security personnel.</div>
                                                <div class="policy">This ID is non-transferable and remains the property of Kenswed Sec School.</div>
                                                <div class="policy">Misuse or alteration of this card may lead to disciplinary action.</div>
                                                <div class="policy">Loss must be reported immediately to the school Administration office.</div>
                                                <div class="policy">Replacement cards are available at a fee.</div>
                                                <div class="policy">All students are expected to adhere to the code of conduct.</div>
                                            </td>
                                        </tr>
                                        <tr class="ftr">
                                            <td colspan="2">
                                                <table class="ftr-inner">
                                                    <tr>
                                                        <td class="ftr-left-cell">
                                                            <div>P: +254 743 237 432 &nbsp;&nbsp; W: www.kenswed.org</div>
                                                            <div>L: Kibiko, Off Maragara road, Kajiado West</div>
                                                        </td>
                                                        <td class="ftr-right-cell">
                                                            <div class="sig-line"></div>
                                                            <div class="sig-label">Principal</div>
                                                        </td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        @endforeach
    </table>
@endforeach

</body>
</html>