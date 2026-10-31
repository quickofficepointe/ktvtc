<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * How a unit is graded on one academic track: two weighted scores (out of 100)
 * combined into a total, then a band.
 */
class GradingScale extends Model
{
    public const TRACKS = [
        'cdacc_cbet' => 'CDACC CBET',
        'knec_cbet'  => 'KNEC CBET',
        'internal'   => 'Internal (college)',
    ];

    protected $fillable = ['track', 'name', 'component_a', 'component_b', 'weight_a', 'weight_b', 'bands'];

    protected $casts = ['bands' => 'array'];

    public static function forTrack(?string $track): ?self
    {
        return $track ? static::where('track', $track)->first() : null;
    }

    /**
     * @return array{total: ?float, grade: ?string, passed: ?bool} nulls until both scores are in
     */
    public function grade(?float $a, ?float $b): array
    {
        if ($a === null || $b === null) {
            return ['total' => null, 'grade' => null, 'passed' => null];
        }

        $total = round($a * $this->weight_a / 100 + $b * $this->weight_b / 100, 2);

        foreach (collect($this->bands)->sortByDesc('min') as $band) {
            if ($total >= $band['min']) {
                return ['total' => $total, 'grade' => $band['label'], 'passed' => (bool) $band['passed']];
            }
        }

        return ['total' => $total, 'grade' => null, 'passed' => false];
    }
}
