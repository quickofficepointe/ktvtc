<?php

namespace App\Console\Commands;

use App\Models\Blog;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class PublishScheduledBlogs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'blogs:publish-scheduled
                            {--dry-run : Preview blogs to be published without actually publishing}
                            {--limit= : Limit the number of blogs to process}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Publish scheduled blog posts that have reached their publication date';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Starting scheduled blog publication process...');

        $dryRun = $this->option('dry-run');
        $limit = $this->option('limit');

        // Build the query
        $query = Blog::where('is_published', false)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->orderBy('published_at', 'asc');

        if ($limit) {
            $query->limit((int)$limit);
        }

        $blogs = $query->get();

        if ($dryRun) {
            return $this->handleDryRun($blogs);
        }

        return $this->publishBlogs($blogs);
    }

    /**
     * Handle dry run mode - just show what would be published
     */
    private function handleDryRun($blogs): int
    {
        $this->info('DRY RUN MODE - No changes will be made');
        $this->newLine();

        if ($blogs->isEmpty()) {
            $this->info('No scheduled blogs ready for publication.');
            return Command::SUCCESS;
        }

        $this->info("Found {$blogs->count()} blog(s) ready for publication:");
        $this->newLine();

        $rows = $blogs->map(function($blog) {
            return [
                $blog->id,
                $blog->title,
                $blog->published_at->format('Y-m-d H:i:s'),
                $blog->author->name ?? 'Unknown',
                $blog->category->name ?? 'Uncategorized'
            ];
        })->toArray();

        $this->table(
            ['ID', 'Title', 'Scheduled Date', 'Author', 'Category'],
            $rows
        );

        return Command::SUCCESS;
    }

    /**
     * Publish the blogs
     */
    private function publishBlogs($blogs): int
    {
        if ($blogs->isEmpty()) {
            $this->info('No scheduled blogs ready for publication.');
            Log::info('No scheduled blogs ready for publication.', [
                'timestamp' => now(),
                'command' => 'blogs:publish-scheduled'
            ]);
            return Command::SUCCESS;
        }

        $this->info("Found {$blogs->count()} blog(s) ready for publication.");

        $publishedCount = 0;
        $failedCount = 0;
        $publishedIds = [];

        // Use transaction for safety
        DB::beginTransaction();

        try {
            foreach ($blogs as $blog) {
                try {
                    // Update blog status
                    $blog->update([
                        'is_published' => true,
                        'published_at' => now(),
                        'updated_at' => now()
                    ]);

                    $publishedCount++;
                    $publishedIds[] = $blog->id;

                    $this->info("✓ Published: {$blog->title} (ID: {$blog->id})");

                    // Log individual publication
                    Log::info('Blog published via scheduled command', [
                        'blog_id' => $blog->id,
                        'title' => $blog->title,
                        'published_at' => now(),
                        'command' => 'blogs:publish-scheduled'
                    ]);

                } catch (\Exception $e) {
                    $failedCount++;
                    $this->error("✗ Failed to publish: {$blog->title} (ID: {$blog->id})");
                    $this->error("  Error: {$e->getMessage()}");

                    Log::error('Failed to publish scheduled blog', [
                        'blog_id' => $blog->id,
                        'title' => $blog->title,
                        'error' => $e->getMessage(),
                        'timestamp' => now()
                    ]);
                }
            }

            // Commit transaction if all went well
            DB::commit();

            // Log summary
            Log::info('Scheduled blog publication completed', [
                'total_found' => $blogs->count(),
                'published' => $publishedCount,
                'failed' => $failedCount,
                'published_ids' => $publishedIds,
                'timestamp' => now()
            ]);

            // Display summary
            $this->newLine();
            $this->info("Publication Summary:");
            $this->info("  Total found: {$blogs->count()}");
            $this->info("  Successfully published: {$publishedCount}");
            $this->info("  Failed: {$failedCount}");

            if ($failedCount > 0) {
                $this->warn("Some blogs failed to publish. Check the logs for details.");
                return Command::FAILURE;
            }

            $this->info("✅ All scheduled blogs published successfully!");

        } catch (\Exception $e) {
            // Rollback on transaction failure
            DB::rollBack();

            $this->error("Transaction failed: {$e->getMessage()}");
            Log::critical('Blog publication transaction failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'timestamp' => now()
            ]);

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
