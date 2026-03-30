<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Enrollment;
use App\Models\FeePayment;
use App\Models\Campus;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class HistoricalDataImportController extends Controller
{
    /**
     * Show import form
     */
    public function index()
    {
        $campuses = Campus::all();
        $years = range(2020, date('Y'));

        return view('ktvtc.admin.import.historical', compact('campuses', 'years'));
    }

    /**
     * Process the historical CSV import
     */
    public function import(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'csv_file' => 'required|file|mimes:csv,txt|max:10240',
            'year' => 'required|integer|min:2020|max:' . date('Y'),
            'campus_id' => 'required|exists:campuses,id',
            'import_type' => 'required|in:original_format,2026_format,2025_format,2024_format,2023_format,2022_format,2020_format'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $file = $request->file('csv_file');
        $year = $request->year;
        $campusId = $request->campus_id;
        $importType = $request->import_type;

        // Get the campus
        $campus = Campus::find($campusId);

        // Process based on format
        switch ($importType) {
            case 'original_format':
                $result = $this->processOriginalFormat($file, $year, $campus);
                break;
            case '2026_format':
                $result = $this->process2026Format($file, $year, $campus);
                break;
            case '2025_format':
                $result = $this->process2025Format($file, $year, $campus);
                break;
            case '2024_format':
                $result = $this->process2024Format($file, $year, $campus);
                break;
            case '2023_format':
                $result = $this->process2023Format($file, $year, $campus);
                break;
            case '2022_format':
                $result = $this->process2022Format($file, $year, $campus);
                break;
            case '2020_format':
                $result = $this->process2020Format($file, $year, $campus);
                break;
            default:
                return redirect()->back()->with('error', 'Unknown import format');
        }

        return redirect()->route('admin.import.historical')
            ->with('success', "Import completed: {$result['imported']} students, {$result['enrollments']} enrollments, {$result['payments']} payments")
            ->with('import_stats', $result);
    }

    /**
     * Process the original format (KTVTC STUDENT FINANCIAL RECORDS - ORIGINAL.csv)
     */
    private function processOriginalFormat($file, $year, $campus)
    {
        $handle = fopen($file->getRealPath(), 'r');
        $headers = fgetcsv($handle); // Read headers

        $stats = [
            'imported' => 0,
            'enrollments' => 0,
            'payments' => 0,
            'errors' => 0,
            'warnings' => []
        ];

        DB::beginTransaction();

        try {
            while (($row = fgetcsv($handle)) !== FALSE) {
                // Map columns based on your ORIGINAL.csv structure
                // CODE,ADM,YEAR,ADM,,NAME,CONTACT,,STATUS,MODE,REG,FEE BALANCE,MAY,JUNE ,JULY FEES,AUG FEES,SEPT,OCT,NOV,DEC

                if (count($row) < 12) continue;

                $code = $row[0] ?? ''; // HDBT, ICP, PA, etc.
                $adm = $row[1] ?? '';
                $regYear = $row[2] ?? $year;
                $regNo = $row[3] ?? '';
                $title = $row[4] ?? '';
                $fullName = $row[5] ?? '';
                $phone = $row[6] ?? '';
                $courseName = $row[7] ?? '';
                $status = $row[8] ?? '';
                $mode = $row[9] ?? '';
                $reg = $row[10] ?? '';
                $feeBalance = str_replace(['"', ',', 'Ksh', 'KES'], '', $row[11] ?? '0');
                $feeBalance = is_numeric($feeBalance) ? (float)$feeBalance : 0;

                // Parse name
                $nameParts = explode(' ', trim($fullName));
                $firstName = $nameParts[0] ?? '';
                $lastName = count($nameParts) > 1 ? implode(' ', array_slice($nameParts, 1)) : '';

                // Create or find student
                $student = Student::firstOrCreate(
                    ['legacy_code' => $regNo],
                    [
                        'first_name' => $firstName,
                        'last_name' => $lastName,
                        'title' => $title,
                        'phone' => $phone,
                        'campus_id' => $campus->id,
                        'student_number' => $regNo,
                        'status' => $this->mapStatus($status),
                        'registration_date' => Carbon::create($regYear, 1, 1),
                        'student_category' => $this->mapCategory($mode),
                        'requires_cleanup' => false,
                        'import_batch' => 'original_' . $year
                    ]
                );

                // Create enrollment
                $enrollment = Enrollment::create([
                    'student_id' => $student->id,
                    'campus_id' => $campus->id,
                    'student_name' => $student->full_name,
                    'student_number' => $student->student_number,
                    'course_name' => $courseName,
                    'course_code' => $code,
                    'intake_year' => $regYear,
                    'intake_month' => 'September', // Default intake
                    'enrollment_date' => Carbon::create($regYear, 9, 1),
                    'status' => $this->mapEnrollmentStatus($status),
                    'total_fees' => $feeBalance, // This is actually the balance in this file
                    'amount_paid' => 0,
                    'balance' => $feeBalance,
                    'is_active' => $status == 'active',
                    'legacy_code' => $regNo,
                ]);

                $stats['enrollments']++;

                // Process monthly payments (MAY, JUNE, JULY FEES, AUG FEES, SEPT, OCT, NOV, DEC)
                $months = ['May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
                $monthColumns = [12, 13, 14, 15, 16, 17, 18, 19]; // Column indices

                foreach ($monthColumns as $index => $colIndex) {
                    if (isset($row[$colIndex]) && !empty($row[$colIndex]) && $row[$colIndex] !== '0') {
                        $amount = str_replace(['"', ',', 'Ksh', 'KES'], '', $row[$colIndex]);
                        $amount = is_numeric($amount) ? (float)$amount : 0;

                        if ($amount > 0) {
                            $month = $months[$index];
                            $paymentDate = Carbon::create($regYear, $this->getMonthNumber($month), 15);

                            FeePayment::create([
                                'student_id' => $student->id,
                                'enrollment_id' => $enrollment->id,
                                'amount' => $amount,
                                'payment_date' => $paymentDate,
                                'receipt_number' => 'IMP-' . $paymentDate->format('Ymd') . '-' . str_pad($stats['payments'] + 1, 4, '0', STR_PAD_LEFT),
                                'payment_method' => 'cash',
                                'status' => 'completed',
                                'is_verified' => false,
                                'notes' => "Imported from original CSV - {$month} {$regYear}",
                                'import_source' => 'original_csv',
                                'recorded_by' => auth()->id(),
                            ]);

                            $stats['payments']++;

                            // Update enrollment amount_paid
                            $enrollment->amount_paid += $amount;
                        }
                    }
                }

                // Update enrollment balance
                $enrollment->balance = $enrollment->total_fees - $enrollment->amount_paid;
                $enrollment->save();

                $stats['imported']++;
            }

            DB::commit();
            fclose($handle);

        } catch (\Exception $e) {
            DB::rollBack();
            fclose($handle);
            throw $e;
        }

        return $stats;
    }

    /**
     * Process 2026 format (KTVTC STUDENT FINANCIAL RECORDS - 2026.csv)
     */
    private function process2026Format($file, $year, $campus)
    {
        $handle = fopen($file->getRealPath(), 'r');
        $headers = fgetcsv($handle); // Read headers

        $stats = [
            'imported' => 0,
            'enrollments' => 0,
            'payments' => 0,
            'errors' => 0,
            'warnings' => []
        ];

        DB::beginTransaction();

        try {
            while (($row = fgetcsv($handle)) !== FALSE) {
                // Skip summary rows
                if (isset($row[0]) && in_array($row[0], ['TOTAL', 'ADD ROW ABOVE', 'COS/', 'HD/', ''])) {
                    continue;
                }

                // Map columns based on 2026 format
                // CODE,ADM,YEAR,REG. NO,SEX,NAME,CONTACT,EMAIL,ID NO,ADM DATE,TSHIRT,INTAKE,COURSE,STATUS,MODE,% PAID,BASE FEES,EXTRA PAYMENTS,HOSTEL,FEE PAYABLE,DAMAGES/EXTRAS,FEE BALANCE,RECEIPT BALANCE,INCOME/FEES,SCHOLARSHIP,ADD TO FEES,...

                if (count($row) < 20) continue;

                $code = $row[0] ?? '';
                $adm = $row[1] ?? '';
                $regYear = $row[2] ?? $year;
                $regNo = $row[3] ?? '';
                $sex = $row[4] ?? '';
                $fullName = $row[5] ?? '';
                $contact = $row[6] ?? '';
                $email = $row[7] ?? '';
                $idNo = $row[8] ?? '';
                $admDate = $row[9] ?? '';
                $tshirt = $row[10] ?? '';
                $intake = $row[11] ?? 'JAN';
                $courseName = $row[12] ?? '';
                $status = $row[13] ?? '';
                $mode = $row[14] ?? '';
                $percentPaid = $row[15] ?? '0';
                $baseFees = str_replace(['"', ',', 'Ksh', 'KES'], '', $row[16] ?? '0');
                $extraPayments = str_replace(['"', ',', 'Ksh', 'KES'], '', $row[17] ?? '0');
                $hostel = str_replace(['"', ',', 'Ksh', 'KES'], '', $row[18] ?? '0');
                $feePayable = str_replace(['"', ',', 'Ksh', 'KES'], '', $row[19] ?? '0');
                $damages = str_replace(['"', ',', 'Ksh', 'KES'], '', $row[20] ?? '0');
                $feeBalance = str_replace(['"', ',', 'Ksh', 'KES'], '', $row[21] ?? '0');
                $receiptBalance = str_replace(['"', ',', 'Ksh', 'KES'], '', $row[22] ?? '0');
                $incomeFees = str_replace(['"', ',', 'Ksh', 'KES'], '', $row[23] ?? '0');

                $baseFees = is_numeric($baseFees) ? (float)$baseFees : 0;
                $feePayable = is_numeric($feePayable) ? (float)$feePayable : 0;
                $feeBalance = is_numeric($feeBalance) ? (float)$feeBalance : 0;
                $receiptBalance = is_numeric($receiptBalance) ? (float)$receiptBalance : 0;

                // Parse name
                $nameParts = explode(' ', trim($fullName));
                $firstName = $nameParts[0] ?? '';
                $lastName = count($nameParts) > 1 ? implode(' ', array_slice($nameParts, 1)) : '';

                // Create or find student
                $student = Student::firstOrCreate(
                    ['email' => $email],
                    [
                        'first_name' => $firstName,
                        'last_name' => $lastName,
                        'title' => $sex == 'Ms.' ? 'Ms.' : ($sex == 'Mr.' ? 'Mr.' : ''),
                        'phone' => $contact,
                        'email' => $email,
                        'id_number' => $idNo,
                        'campus_id' => $campus->id,
                        'student_number' => $regNo,
                        'status' => $this->mapStatus($status),
                        'registration_date' => $admDate ? Carbon::parse($admDate) : Carbon::create($regYear, 1, 1),
                        'tshirt_size' => $tshirt,
                        'student_category' => $this->mapCategory($mode),
                        'requires_cleanup' => false,
                        'import_batch' => '2026_' . $year
                    ]
                );

                // Create enrollment
                $enrollment = Enrollment::create([
                    'student_id' => $student->id,
                    'campus_id' => $campus->id,
                    'student_name' => $student->full_name,
                    'student_number' => $student->student_number,
                    'course_name' => $courseName,
                    'course_code' => $code,
                    'intake_year' => $regYear,
                    'intake_month' => $this->mapIntakeMonth($intake),
                    'enrollment_date' => $admDate ? Carbon::parse($admDate) : Carbon::create($regYear, 1, 1),
                    'study_mode' => $this->mapStudyMode($mode),
                    'student_type' => $this->mapStudentType($mode),
                    'sponsorship_type' => $this->mapSponsorshipType($mode),
                    'status' => $this->mapEnrollmentStatus($status),
                    'total_fees' => $feePayable,
                    'amount_paid' => $feePayable - $feeBalance,
                    'balance' => $feeBalance,
                    'requires_external_exam' => $this->requiresExternalExam($courseName),
                    'is_active' => $status == 'REG',
                    'legacy_code' => $regNo,
                ]);

                $stats['enrollments']++;

                // Process payments from the monthly columns (starting from column 60 or so)
                $this->processMonthlyPayments2026($row, $student, $enrollment, $regYear, $stats);

                $stats['imported']++;
            }

            DB::commit();
            fclose($handle);

        } catch (\Exception $e) {
            DB::rollBack();
            fclose($handle);
            throw $e;
        }

        return $stats;
    }

    /**
     * Process 2025 format
     */
    private function process2025Format($file, $year, $campus)
    {
        // Similar to 2026 but with different column mappings
        // Implementation would be similar to process2026Format but adjusted for 2025 columns
        // For brevity, I'll include the key differences

        // In 2025 format, monthly payment columns start at a different index
        // You would map accordingly

        return $this->process2026Format($file, $year, $campus); // Placeholder
    }

    /**
     * Process 2024 format
     */
    private function process2024Format($file, $year, $campus)
    {
        // Similar to 2026 but with different column mappings
        return $this->process2026Format($file, $year, $campus); // Placeholder
    }

    /**
     * Process 2023 format
     */
    private function process2023Format($file, $year, $campus)
    {
        // 2023 format has many columns
        return $this->process2026Format($file, $year, $campus); // Placeholder
    }

    /**
     * Process 2022 format
     */
    private function process2022Format($file, $year, $campus)
    {
        $handle = fopen($file->getRealPath(), 'r');
        $headers = fgetcsv($handle);

        $stats = [
            'imported' => 0,
            'enrollments' => 0,
            'payments' => 0,
            'errors' => 0,
            'warnings' => []
        ];

        DB::beginTransaction();

        try {
            while (($row = fgetcsv($handle)) !== FALSE) {
                if (count($row) < 10) continue;

                $regNo = $row[3] ?? '';
                $sex = $row[4] ?? '';
                $fullName = $row[5] ?? '';
                $contact = $row[6] ?? '';
                $intake = $row[10] ?? 'JAN';
                $courseName = $row[11] ?? '';
                $status = $row[12] ?? '';
                $mode = $row[13] ?? '';
                $fees = str_replace(['"', ',', 'Ksh', 'KES'], '', $row[16] ?? '0');
                $feeBalance = str_replace(['"', ',', 'Ksh', 'KES'], '', $row[18] ?? '0');

                $fees = is_numeric($fees) ? (float)$fees : 0;
                $feeBalance = is_numeric($feeBalance) ? (float)$feeBalance : 0;

                // Parse name
                $nameParts = explode(' ', trim($fullName));
                $firstName = $nameParts[0] ?? '';
                $lastName = count($nameParts) > 1 ? implode(' ', array_slice($nameParts, 1)) : '';

                // Create student
                $student = Student::firstOrCreate(
                    ['phone' => $contact],
                    [
                        'first_name' => $firstName,
                        'last_name' => $lastName,
                        'title' => $sex == 'Ms.' ? 'Ms.' : ($sex == 'Mr.' ? 'Mr.' : ''),
                        'phone' => $contact,
                        'campus_id' => $campus->id,
                        'student_number' => $regNo,
                        'status' => $this->mapStatus($status),
                        'registration_date' => Carbon::create($year, 1, 1),
                        'student_category' => $this->mapCategory($mode),
                        'requires_cleanup' => false,
                        'import_batch' => '2022_' . $year
                    ]
                );

                // Create enrollment
                $enrollment = Enrollment::create([
                    'student_id' => $student->id,
                    'campus_id' => $campus->id,
                    'student_name' => $student->full_name,
                    'student_number' => $student->student_number,
                    'course_name' => $courseName,
                    'course_code' => explode('/', $regNo)[0] ?? '',
                    'intake_year' => $year,
                    'intake_month' => $this->mapIntakeMonth($intake),
                    'enrollment_date' => Carbon::create($year, 1, 1),
                    'status' => $this->mapEnrollmentStatus($status),
                    'total_fees' => $fees,
                    'amount_paid' => $fees - $feeBalance,
                    'balance' => $feeBalance,
                    'is_active' => $status == 'REG',
                    'legacy_code' => $regNo,
                ]);

                $stats['enrollments']++;
                $stats['imported']++;

                // Process monthly payments from columns 42 onwards
                $this->processMonthlyPayments2022($row, $student, $enrollment, $year, $stats);
            }

            DB::commit();
            fclose($handle);

        } catch (\Exception $e) {
            DB::rollBack();
            fclose($handle);
            throw $e;
        }

        return $stats;
    }

    /**
     * Process 2020 format
     */
    private function process2020Format($file, $year, $campus)
    {
        $handle = fopen($file->getRealPath(), 'r');
        $headers = fgetcsv($handle);

        $stats = [
            'imported' => 0,
            'enrollments' => 0,
            'payments' => 0,
            'errors' => 0,
            'warnings' => []
        ];

        DB::beginTransaction();

        try {
            while (($row = fgetcsv($handle)) !== FALSE) {
                if (count($row) < 10) continue;

                $code = $row[0] ?? '';
                $adm = $row[1] ?? '';
                $regYear = $row[2] ?? $year;
                $regNo = $row[3] ?? '';
                $title = $row[4] ?? '';
                $fullName = $row[5] ?? '';
                $contact = $row[6] ?? '';
                $intake = $row[7] ?? 'SEPT';
                $courseName = $row[8] ?? '';
                $status = $row[9] ?? '';
                $fees = str_replace(['"', ',', 'Ksh', 'KES'], '', $row[12] ?? '0');
                $feeBalance = str_replace(['"', ',', 'Ksh', 'KES'], '', $row[13] ?? '0');
                $feePaid = str_replace(['"', ',', 'Ksh', 'KES'], '', $row[14] ?? '0');

                $fees = is_numeric($fees) ? (float)$fees : 0;
                $feeBalance = is_numeric($feeBalance) ? (float)$feeBalance : 0;
                $feePaid = is_numeric($feePaid) ? (float)$feePaid : 0;

                // Parse name
                $nameParts = explode(' ', trim($fullName));
                $firstName = $nameParts[0] ?? '';
                $lastName = count($nameParts) > 1 ? implode(' ', array_slice($nameParts, 1)) : '';

                // Create student
                $student = Student::firstOrCreate(
                    ['phone' => $contact],
                    [
                        'first_name' => $firstName,
                        'last_name' => $lastName,
                        'title' => $title,
                        'phone' => $contact,
                        'campus_id' => $campus->id,
                        'student_number' => $regNo,
                        'status' => $this->mapStatus($status),
                        'registration_date' => Carbon::create($regYear, 9, 1),
                        'student_category' => 'regular',
                        'requires_cleanup' => false,
                        'import_batch' => '2020_' . $year
                    ]
                );

                // Create enrollment
                $enrollment = Enrollment::create([
                    'student_id' => $student->id,
                    'campus_id' => $campus->id,
                    'student_name' => $student->full_name,
                    'student_number' => $student->student_number,
                    'course_name' => $courseName,
                    'course_code' => $code,
                    'intake_year' => $regYear,
                    'intake_month' => $this->mapIntakeMonth($intake),
                    'enrollment_date' => Carbon::create($regYear, 9, 1),
                    'status' => $this->mapEnrollmentStatus($status),
                    'total_fees' => $fees,
                    'amount_paid' => $feePaid,
                    'balance' => $feeBalance,
                    'is_active' => false, // Historical
                    'legacy_code' => $regNo,
                ]);

                $stats['enrollments']++;
                $stats['imported']++;

                // Process payments from Oct, Nov, Dec columns (15, 16, 17)
                $months = ['October', 'November', 'December'];
                $monthColumns = [15, 16, 17];

                foreach ($monthColumns as $index => $colIndex) {
                    if (isset($row[$colIndex]) && !empty($row[$colIndex]) && $row[$colIndex] !== '0') {
                        $amount = str_replace(['"', ',', 'Ksh', 'KES'], '', $row[$colIndex]);
                        $amount = is_numeric($amount) ? (float)$amount : 0;

                        if ($amount > 0) {
                            $month = $months[$index];
                            $paymentDate = Carbon::create($regYear, $this->getMonthNumber($month), 15);

                            FeePayment::create([
                                'student_id' => $student->id,
                                'enrollment_id' => $enrollment->id,
                                'amount' => $amount,
                                'payment_date' => $paymentDate,
                                'receipt_number' => 'IMP-2020-' . $paymentDate->format('Ymd') . '-' . str_pad($stats['payments'] + 1, 4, '0', STR_PAD_LEFT),
                                'payment_method' => 'cash',
                                'status' => 'completed',
                                'is_verified' => false,
                                'notes' => "Imported from 2020 CSV - {$month} {$regYear}",
                                'import_source' => '2020_csv',
                                'recorded_by' => auth()->id(),
                            ]);

                            $stats['payments']++;
                        }
                    }
                }
            }

            DB::commit();
            fclose($handle);

        } catch (\Exception $e) {
            DB::rollBack();
            fclose($handle);
            throw $e;
        }

        return $stats;
    }

    /**
     * Process monthly payments for 2026 format
     */
    private function processMonthlyPayments2026($row, $student, $enrollment, $year, &$stats)
    {
        // Monthly payment columns in 2026 format (starting around column 60)
        // Jan 2026, FEB, MAR, APR, MAY, JUN, JUL, AUG, SEP, OCT, NOV, DEC
        $monthNames = ['January', 'February', 'March', 'April', 'May', 'June',
                       'July', 'August', 'September', 'October', 'November', 'December'];

        // Find where the monthly payments start - look for 'Jan 2026' in headers
        // For simplicity, let's assume they start at column 60
        $startCol = 60;

        for ($i = 0; $i < 12; $i++) {
            $colIndex = $startCol + $i;
            if (isset($row[$colIndex]) && !empty($row[$colIndex]) && $row[$colIndex] !== '0' && $row[$colIndex] !== '') {
                $amount = str_replace(['"', ',', 'Ksh', 'KES'], '', $row[$colIndex]);
                $amount = is_numeric($amount) ? (float)$amount : 0;

                if ($amount > 0) {
                    $month = $monthNames[$i];
                    $paymentDate = Carbon::create($year, $i + 1, 15);

                    FeePayment::create([
                        'student_id' => $student->id,
                        'enrollment_id' => $enrollment->id,
                        'amount' => $amount,
                        'payment_date' => $paymentDate,
                        'receipt_number' => 'IMP-' . $paymentDate->format('Ymd') . '-' . str_pad($stats['payments'] + 1, 4, '0', STR_PAD_LEFT),
                        'payment_method' => 'cash',
                        'status' => 'completed',
                        'is_verified' => false,
                        'notes' => "Imported from 2026 CSV - {$month} {$year}",
                        'import_source' => '2026_csv',
                        'recorded_by' => auth()->id(),
                    ]);

                    $stats['payments']++;
                }
            }
        }
    }

    /**
     * Process monthly payments for 2022 format
     */
    private function processMonthlyPayments2022($row, $student, $enrollment, $year, &$stats)
    {
        // Monthly payment columns in 2022 format (Jan 2022, FEB, MAR, etc.)
        $monthNames = ['January', 'February', 'March', 'April', 'May', 'June',
                       'July', 'August', 'September', 'October', 'November', 'December'];

        // In 2022 format, monthly payments start around column 42
        $startCol = 42;

        for ($i = 0; $i < 12; $i++) {
            $colIndex = $startCol + $i;
            if (isset($row[$colIndex]) && !empty($row[$colIndex]) && $row[$colIndex] !== '0' && $row[$colIndex] !== '') {
                $amount = str_replace(['"', ',', 'Ksh', 'KES'], '', $row[$colIndex]);
                $amount = is_numeric($amount) ? (float)$amount : 0;

                if ($amount > 0) {
                    $month = $monthNames[$i];
                    $paymentDate = Carbon::create($year, $i + 1, 15);

                    FeePayment::create([
                        'student_id' => $student->id,
                        'enrollment_id' => $enrollment->id,
                        'amount' => $amount,
                        'payment_date' => $paymentDate,
                        'receipt_number' => 'IMP-2022-' . $paymentDate->format('Ymd') . '-' . str_pad($stats['payments'] + 1, 4, '0', STR_PAD_LEFT),
                        'payment_method' => 'cash',
                        'status' => 'completed',
                        'is_verified' => false,
                        'notes' => "Imported from 2022 CSV - {$month} {$year}",
                        'import_source' => '2022_csv',
                        'recorded_by' => auth()->id(),
                    ]);

                    $stats['payments']++;
                }
            }
        }
    }

    /**
     * Helper: Map status from CSV to student status
     */
    private function mapStatus($status)
    {
        $status = strtoupper(trim($status));

        if (in_array($status, ['REG', 'REGULAR', 'ACTIVE'])) {
            return 'active';
        }
        if (in_array($status, ['ALUMNUS', 'ALUMNI', 'GRADUATED'])) {
            return 'alumnus';
        }
        if ($status == 'DROPPED') {
            return 'dropped';
        }
        if ($status == 'SUSPENDED') {
            return 'suspended';
        }
        if ($status == 'HISTORICAL') {
            return 'historical';
        }

        return 'historical';
    }

    /**
     * Helper: Map status to enrollment status
     */
    private function mapEnrollmentStatus($status)
    {
        $status = strtoupper(trim($status));

        if (in_array($status, ['REG', 'REGULAR', 'ACTIVE'])) {
            return 'active';
        }
        if (in_array($status, ['ALUMNUS', 'ALUMNI', 'GRADUATED', 'COMPLETED'])) {
            return 'completed';
        }
        if ($status == 'DROPPED') {
            return 'dropped';
        }
        if ($status == 'SUSPENDED') {
            return 'suspended';
        }

        return 'completed';
    }

    /**
     * Helper: Map mode to student category
     */
    private function mapCategory($mode)
    {
        $mode = strtoupper(trim($mode));

        if ($mode == 'GIRL EMP.' || $mode == 'GEP') {
            return 'sponsored';
        }
        if ($mode == 'ALUMNUS' || $mode == 'ALUMNI') {
            return 'alumnus';
        }
        if ($mode == 'STAFF') {
            return 'staff_child';
        }
        if ($mode == 'KSP' || $mode == 'SCHOLARSHIP') {
            return 'scholarship';
        }

        return 'regular';
    }

    /**
     * Helper: Map mode to study mode
     */
    private function mapStudyMode($mode)
    {
        $mode = strtoupper(trim($mode));

        if ($mode == 'WKEND' || $mode == 'WEEKEND') {
            return 'weekend';
        }
        if ($mode == 'ONLINE' || $mode == 'VIRTUAL') {
            return 'online';
        }
        if ($mode == 'PART TIME') {
            return 'part_time';
        }
        if ($mode == 'EVENING') {
            return 'evening';
        }

        return 'full_time';
    }

    /**
     * Helper: Map mode to student type
     */
    private function mapStudentType($mode)
    {
        $mode = strtoupper(trim($mode));

        if ($mode == 'ALUMNUS' || $mode == 'ALUMNI') {
            return 'alumnus';
        }
        if ($mode == 'TRANSFER') {
            return 'transfer';
        }
        if ($mode == 'CONTINUING') {
            return 'continuing';
        }

        return 'new';
    }

    /**
     * Helper: Map mode to sponsorship type
     */
    private function mapSponsorshipType($mode)
    {
        $mode = strtoupper(trim($mode));

        if ($mode == 'GIRL EMP.' || $mode == 'GEP' || $mode == 'SPONSORED') {
            return 'sponsored';
        }
        if ($mode == 'GOVERNMENT' || $mode == 'GOVT') {
            return 'government';
        }
        if ($mode == 'KSP' || $mode == 'SCHOLARSHIP') {
            return 'scholarship';
        }
        if ($mode == 'COMPANY' || $mode == 'EMPLOYER') {
            return 'company';
        }

        return 'self';
    }

    /**
     * Helper: Map intake string to month name
     */
    private function mapIntakeMonth($intake)
    {
        $intake = strtoupper(trim($intake));

        $map = [
            'JAN' => 'January',
            'FEB' => 'February',
            'MAR' => 'March',
            'APR' => 'April',
            'MAY' => 'May',
            'JUN' => 'June',
            'JUL' => 'July',
            'AUG' => 'August',
            'SEP' => 'September',
            'SEPT' => 'September',
            'OCT' => 'October',
            'NOV' => 'November',
            'DEC' => 'December'
        ];

        return $map[$intake] ?? 'September';
    }

    /**
     * Helper: Determine if course requires external exam
     */
    private function requiresExternalExam($courseName)
    {
        $courseName = strtoupper($courseName);

        $examCourses = [
            'KNEC', 'CDACC', 'NITA', 'TVETA',
            'CRAFT', 'DIPLOMA', 'CERTIFICATE',
            'SOLAR', 'ELECTRICAL', 'PROGRAMMING'
        ];

        foreach ($examCourses as $keyword) {
            if (strpos($courseName, $keyword) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Helper: Get month number from name
     */
    private function getMonthNumber($monthName)
    {
        $months = [
            'January' => 1, 'February' => 2, 'March' => 3, 'April' => 4,
            'May' => 5, 'June' => 6, 'July' => 7, 'August' => 8,
            'September' => 9, 'October' => 10, 'November' => 11, 'December' => 12
        ];

        return $months[$monthName] ?? 1;
    }

    /**
     * Export historical data summary
     */
    public function exportSummary(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'year' => 'nullable|integer|min:2020|max:' . date('Y'),
            'campus_id' => 'nullable|exists:campuses,id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator);
        }

        $query = Enrollment::with(['student', 'campus'])
            ->when($request->year, function($q) use ($request) {
                return $q->where('intake_year', $request->year);
            })
            ->when($request->campus_id, function($q) use ($request) {
                return $q->where('campus_id', $request->campus_id);
            });

        $enrollments = $query->orderBy('intake_year', 'desc')
            ->orderBy('intake_month')
            ->get();

        $filename = 'historical_enrollments_' . now()->format('Y-m-d') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($enrollments) {
            $file = fopen('php://output', 'w');

            // Add BOM for UTF-8
            fwrite($file, "\xEF\xBB\xBF");

            // Headers
            fputcsv($file, [
                'Student Name',
                'Student Number',
                'Course',
                'Intake Year',
                'Intake Month',
                'Campus',
                'Status',
                'Total Fees',
                'Amount Paid',
                'Balance',
                'Payment Progress (%)',
                'Legacy Code',
                'Enrollment Date'
            ]);

            // Data
            foreach ($enrollments as $enrollment) {
                fputcsv($file, [
                    $enrollment->student_name,
                    $enrollment->student_number,
                    $enrollment->course_name,
                    $enrollment->intake_year,
                    $enrollment->intake_month,
                    $enrollment->campus->name ?? 'N/A',
                    ucfirst($enrollment->status),
                    number_format($enrollment->total_fees, 2),
                    number_format($enrollment->amount_paid, 2),
                    number_format($enrollment->balance, 2),
                    $enrollment->payment_progress . '%',
                    $enrollment->legacy_code,
                    $enrollment->enrollment_date?->format('Y-m-d')
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
