<?php

namespace App\Support;

/**
 * Small server-side charts for PDF reports (dompdf cannot run JavaScript charts).
 * Each method returns an <img> data URI so it drops straight into a PDF view.
 */
class SvgChart
{
    private const FONT = 'font-family="DejaVu Sans, Arial, sans-serif"';
    private const GRID = '#e5e7eb';
    private const TEXT = '#4b5563';

    /**
     * Vertical bars. $series: [['label' => .., 'color' => '#..', 'values' => [..]], ..]; stacked or side by side.
     */
    public static function bars(array $labels, array $series, bool $stacked = true, int $w = 720, int $h = 230): string
    {
        $n = count($labels);
        if ($n === 0) {
            return self::empty($w, $h);
        }
        [$left, $top, $right, $bottom] = [52, count($series) > 1 ? 26 : 10, 8, 34];
        $pw = $w - $left - $right;
        $ph = $h - $top - $bottom;

        $totals = [];
        for ($i = 0; $i < $n; $i++) {
            $vals = array_map(fn ($s) => (float) ($s['values'][$i] ?? 0), $series);
            $totals[] = $stacked ? array_sum($vals) : (count($vals) ? max($vals) : 0);
        }
        $max = self::niceMax(max($totals ?: [0]));

        $svg = self::open($w, $h) . self::yAxis($left, $top, $pw, $ph, $max) . self::legend($series, $left, $w);
        $slot = $pw / $n;
        $groups = $stacked ? 1 : max(count($series), 1);
        $bw = max(min($slot * 0.7 / $groups, 38), 1.5);

        for ($i = 0; $i < $n; $i++) {
            $x0 = $left + $slot * $i + ($slot - $bw * $groups) / 2;
            $base = $top + $ph;
            foreach ($series as $si => $s) {
                $v = (float) ($s['values'][$i] ?? 0);
                if ($v <= 0) {
                    continue;
                }
                $bh = $ph * $v / $max;
                $x = $stacked ? $x0 : $x0 + $bw * $si;
                $y = $stacked ? $base - $bh : $top + $ph - $bh;
                $svg .= sprintf('<rect x="%.1f" y="%.1f" width="%.1f" height="%.1f" fill="%s"/>', $x, $y, $bw, $bh, $s['color']);
                if ($stacked) {
                    $base -= $bh;
                }
            }
        }
        return self::close($svg . self::xLabels($labels, $left, $top + $ph, $slot));
    }

    /** Lines over time. $series as in bars(). */
    public static function line(array $labels, array $series, int $w = 720, int $h = 220): string
    {
        $n = count($labels);
        if ($n === 0) {
            return self::empty($w, $h);
        }
        [$left, $top, $right, $bottom] = [52, count($series) > 1 ? 26 : 10, 12, 34];
        $pw = $w - $left - $right;
        $ph = $h - $top - $bottom;
        $all = array_merge(...array_map(fn ($s) => array_map('floatval', $s['values']), $series));
        $max = self::niceMax(max($all ?: [0]));
        $step = $n > 1 ? $pw / ($n - 1) : 0;

        $svg = self::open($w, $h) . self::yAxis($left, $top, $pw, $ph, $max) . self::legend($series, $left, $w);
        foreach ($series as $s) {
            $pts = [];
            foreach (array_values($s['values']) as $i => $v) {
                $pts[] = sprintf('%.1f,%.1f', $left + ($n > 1 ? $step * $i : $pw / 2), $top + $ph - $ph * (float) $v / $max);
            }
            $svg .= sprintf('<polyline points="%s" fill="none" stroke="%s" stroke-width="2.2"/>', implode(' ', $pts), $s['color']);
            if ($n <= 40) {
                foreach ($pts as $p) {
                    [$x, $y] = explode(',', $p);
                    $svg .= sprintf('<circle cx="%s" cy="%s" r="2.6" fill="%s"/>', $x, $y, $s['color']);
                }
            }
        }
        $slot = $n > 1 ? $step : $pw;
        return self::close($svg . self::xLabels($labels, $left - ($n > 1 ? $slot / 2 : 0), $top + $ph, $slot));
    }

