<?php
// app/Console/Commands/GenerateIntakes.php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\MonthlyIntakeService;

class GenerateIntakes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'intakes:generate
                            {--course= : Course ID (for single course operations)}
                            {--start=2026 : Start year (default: 2026)}
                            {--end=2027 : End year (default: 2027)}
                            {--cleanup : Clean up past intakes}
                            {--list : List upcoming intakes}
                            {--stats : Show intake statistics}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate and manage monthly course intakes';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $service = app(MonthlyIntakeService::class);

        if ($this->option('cleanup')) {
            return $this->cleanupIntakes($service);
        }

        if ($this->option('list')) {
            return $this->listIntakes($service);
        }

        if ($this->option('stats')) {
            return $this->showStats($service);
        }

        return $this->generateIntakes($service);
    }

    private function generateIntakes(MonthlyIntakeService $service)
    {
        $courseId = $this->option('course');
        $startYear = (int)$this->option('start');
        $endYear = (int)$this->option('end');

        $this->info("====================================");
        $this->info("KTVTC Monthly Intake Generator");
        $this->info("====================================");
        $this->line("Period: {$startYear} - {$endYear}");

        if ($courseId) {
            $this->info("Generating for course ID: {$courseId}");
            $course = \App\Models\Course::find($courseId);

            if (!$course) {
                $this->error("Course not found!");
                return Command::FAILURE;
            }

            $result = $service->generateForCourse($course, $startYear, $endYear);
            $this->info("✓ Completed for: {$course->name}");
            $this->line("  Created: {$result['created']} intakes");
            $this->line("  Updated: {$result['updated']} intakes");
        } else {
            $this->info("Generating for ALL courses...");

            if (!$this->confirm("This will create intakes for ALL active courses. Continue?")) {
                $this->info('Operation cancelled.');
                return Command::SUCCESS;
            }

            $result = $service->generateForAllCourses($startYear, $endYear);

            $this->info("✓ GENERATION COMPLETE");
            $this->line("  Courses processed: {$result['total_courses']}");
            $this->line("  New intakes created: {$result['intakes_created']}");
            $this->line("  Existing intakes updated: {$result['intakes_updated']}");
        }

        $this->info("====================================");
        return Command::SUCCESS;
    }

    private function cleanupIntakes(MonthlyIntakeService $service)
    {
        $this->info("Cleaning up past intakes...");
        $deleted = $service->cleanupPastIntakes();
        $this->info("Deactivated {$deleted} past intakes");

        return Command::SUCCESS;
    }

    private function listIntakes(MonthlyIntakeService $service)
    {
        $this->info("Listing upcoming intakes...");
        $intakes = $service->getAllUpcomingIntakes(20);

        if ($intakes->isEmpty()) {
            $this->warn("No upcoming intakes found.");
            return Command::SUCCESS;
        }

        $this->table(
            ['ID', 'Course', 'Month', 'Year', 'Deadline', 'Status'],
            $intakes->map(function($intake) {
                return [
                    $intake->id,
                    $intake->course->name ?? 'N/A',
                    $intake->month,
                    $intake->year,
                    $intake->application_deadline ? $intake->application_deadline->format('M j, Y') : 'N/A',
                    $intake->is_active ? 'Active' : 'Inactive'
                ];
            })
        );

        return Command::SUCCESS;
    }

    private function showStats(MonthlyIntakeService $service)
    {
        $stats = $service->getIntakeStats();

        $this->info("====================================");
        $this->info("📊 INTAKE STATISTICS");
        $this->info("====================================");
        $this->line("2026 Total Intakes:   {$stats['2026_total']}");
        $this->line("2026 Active Intakes:  {$stats['2026_active']}");
        $this->line("2027 Total Intakes:   {$stats['2027_total']}");
        $this->line("2027 Active Intakes:  {$stats['2027_active']}");
        $this->info("====================================");

        return Command::SUCCESS;
    }
}
