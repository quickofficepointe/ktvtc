<?php

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
                            {--years=2 : Number of years to generate ahead}
                            {--cleanup : Clean up past intakes}
                            {--list : List upcoming intakes}';

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

        return $this->generateIntakes($service);
    }

    private function generateIntakes(MonthlyIntakeService $service)
    {
        $courseId = $this->option('course');
        $years = $this->option('years');

        if ($courseId) {
            $this->info("Generating monthly intakes for course ID: {$courseId}");
            $course = \App\Models\Course::find($courseId);

            if (!$course) {
                $this->error("Course not found!");
                return Command::FAILURE;
            }

            $result = $service->generateForCourse($course, $years);
            $this->info("Created: {$result['created']} intakes");
            $this->info("Updated: {$result['updated']} intakes");
        } else {
            $this->info("Generating monthly intakes for all courses...");
            $result = $service->generateForAllCourses($years);

            $this->info("Total courses processed: {$result['total_courses']}");
            $this->info("Intakes created: {$result['intakes_created']}");
            $this->info("Intakes updated: {$result['intakes_updated']}");
        }

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

        $this->table(
            ['Course', 'Month', 'Year', 'Deadline', 'Status'],
            $intakes->map(function($intake) {
                return [
                    $intake->course->name,
                    $intake->month,
                    $intake->year,
                    $intake->formatted_deadline ?? 'N/A',
                    $intake->is_application_open ? 'Open' : 'Closed'
                ];
            })
        );

        return Command::SUCCESS;
    }
}
