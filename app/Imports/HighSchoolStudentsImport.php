<?php

namespace App\Imports;

use App\Models\HighSchoolStudent;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Carbon\Carbon;

class HighSchoolStudentsImport implements ToCollection, WithHeadingRow, WithChunkReading, WithBatchInserts, SkipsOnFailure
{
    use SkipsFailures;

    protected $importedCount = 0;
    protected $updatedCount = 0;
    protected $errorCount = 0;
    protected $warningCount = 0;
    protected $errors = [];
    protected $warnings = [];
    protected $rowNumber = 0;
    protected $className = null;
    protected $format = 'standard';
    protected $headers = [];

    public function __construct($className = null)
    {
        $this->className = $className;
    }

    public function collection(Collection $rows)
    {
        DB::beginTransaction();

        try {
            // Get the actual headers from the first row
            $firstRow = $rows->first();
            if ($firstRow) {
                $this->headers = array_keys($firstRow->toArray());
                // Determine the file format based on headers
                $this->format = $this->detectFormat($this->headers);
            }

            foreach ($rows as $index => $row) {
                $this->rowNumber = $index + 2;

                // Skip empty rows
                if ($this->isEmptyRow($row)) {
                    continue;
                }

                // Map columns based on detected format
                $mappedData = $this->mapColumns($row, $this->format);

                // Skip if no name provided
                if (empty($mappedData['full_name'])) {
                    $this->addWarning($this->rowNumber, 'Skipped: No name provided');
                    $this->warningCount++;
                    continue;
                }

                // Validate the row data
                $validator = $this->validateRow($mappedData);

                if ($validator->fails()) {
                    $this->addError($this->rowNumber, $validator->errors()->all());
                    $this->errorCount++;
                    continue;
                }

                // Prepare data for insertion
                $studentData = $this->prepareStudentData($mappedData);

                // Check for existing student by admission number
                $existingStudent = null;
                if (!empty($studentData['admission_number'])) {
                    $existingStudent = HighSchoolStudent::where('admission_number', $studentData['admission_number'])->first();
                }

                if ($existingStudent) {
                    // Update existing student
                    $existingStudent->update($studentData);
                    $this->updatedCount++;
                    $this->addWarning($this->rowNumber, 'Updated existing student: ' . $studentData['admission_number']);
                } else {
                    // Create new student
                    HighSchoolStudent::create($studentData);
                    $this->importedCount++;
                }
            }

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Check if row is empty
     */
    protected function isEmptyRow($row)
    {
        $filtered = $row->filter(function ($value) {
            return !is_null($value) && $value !== '' && $value !== ' ';
        });
        return $filtered->isEmpty();
    }

    /**
     * Detect the format of the Excel file based on headers
     */
    protected function detectFormat($headers)
    {
        $headers = array_map('strtolower', array_map('trim', $headers));
        
        // Check for ADM NO format (Grade 10, Form 3, Form 4)
        if (in_array('adm no', $headers) && in_array('name', $headers)) {
            return 'adm_format';
        }
        
        // Check for ADMISSION NUMBER format
        if (in_array('admission_number', $headers) || in_array('admission no', $headers)) {
            return 'standard';
        }
        
        // Check for other common formats
        if (in_array('student_id', $headers) || in_array('student id', $headers)) {
            return 'standard';
        }
        
        // Default to standard
        return 'standard';
    }

    /**
     * Map columns based on the detected format
     */
    protected function mapColumns($row, $format)
    {
        $mapped = [];
        $rowArray = [];
        
        foreach ($row as $key => $value) {
            $key = strtolower(trim($key));
            $value = is_string($value) ? trim($value) : $value;
            $rowArray[$key] = $value;
        }

        if ($format === 'adm_format') {
            // Format: ADM NO | NAME | GENDER | PARENT | PHONE NUMBER
            $mapped['admission_number'] = $rowArray['adm no'] ?? null;
            $mapped['full_name'] = $rowArray['name'] ?? null;
            $mapped['gender'] = $rowArray['gender'] ?? null;
            $mapped['parent_name'] = $rowArray['parent'] ?? null;
            $mapped['parent_phone'] = $rowArray['phone number'] ?? null;
            
            // Use class from constructor if provided
            if ($this->className) {
                $mapped['class'] = $this->className;
            }
        } else {
            // Standard format with column mapping
            $columnMapping = [
                'admission_number' => ['admission_number', 'admission no', 'adm no', 'student id', 'reg number', 'registration number', 'student_no'],
                'full_name' => ['full_name', 'full name', 'student name', 'name', 'candidate name', 'student_full_name'],
                'class' => ['class', 'form', 'grade', 'level', 'class_name', 'current class'],
                'date_of_birth' => ['date_of_birth', 'dob', 'birth date', 'birthday', 'date of birth', 'birthdate'],
                'gender' => ['gender', 'sex'],
                'admission_date' => ['admission_date', 'admission date', 'enrollment date', 'reg date', 'registration date'],
                'is_boarder' => ['is_boarder', 'boarding', 'boarder', 'hostel', 'boarding status', 'residence'],
                'profile_picture' => ['profile_picture', 'photo', 'picture', 'image'],
                'parent_phone' => ['parent_phone', 'parent phone', 'guardian phone', 'parent contact', 'parent mobile', 'phone number', 'phone', 'mother phone', 'father phone'],
                'parent_name' => ['parent_name', 'parent name', 'guardian name', 'parent/guardian', 'parent', 'mother name', 'father name'],
                'status' => ['status', 'student status', 'enrollment status', 'current status'],
            ];

            foreach ($columnMapping as $dbField => $excelHeaders) {
                foreach ($excelHeaders as $header) {
                    $header = strtolower($header);
                    if (isset($rowArray[$header]) && !is_null($rowArray[$header]) && $rowArray[$header] !== '') {
                        $mapped[$dbField] = $rowArray[$header];
                        break;
                    }
                }
            }
        }

        return $mapped;
    }

    /**
     * Validate a single row
     */
    protected function validateRow($data)
    {
        return Validator::make($data, [
            'admission_number' => 'nullable|string|max:50',
            'full_name' => 'nullable|string|max:255',
            'class' => 'nullable|string|max:20',
            'date_of_birth' => 'nullable|date',
            'admission_date' => 'nullable|date',
            'gender' => 'nullable|in:male,female,Male,Female,M,F',
            'is_boarder' => 'nullable|in:yes,no,true,false,1,0,y,n,boarder,day,day scholar',
            'parent_phone' => 'nullable|string|max:20',
            'parent_name' => 'nullable|string|max:255',
            'status' => 'nullable|in:active,inactive,graduated,Active,Inactive,Graduated',
        ]);
    }

    /**
     * Prepare student data for insert/update
     */
    protected function prepareStudentData($data)
    {
        // Set default values
        $studentData = [
            'status' => 'active',
            'is_boarder' => true,
            'admission_date' => now(),
        ];

        // Map each field
        foreach ($data as $field => $value) {
            if ($value === null || $value === '') {
                continue;
            }

            switch ($field) {
                case 'admission_number':
                case 'full_name':
                case 'class':
                case 'profile_picture':
                case 'parent_phone':
                case 'parent_name':
                    $studentData[$field] = trim($value);
                    break;

                case 'date_of_birth':
                    $studentData[$field] = $this->parseDate($value);
                    break;

                case 'admission_date':
                    $studentData[$field] = $this->parseDate($value) ?? now();
                    break;

                case 'gender':
                    $gender = strtolower(trim($value));
                    if (in_array($gender, ['male', 'female'])) {
                        $studentData[$field] = $gender;
                    } elseif ($gender == 'm') {
                        $studentData[$field] = 'male';
                    } elseif ($gender == 'f') {
                        $studentData[$field] = 'female';
                    }
                    break;

                case 'is_boarder':
                    $studentData[$field] = $this->parseBoolean($value);
                    break;

                case 'status':
                    $status = strtolower(trim($value));
                    $validStatuses = ['active', 'inactive', 'graduated'];
                    if (in_array($status, $validStatuses)) {
                        $studentData[$field] = $status;
                    } else {
                        $studentData[$field] = 'active';
                    }
                    break;
            }
        }

        // Generate admission number if not provided
        if (empty($studentData['admission_number'])) {
            $studentData['admission_number'] = $this->generateAdmissionNumber();
            $this->addWarning($this->rowNumber, 'Auto-generated admission number: ' . $studentData['admission_number']);
        }

        // Clean up parent name - remove prefixes
        if (!empty($studentData['parent_name'])) {
            $studentData['parent_name'] = $this->cleanParentName($studentData['parent_name']);
        }

        // If no class provided and no class name in constructor, add warning
        if (empty($studentData['class'])) {
            $studentData['class'] = 'Unassigned';
            $this->addWarning($this->rowNumber, 'No class specified, assigned to "Unassigned"');
        }

        return $studentData;
    }

    /**
     * Clean parent name - remove prefixes like Mr., Mrs., etc.
     */
    protected function cleanParentName($name)
    {
        $name = trim($name);
        
        // Remove common prefixes
        $prefixes = ['Mr.', 'Mr', 'Mrs.', 'Mrs', 'Miss.', 'Miss', 'Ms.', 'Ms', 'Dr.', 'Dr', 'Prof.', 'Prof'];
        foreach ($prefixes as $prefix) {
            if (strpos($name, $prefix) === 0) {
                $name = trim(substr($name, strlen($prefix)));
            }
        }
        
        return $name;
    }

    /**
     * Parse date from various formats
     */
    protected function parseDate($value)
    {
        if (empty($value)) {
            return null;
        }

        try {
            // Handle Excel serial date
            if (is_numeric($value)) {
                $unix = ($value - 25569) * 86400;
                return date('Y-m-d', $unix);
            }

            $value = trim($value);
            
            // Handle various string date formats
            $formats = [
                'Y-m-d',
                'd/m/Y',
                'm/d/Y',
                'd-m-Y',
                'm-d-Y',
                'Y/m/d',
                'd.m.Y',
                'm.d.Y',
                'd M Y',
                'M d Y',
                'd M, Y',
                'M d, Y',
            ];

            foreach ($formats as $format) {
                try {
                    $date = Carbon::createFromFormat($format, $value);
                    if ($date) {
                        return $date->format('Y-m-d');
                    }
                } catch (\Exception $e) {
                    continue;
                }
            }

            // Try standard parse
            return Carbon::parse($value)->format('Y-m-d');
            
        } catch (\Exception $e) {
            $this->addWarning($this->rowNumber, 'Invalid date format: ' . $value);
            return null;
        }
    }

    /**
     * Parse boolean from various formats
     */
    protected function parseBoolean($value)
    {
        if (empty($value)) {
            return false;
        }

        $value = strtolower(trim($value));
        
        // True values
        if (in_array($value, ['yes', 'true', '1', 'y', 'boarder', 'boarding', 'board', 'hostel', 'residential'])) {
            return true;
        }
        
        // False values
        if (in_array($value, ['no', 'false', '0', 'n', 'day', 'day scholar', 'day-scholar', 'day_scholar', 'non-boarder'])) {
            return false;
        }

        // Default to true for boarding if ambiguous
        return true;
    }

    /**
     * Generate a unique admission number
     */
    protected function generateAdmissionNumber()
    {
        $year = date('Y');
        $prefix = 'HS';
        
        $lastStudent = HighSchoolStudent::where('admission_number', 'LIKE', "{$prefix}/{$year}/%")
            ->orderBy('admission_number', 'desc')
            ->first();

        if ($lastStudent) {
            $parts = explode('/', $lastStudent->admission_number);
            $lastNumber = (int) end($parts);
            $newNumber = str_pad($lastNumber + 1, 5, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '00001';
        }

        return "{$prefix}/{$year}/{$newNumber}";
    }

    /**
     * Add an error message
     */
    protected function addError($rowNumber, $messages)
    {
        if (is_array($messages)) {
            $messages = implode(', ', $messages);
        }
        $this->errors[] = "Row {$rowNumber}: {$messages}";
    }

    /**
     * Add a warning message
     */
    protected function addWarning($rowNumber, $message)
    {
        $this->warnings[] = "Row {$rowNumber}: {$message}";
    }

    /**
     * Get import statistics
     */
    public function getImportStats()
    {
        return [
            'imported' => $this->importedCount,
            'updated' => $this->updatedCount,
            'errors' => $this->errorCount,
            'warnings' => $this->warningCount,
            'total_processed' => $this->importedCount + $this->updatedCount,
            'error_messages' => $this->errors,
            'warning_messages' => $this->warnings,
            'format_detected' => $this->format,
            'headers' => $this->headers,
        ];
    }

    /**
     * Get total imported count
     */
    public function getRowCount()
    {
        return $this->importedCount + $this->updatedCount;
    }

    /**
     * Get the chunk size
     */
    public function chunkSize(): int
    {
        return 100;
    }

    /**
     * Get the batch size
     */
    public function batchSize(): int
    {
        return 50;
    }

    /**
     * Get the heading row
     */
    public function headingRow(): int
    {
        return 1;
    }
}