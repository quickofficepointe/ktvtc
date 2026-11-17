<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Services\Reports\FeesReport;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/** School fees report (finance): on screen, as PDF, and one student's fee statement. */
class FeesReportController extends Controller
{
    public const PARTS = [
        'full' => 'School fees report',
        'collections' => 'Fee collections report',
        'balances' => 'Fee balances report',
        'defaulters' => 'Fee defaulters list',
    ];

    public function show(Request $request, FeesReport $report)
    {
        [$from, $to] = $this->period($request);
        [$campusId, $courseId] = $this->filters($request);

        return view('reports.fees.show', $report->build($from, $to, $campusId, $courseId) + [
            'startDate' => $from->toDateString(),
            'endDate' => $to->toDateString(),
            'campuses' => DB::table('campuses')->whereNull('deleted_at')->orderBy('name')->pluck('name', 'id'),
            'courses' => DB::table('courses')->where('is_active', 1)->orderBy('name')->pluck('name', 'id'),
            'search' => $this->studentSearch($request->query('student')),
            'studentQuery' => $request->query('student'),
            'parts' => self::PARTS,
        ]);
    }

    public function pdf(Request $request, FeesReport $report)
    {
        [$from, $to] = $this->period($request);
        [$campusId, $courseId] = $this->filters($request);
        $part = array_key_exists($request->query('part'), self::PARTS) ? $request->query('part') : 'full';
        $data = $report->build($from, $to, $campusId, $courseId);

        $scope = collect([$data['campusName'], $data['courseName']])->filter()->implode(' · ');
        $period = in_array($part, ['balances', 'defaulters'], true)
            ? 'As at ' . now()->format('j M Y')
            : ($data['single'] ? $from->format('l, j F Y') : $from->format('j M Y') . ' – ' . $to->format('j M Y'));

        return $this->render('reports.fees.pdf', $data + [
            'part' => $part,
            'title' => self::PARTS[$part],
            'period' => $period . ($scope ? ' · ' . $scope : ''),
        ], $request, str_replace(' ', '-', strtolower(self::PARTS[$part])) . '-' . $from->toDateString() . ($data['single'] ? '' : '-to-' . $to->toDateString()));
    }

    public function statement(Request $request, int $student, FeesReport $report)
    {
        $data = $report->statement($student);
        $s = $data['student'];

        return $this->render('reports.fees.statement', $data + [
            'title' => 'Fee statement',
            'period' => trim("{$s->first_name} {$s->last_name}") . ' · ' . ($s->student_number ?: 'No student number') . ' · ' . now()->format('j M Y'),
        ], $request, 'fee-statement-' . ($s->student_number ? str_replace('/', '-', $s->student_number) : $s->id));
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

    /** Students matching a number, name or phone, for opening a statement. */
    private function studentSearch(?string $term)
    {
        $term = trim((string) $term);
        if (mb_strlen($term) < 2) {
            return collect();
        }
        return DB::table('students')->whereNull('deleted_at')->whereNull('merged_into_id')
            ->where(fn ($q) => $q->where('student_number', 'like', "%{$term}%")->orWhere('phone', 'like', "%{$term}%")
                ->orWhereRaw("CONCAT_WS(' ', first_name, middle_name, last_name) LIKE ?", ["%{$term}%"]))
            ->orderBy('first_name')->limit(15)->get(['id', 'student_number', 'first_name', 'last_name', 'phone']);
    }

    private function filters(Request $request): array
    {
        return [
            $request->integer('campus_id') ?: null,
            $request->integer('course_id') ?: null,
        ];
    }

    /** From/To from the query (default this month), both days included, at most a year. */
    private function period(Request $request): array
    {
        $parse = fn ($v, $default) => rescue(fn () => $v ? Carbon::parse($v) : $default, $default, false);
        $from = $parse($request->query('start_date'), today()->startOfMonth())->startOfDay();
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
