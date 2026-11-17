<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Services\Reports\CafeteriaReport;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;

/**
 * Cafeteria report: on screen and as a PDF. Opened by finance (finance.reports.cafeteria*)
 * and by the cafeteria manager (cafeteria.reports.cafeteria*), each inside their own layout.
 */
class CafeteriaReportController extends Controller
{
    /** PDF sections people can download on their own. */
    public const PARTS = [
        'full' => 'Cafeteria report',
        'sales' => 'Cafeteria sales report',
        'stock' => 'Cafeteria stock report',
        'reorder' => 'Cafeteria purchase list',
    ];

    public function show(Request $request, CafeteriaReport $report)
    {
        [$from, $to] = $this->period($request);

        return view('reports.cafeteria.show', $report->build($from, $to) + [
            'startDate' => $from->toDateString(),
            'endDate' => $to->toDateString(),
            'layout' => $this->isFinance($request) ? 'ktvtc.finance.layouts.app' : 'ktvtc.cafeteria.layout.cafeterialayout',
            'routePrefix' => $this->routePrefix($request),
            'parts' => self::PARTS,
        ]);
    }

    public function pdf(Request $request, CafeteriaReport $report)
    {
        [$from, $to] = $this->period($request);
        $part = array_key_exists($request->query('part'), self::PARTS) ? $request->query('part') : 'full';
        $data = $report->build($from, $to);

        $title = self::PARTS[$part];
        $period = $data['single'] ? $from->format('l, j F Y') : $from->format('j M Y') . ' – ' . $to->format('j M Y');

        $pdf = Pdf::loadView('reports.cafeteria.pdf', $data + [
            'part' => $part,
            'title' => $title,
            'period' => $period,
            'generatedBy' => $request->user()?->name,
            'generatedAt' => now(),
        ])->setPaper('a4', 'portrait');

        // Page numbers in the footer
        $pdf->render();
        $canvas = $pdf->getDomPDF()->getCanvas();
        $canvas->page_text($canvas->get_width() - 92, $canvas->get_height() - 28, 'Page {PAGE_NUM} of {PAGE_COUNT}', null, 7.5, [0.42, 0.45, 0.5]);

        $file = str_replace(' ', '-', strtolower($title)) . '-' . $from->toDateString() . ($data['single'] ? '' : '-to-' . $to->toDateString()) . '.pdf';

        return $request->boolean('inline') ? $pdf->stream($file) : $pdf->download($file);
    }

    /** From/To from the query (default today), both days included, at most a year. */
    private function period(Request $request): array
    {
        $parse = fn ($v, $default) => rescue(fn () => $v ? Carbon::parse($v) : $default, $default, false);
        $from = $parse($request->query('start_date'), today())->startOfDay();
        $to = $parse($request->query('end_date'), $from->copy())->startOfDay();
        if ($to->lt($from)) {
            [$from, $to] = [$to, $from];
        }
        if ($from->diffInDays($to) > 366) {
            $from = $to->copy()->subDays(366);
        }
        return [$from, $to];
    }

    private function isFinance(Request $request): bool
    {
        return str_starts_with((string) $request->route()?->getName(), 'finance.');
    }

    private function routePrefix(Request $request): string
    {
        return $this->isFinance($request) ? 'finance.reports.cafeteria' : 'cafeteria.reports.cafeteria';
    }
}
