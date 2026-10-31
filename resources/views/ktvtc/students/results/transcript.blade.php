<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Transcript</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #222; }
        .head { text-align: center; border-bottom: 2px solid #B91C1C; padding-bottom: 8px; margin-bottom: 12px; }
        .head img { height: 60px; }
        h1 { font-size: 16px; margin: 4px 0; color: #B91C1C; }
        h2 { font-size: 13px; margin: 14px 0 4px; }
        h3 { font-size: 12px; margin: 10px 0 4px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ccc; padding: 4px 6px; text-align: left; }
        th { background: #f3f3f3; }
        .r { text-align: right; }
        .fail { color: #B91C1C; font-weight: bold; }
        .meta td { border: none; padding: 2px 0; }
        .foot { margin-top: 24px; font-size: 9px; color: #666; }
    </style>
</head>
<body>
    <div class="head">
        @if(file_exists($logo_path))<img src="{{ $logo_path }}">@endif
        <h1>Kenswed Technical &amp; Vocational Training College</h1>
        <div>Academic Transcript</div>
    </div>

    <table class="meta">
        <tr><td><strong>Name:</strong> {{ $student->full_name }}</td><td><strong>Student No.:</strong> {{ $student->student_number }}</td></tr>
        <tr><td><strong>Issued:</strong> {{ $issued_at->format('d M Y') }}</td><td></td></tr>
    </table>

    @foreach($courses as $c)
        <h2>{{ $c['enrollment']->course_name }}{{ $c['track'] ? ' — ' . $c['track'] : '' }}</h2>
        @foreach($c['terms'] as $term => $results)
            <h3>Term {{ $term }}</h3>
            <table>
                <tr>
                    <th>Unit</th>
                    <th class="r">{{ $c['scale']->component_a ?? 'Score 1' }}</th>
                    <th class="r">{{ $c['scale']->component_b ?? 'Score 2' }}</th>
                    <th class="r">Total</th>
                    <th>Grade</th>
                </tr>
                @foreach($results as $r)
                    <tr>
                        <td>{{ $r->unit->label }}{{ $r->attempts_count > 1 ? ' (re-sit)' : '' }}</td>
                        <td class="r">{{ $r->score_a }}</td>
                        <td class="r">{{ $r->score_b }}</td>
                        <td class="r">{{ $r->total }}</td>
                        <td class="{{ $r->passed ? '' : 'fail' }}">{{ $r->grade }}</td>
                    </tr>
                @endforeach
            </table>
        @endforeach
    @endforeach

    <div class="foot">Only results published by the academic office are shown. Where a unit was re-sat, the latest attempt is shown.</div>
</body>
</html>
