<?php

namespace App\Console\Commands;

use App\Models\Book;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * One-off: books only had typed copy counts, but lending needs barcoded copies.
 * For each book this creates the missing copies (B<book id>-01, -02 ...): enough
 * to reach the old available count as 'available', the rest of the old total as
 * 'missing' until a stock-take finds them. Afterwards counts come from copies only.
 * Safe to re-run: books whose copies already cover the counts are left alone.
 */
class GenerateLibraryCopies extends Command
{
    protected $signature = 'library:generate-copies {--dry-run : Show what would be created without saving}';

    protected $description = 'Create barcoded copies for books that only have typed copy counts';

    public function handle(): int
    {
        $rows = [];
        $totals = ['available' => 0, 'missing' => 0];

        foreach (Book::orderBy('id')->get() as $book) {
            $items = $book->items()->selectRaw('status, COUNT(*) as n')->groupBy('status')->pluck('n', 'status');
            $have = (int) $items->sum();
            $wantTotal = max((int) $book->total_copies, $have);
            $wantAvailable = min(max((int) $book->available_copies, 0), $wantTotal);

            $addAvailable = max(0, $wantAvailable - (int) ($items['available'] ?? 0));
            $addAvailable = min($addAvailable, $wantTotal - $have);
            $addMissing = max(0, $wantTotal - $have - $addAvailable);

            if ($addAvailable + $addMissing === 0) {
                continue;
            }
            $rows[] = [$book->id, mb_strimwidth($book->title, 0, 40, '…'), $have, $addAvailable, $addMissing];
            $totals['available'] += $addAvailable;
            $totals['missing'] += $addMissing;

            if (!$this->option('dry-run')) {
                DB::transaction(function () use ($book, $addAvailable, $addMissing) {
                    $book->createCopies($addAvailable);
                    $book->createCopies($addMissing, null, 'missing');
                });
            }
        }

        if (!$this->option('dry-run')) {
            Book::all()->each->syncCopyCounts();
        }

        $this->table(['Book', 'Title', 'Copies before', 'New on shelf', 'New missing'], $rows);
        $this->info(($this->option('dry-run') ? 'Dry run: would create ' : 'Created ')
            . "{$totals['available']} copies on the shelf and {$totals['missing']} marked missing.");
        $this->line('Next: print the barcodes (Library > Items), stick them on the books, and set copies found in the stock-take back to Available.');

        return self::SUCCESS;
    }
}
