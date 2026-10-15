<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>ID Cards — Backs</title>
<style>
    @page { margin: 5mm; }
    * { margin: 0; padding: 0; box-sizing: border-box; }

    body {
        font-family: Arial, Helvetica, sans-serif;
        color: #1a1a1a;
        font-size: 8px;
    }

    table.sheet {
        width: 100%;
        border-collapse: separate;
        border-spacing: 1.5mm;
        table-layout: fixed;
    }
    table.sheet td {
        width: 88.9mm;
        height: 50.8mm;
        padding: 0;
        vertical-align: top;
        page-break-inside: avoid;
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

    /* Body (policies) */
    tr.body td {
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

    /* Footer */
    tr.ftr td {
        height: 10.8mm;
        padding: 0;
        vertical-align: middle;
    }
    table.ftr-inner {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
        border-top: 0.3mm solid #e5e7eb;
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
        width: 70%;
        height: 0.3mm;
        background: #000;
        margin: 0 auto 0.4mm auto;
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
    $pages = $cards->chunk(10);
@endphp

@foreach($pages as $pageIndex => $pageCards)
    @if($pageIndex > 0)
        <div style="page-break-before: always;"></div>
    @endif

    <table class="sheet">
        @for($row = 0; $row < 5; $row++)
            <tr>
                @for($col = 0; $col < 2; $col++)
                    @php
                        $idx = ($row * 2) + $col;
                        $card = $pageCards->values()->get($idx);
                    @endphp
                    <td>
                        @if($card)
                            <div class="card">
                                <table class="card-inner">
                                    <tr class="body">
                                        <td colspan="2">
                                            <div class="back-title">School Policies</div>
                                            <div class="policy">Present this card upon request by any school official or security personnel.</div>
                                            <div class="policy">This ID is non-transferable and remains the property of Kenswed Sec School.</div>
                                            <div class="policy">Misuse or alteration of this card may lead to disciplinary action.</div>
                                            <div class="policy">Loss must be reported immediately to the school Administration office.</div>
                                            <div class="policy">Replacement cards are available at a fee.</div>
                                            <div class="policy">All students are expected to adhere to the code of conduct available on the school website.</div>
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
                        @endif
                    </td>
                @endfor
            </tr>
        @endfor
    </table>
@endforeach

</body>
</html>