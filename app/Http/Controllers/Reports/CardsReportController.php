<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Services\Reports\CardsReport;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;

/** Student cards report (finance): transport and canteen wallets apart, on screen and as PDF. */
class CardsReportController extends Controller
{
    public const PARTS = [
        'full' => 'Student cards report',
        'transport' => 'Transport wallet report',
        'canteen' => 'Canteen wallet report',
        'balances' => 'Card balances list',
    ];

    public function show(Request $request, CardsReport $report)
    {
        [$from, $to] = $this->period($request);
        $class = $request->query('class') ?: null;

        return view('reports.cards.show', $report->build($from, $to, $class) + [
            'startDate' => $from->toDateString(),
            'endDate' => $to->toDateString(),
            'parts' => self::PARTS,
        ]);
    }

    public function pdf(Request $request, CardsReport $report)
    {
        [$from, $to] = $this->period($request);
        $class = $request->query('class') ?: null;
        $part = array_key_exists($request->query('part'), self::PARTS) ? $request->query('part') : 'full';
        $data = $report->build($from, $to, $class);

        $period = $part === 'balances' ? 'As at ' . now()->format('j M Y')
            : ($data['single'] ? $from->format('l, j F Y') : $from->format('j M Y') . ' – ' . $to->format('j M Y'));

        return $this->render('reports.cards.pdf', $data + ['part' => $part, 'title' => self::PARTS[$part], 'period' => $period . ($class ? ' · ' . $class : '')],
            $request, str_replace(' ', '-', strtolower(self::PARTS[$part])) . '-' . $from->toDateString() . ($data['single'] ? '' : '-to-' . $to->toDateString()));
    }

    public function statement(Request $request, int $card, CardsReport $report)
    {
        [$from, $to] = $this->period($request, today()->subDays(29));
        $data = $report->statement($card, $from, $to);
        $c = $data['card'];

        return $this->render('reports.cards.statement', $data + [
            'title' => 'Card statement',
            'period' => ($c->name ?: 'Student') . ' · ' . $from->format('j M Y') . ' – ' . $to->format('j M Y'),
        ], $request, 'card-statement-' . preg_replace('/[^A-Za-z0-9]+/', '-', $c->admission ?: $c->card_number ?: $c->id));
    }

    private function render(string $view, array $data, Request $request, string $file)
    {
        @ini_set('memory_limit', '512M'); // long tables in dompdf need more than the default
        $pdf = Pdf::loadView($view, $data + ['generatedBy' => $request->user()?->name, 'generatedAt' => now()])->setPaper('a4', 'portrait');
        $pdf->render();
        $canvas = $pdf->getDomPDF()->getCanvas();
        $canvas->page_text($canvas->get_width() - 92, $canvas->get_height() - 28, 'Page {PAGE_NUM} of {PAGE_COUNT}', null, 7.5, [0.42, 0.45, 0.5]);

        return $request->boolean('inline') ? $pdf->stream($file . '.pdf') : $pdf->download($file . '.pdf');
    }

    /** From/To from the query (default this month), both days included, at most a year. */
    private function period(Request $request, ?Carbon $defaultFrom = null): array
    {
        $parse = fn ($v, $default) => rescue(fn () => $v ? Carbon::parse($v) : $default, $default, false);
        $from = $parse($request->query('start_date'), $defaultFrom ?? today()->startOfMonth())->startOfDay();
        $to = $parse($request->query('end_date'), today())->startOfDay();
        if ($to->lt($from)) {
            [$from, $to] = [$to, $from];
        }
        if ($from->diffInDays($to) > 366) {
            $from = $to->copy()->subDays(366);
        }
        return [$from, $to];
    }
}
