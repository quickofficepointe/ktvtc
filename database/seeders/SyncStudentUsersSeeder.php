<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class SyncStudentUsersSeeder extends Seeder
{
    public function run(): void
    {
        // Get all students
        $students = Student::all();

        $created = 0;
        $skipped = 0;
        $errors = 0;

        $this->command->info('Starting student user synchronization...');
        $this->command->newLine();

        foreach ($students as $student) {
            try {
                // Skip if student already has a linked user
                $existingUser = User::where('student_id', $student->id)->first();

                if ($existingUser) {
                    $skipped++;
                    continue;
                }

                // Generate student number if missing
                $studentNumber = $student->student_number ?? $student->legacy_student_code;

                if (!$studentNumber) {
                    // Generate a student number if none exists
                    $studentNumber = 'STU/' . date('Y') . '/' . str_pad($student->id, 4, '0', STR_PAD_LEFT);
                    $student->student_number = $studentNumber;
                    $student->save();
                    $this->command->info("Generated student number for ID {$student->id}: {$studentNumber}");
                }

                // Check if user already exists by username
                $existingByUsername = User::where('username', $studentNumber)->first();

                if ($existingByUsername) {
                    // Link existing user to student
                    $existingByUsername->student_id = $student->id;
                    $existingByUsername->save();
                    $this->command->info("Linked existing user to student: {$studentNumber}");
                    $skipped++;
                    continue;
                }

                // Generate default password (student number in uppercase)
                $defaultPassword = strtoupper($studentNumber);

                // Get email from student or create default
                $email = $student->email ?? strtolower($studentNumber) . '@student.ktvtc.ac.ke';

                // Get full name
                $name = $student->full_name ?? trim($student->first_name . ' ' . $student->last_name);

                // Create user account using your actual database columns
                User::create([
                    'student_id' => $student->id,
                    'name' => $name,
                    'username' => $studentNumber,
                    'email' => $email,
                    'phone_number' => $student->phone ?? null,  // Using phone_number (matches your DB)
                    'profile_picture' => null,
                    'bio' => $student->remarks ?? 'Student account automatically created',
                    'role' => 5, // Student role
                    'is_approved' => true, // Auto-approve students
                    'password' => Hash::make($defaultPassword),
                    'email_verified_at' => now(),
                ]);

                $created++;
                $this->command->info("✓ Created user for student: {$studentNumber} | Email: {$email}");

            } catch (\Exception $e) {
                $errors++;
                $this->command->error("Error processing student ID {$student->id}: " . $e->getMessage());
            }
        }

        $this->command->newLine();
        $this->command->line("========== SEEDING COMPLETE ==========");
        $this->command->line("✓ Created: {$created}");
        $this->command->line("⤷ Skipped (already linked): {$skipped}");
        $this->command->line("✗ Errors: {$errors}");
        $this->command->line("======================================");

        if ($created > 0) {
            $this->command->newLine();
            $this->command->line("📝 Student Login Information:");
            $this->command->line("   Username: Student number");
            $this->command->line("   Password: Same as username in UPPERCASE");
            $this->command->newLine();
            $this->command->warn("⚠️  Students must change their password on first login!");
        }
    }
}
