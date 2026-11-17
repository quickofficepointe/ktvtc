{{-- Shared letterhead for PDF reports (dompdf): tables for layout, no flex/grid, fonts from DejaVu. --}}
@php
    $logoPath = public_path('Assets/images/Kenswed_logo.png');
    $logo = is_file($logoPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath)) : null;
@endphp
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>{{ $title }} · {{ $period }}</title>
<style>
    @page { margin: 118px 34px 56px 34px; }
    * { font-family: "DejaVu Sans", Arial, sans-serif; }
    body { font-size: 9.5px; color: #1f2937; }
    header { position: fixed; top: -100px; left: 0; right: 0; height: 86px; }
    footer { position: fixed; bottom: -40px; left: 0; right: 0; height: 24px; font-size: 7.5px; color: #6b7280; border-top: 1px solid #e5e7eb; padding-top: 6px; }
    .band { height: 4px; background: #b91c1c; margin-top: 6px; }
    .brand td { vertical-align: middle; }
    .college { font-size: 13px; font-weight: bold; color: #111827; }
    .college-sub { font-size: 8px; color: #6b7280; margin-top: 2px; }
    .doc-title { font-size: 15px; font-weight: bold; color: #b91c1c; text-align: right; }
    .doc-period { font-size: 9.5px; color: #374151; text-align: right; margin-top: 3px; }

    h2 { font-size: 12px; color: #111827; margin: 16px 0 6px; padding-bottom: 4px; border-bottom: 2px solid #b91c1c; }
    h3 { font-size: 10.5px; color: #374151; margin: 10px 0 4px; }
    p.note { font-size: 8px; color: #6b7280; margin: 4px 0 0; }
    .page-break { page-break-before: always; }
    .keep { page-break-inside: avoid; }

    table.grid { width: 100%; border-collapse: collapse; }
    table.grid th { background: #f3f4f6; color: #374151; font-size: 8.5px; text-transform: uppercase; letter-spacing: .3px; text-align: left; padding: 5px 6px; border-bottom: 1px solid #d1d5db; }
    table.grid td { padding: 4px 6px; border-bottom: 1px solid #eef0f3; vertical-align: top; }
    table.grid tr:nth-child(even) td { background: #fafafa; }
    table.grid tfoot td { font-weight: bold; border-top: 1.5px solid #9ca3af; background: #fff; }
    .r { text-align: right; } .c { text-align: center; }
    .muted { color: #6b7280; } .bold { font-weight: bold; }
    .up { color: #15803d; } .down { color: #b91c1c; }
    .tag { display: inline-block; padding: 1px 5px; border-radius: 3px; font-size: 7.5px; font-weight: bold; }
    .tag-red { background: #fee2e2; color: #991b1b; } .tag-amber { background: #fef3c7; color: #92400e; } .tag-green { background: #dcfce7; color: #166534; }

    table.kpis { width: 100%; border-collapse: separate; border-spacing: 6px 0; margin: 0 -6px; }
    table.kpis td { width: 25%; background: #f9fafb; border: 1px solid #e5e7eb; border-left: 3px solid #b91c1c; padding: 7px 9px; vertical-align: top; }
    .kpi-label { font-size: 7.5px; color: #6b7280; text-transform: uppercase; letter-spacing: .3px; }
    .kpi-value { font-size: 14px; font-weight: bold; color: #111827; margin-top: 3px; }
    .kpi-hint { font-size: 7.5px; color: #6b7280; margin-top: 2px; }
    .chart { width: 100%; margin-top: 4px; }
    .swatch { display: inline-block; width: 8px; height: 8px; border-radius: 2px; margin-right: 4px; }
    @yield('styles')
</style>
</head>
<body>
<header>
    <table class="brand" width="100%">
        <tr>
            <td width="62">@if($logo)<img src="{{ $logo }}" style="height: 54px;">@endif</td>
            <td>
                <div class="college">Kenswed Technical and Vocational Training College</div>
                <div class="college-sub">Ngong, Kenya · info@ktvtc.ac.ke · ktvtc.ac.ke</div>
            </td>
            <td width="250">
                <div class="doc-title">{{ $title }}</div>
                <div class="doc-period">{{ $period }}</div>
            </td>
        </tr>
    </table>
    <div class="band"></div>
</header>
<footer>
    <table width="100%"><tr>
        <td>Generated {{ $generatedAt->format('j M Y, H:i') }}@if($generatedBy) by {{ $generatedBy }}@endif · KTVTC management system</td>
    </tr></table>
</footer>

<main>
    @yield('body')
</main>
</body>
</html>