    /** Horizontal bars with the value at the end, e.g. top products. */
    public static function hbars(array $labels, array $values, string $color = '#b91c1c', int $w = 720, ?int $h = null, string $prefix = ''): string
    {
        $n = count($labels);
        if ($n === 0) {
            return self::empty($w, 80);
        }
        $row = 22;
        $h ??= $n * $row + 8;
        $left = 170;
        $pw = $w - $left - 80;
        $max = max(array_map('floatval', $values) ?: [0]) ?: 1;
        $svg = self::open($w, $h);
        foreach (array_values($labels) as $i => $label) {
            $v = (float) array_values($values)[$i];
            $y = 4 + $i * $row;
            $bw = max($pw * $v / $max, 1);
            $svg .= sprintf('<text x="%d" y="%.1f" font-size="10.5" text-anchor="end" fill="%s" %s>%s</text>', $left - 8, $y + 14, self::TEXT, self::FONT, self::esc(self::cut($label, 28)));
            $svg .= sprintf('<rect x="%d" y="%d" width="%.1f" height="15" rx="2" fill="%s"/>', $left, $y + 3, $bw, $color);
            $svg .= sprintf('<text x="%.1f" y="%.1f" font-size="10" fill="#111827" %s>%s</text>', $left + $bw + 5, $y + 14, self::FONT, self::esc($prefix . self::num($v)));
        }
        return self::close($svg);
    }

    // ------------------------------------------------------------------ parts

    private static function open(int $w, int $h): string
    {
        return sprintf('<svg xmlns="http://www.w3.org/2000/svg" width="%d" height="%d" viewBox="0 0 %d %d">', $w, $h, $w, $h);
    }

    private static function close(string $svg): string
    {
        return 'data:image/svg+xml;base64,' . base64_encode($svg . '</svg>');
    }

    private static function empty(int $w, int $h): string
    {
        return self::close(self::open($w, $h) . sprintf('<text x="%d" y="%d" font-size="12" text-anchor="middle" fill="#9ca3af" %s>No data for this period</text>', $w / 2, $h / 2, self::FONT));
    }

    private static function yAxis(int $left, int $top, float $pw, float $ph, float $max): string
    {
        $svg = '';
        for ($i = 0; $i <= 4; $i++) {
            $y = $top + $ph - $ph * $i / 4;
            $svg .= sprintf('<line x1="%d" y1="%.1f" x2="%.1f" y2="%.1f" stroke="%s" stroke-width="1"/>', $left, $y, $left + $pw, $y, self::GRID);
            $svg .= sprintf('<text x="%d" y="%.1f" font-size="9.5" text-anchor="end" fill="%s" %s>%s</text>', $left - 6, $y + 3.5, self::TEXT, self::FONT, self::short($max * $i / 4));
        }
        return $svg;
    }

    private static function xLabels(array $labels, float $left, float $baseY, float $slot): string
    {
        $n = count($labels);
        $fit = max(2, (int) floor($slot * $n / 46)); // about 46px per label so they never touch
        $every = max(1, (int) ceil($n / $fit));
        $svg = '';
        foreach (array_values($labels) as $i => $label) {
            if ($i % $every !== 0) {
                continue;
            }
            $svg .= sprintf('<text x="%.1f" y="%.1f" font-size="9.5" text-anchor="middle" fill="%s" %s>%s</text>', $left + $slot * $i + $slot / 2, $baseY + 15, self::TEXT, self::FONT, self::esc(self::cut((string) $label, 10)));
        }
        return $svg;
    }

    private static function legend(array $series, int $left, int $w): string
    {
        if (count($series) < 2) {
            return '';
        }
        $svg = '';
        $x = $left;
        foreach ($series as $s) {
            $label = self::cut($s['label'], 26);
            $svg .= sprintf('<rect x="%d" y="6" width="10" height="10" rx="2" fill="%s"/>', $x, $s['color']);
            $svg .= sprintf('<text x="%d" y="15" font-size="10" fill="%s" %s>%s</text>', $x + 14, self::TEXT, self::FONT, self::esc($label));
            $x += 24 + (int) (strlen($label) * 5.6);
            if ($x > $w - 60) {
                break;
            }
        }
        return $svg;
    }

    private static function niceMax(float $v): float
    {
        if ($v <= 0) {
            return 1;
        }
        $mag = 10 ** floor(log10($v));
        foreach ([1, 1.2, 1.6, 2, 2.4, 3.2, 4, 6, 8, 10] as $m) { // quarters stay round (e.g. 1.6 -> 0.4 steps)
            if ($v <= $m * $mag) {
                return $m * $mag;
            }
        }
        return 10 * $mag;
    }

    private static function short(float $v): string
    {
        return match (true) {
            $v >= 1_000_000 => rtrim(rtrim(number_format($v / 1_000_000, 1), '0'), '.') . 'M',
            $v >= 1_000 => rtrim(rtrim(number_format($v / 1_000, 1), '0'), '.') . 'k',
            default => (string) round($v),
        };
    }

    private static function num(float $v): string
    {
        return number_format($v, fmod($v, 1.0) == 0.0 ? 0 : 2);
    }

    private static function cut(string $s, int $len): string
    {
        return mb_strlen($s) > $len ? mb_substr($s, 0, $len - 1) . '…' : $s;
    }

    private static function esc(string $s): string
    {
        return htmlspecialchars($s, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }
}
