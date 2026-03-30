<?php

namespace App\Imports;

use App\Models\Student;
use App\Models\Campus;
use App\Models\Application;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Carbon\Carbon;

class StudentsImport implements ToCollection, WithHeadingRow, WithChunkReading, WithBatchInserts
{
    protected $batchName;
    protected $campusId;
    protected $importedCount = 0;
    protected $errorCount = 0;
    protected $warningCount = 0;
    protected $errors = [];
    protected $warnings = [];

    // Map Excel columns to database fields
    protected $columnMapping = [
        // Student Identification
        'student_number' => ['student_number', 'student no', 'student no.', 'student id', 'admission no'],
        'legacy_student_code' => ['legacy_code', 'legacy student code', 'old code', 'shep code', 'excel code'],
        'legacy_code' => ['alternative code', 'alt code', 'other code'],

        // Personal Information
        'title' => ['title', 'salutation'],
        'first_name' => ['first name', 'fname', 'given name'],
        'last_name' => ['last name', 'lname', 'surname', 'family name'],
        'middle_name' => ['middle name', 'mname', 'other name'],
        'email' => ['email', 'e-mail', 'email address'],
        'phone' => ['phone', 'telephone', 'mobile', 'phone number', 'contact'],
        'id_number' => ['id number', 'national id', 'id no', 'identity number', 'ktn'],
        'id_type' => ['id type', 'identity type', 'document type'],
        'date_of_birth' => ['date of birth', 'dob', 'birth date', 'birthday'],
        'gender' => ['gender', 'sex'],
        'marital_status' => ['marital status', 'marriage status'],

        // Contact Information
        'address' => ['address', 'physical address', 'postal address'],
        'city' => ['city', 'town'],
        'county' => ['county', 'district', 'constituency'],
        'postal_code' => ['postal code', 'post code', 'zip code'],
        'country' => ['country'],

        // Next of Kin
        'next_of_kin_name' => ['next of kin name', 'nok name', 'kin name'],
        'next_of_kin_phone' => ['next of kin phone', 'nok phone', 'kin phone'],
        'next_of_kin_relationship' => ['next of kin relationship', 'nok relationship', 'kin relationship'],
        'next_of_kin_address' => ['next of kin address', 'nok address', 'kin address'],
        'next_of_kin_email' => ['next of kin email', 'nok email', 'kin email'],
        'next_of_kin_id_number' => ['next of kin id', 'nok id', 'kin id'],

        // Emergency Contact
        'emergency_contact_name' => ['emergency contact name', 'emergency name'],
        'emergency_contact_phone' => ['emergency contact phone', 'emergency phone'],
        'emergency_contact_relationship' => ['emergency contact relationship', 'emergency relationship'],
        'emergency_contact_phone_alt' => ['emergency contact alt phone', 'emergency phone 2'],

        // Education Background
        'education_level' => ['education level', 'highest education', 'qualification'],
        'school_name' => ['school name', 'institution', 'college', 'university'],
        'graduation_year' => ['graduation year', 'year graduated', 'year of graduation'],
        'mean_grade' => ['mean grade', 'grade', 'average grade'],
        'kcse_index_number' => ['kcse index', 'index number', 'kcse no'],

        // Medical & Special Needs
        'medical_conditions' => ['medical conditions', 'health conditions', 'medical'],
        'allergies' => ['allergies', 'allergic'],
        'blood_group' => ['blood group', 'blood type'],
        'special_needs' => ['special needs', 'disability', 'special requirements'],
        'disability_type' => ['disability type', 'disability'],

        // Additional Info
        'tshirt_size' => ['tshirt size', 't-shirt size', 'shirt size'],
        'remarks' => ['remarks', 'notes', 'comments'],
        'student_category' => ['student category', 'category', 'student type'],

        // Status
        'status' => ['status', 'student status'],
        'registration_date' => ['registration date', 'reg date', 'enrollment date'],
    ];

    public function __construct($batchName = null, $campusId = null)
    {
        $this->batchName = $batchName ?? 'IMPORT_' . now()->format('Ymd_His');
        $this->campusId = $campusId;
    }

    /**
     * @param Collection $rows
     */
    public function collection(Collection $rows)
    {
        DB::beginTransaction();

        try {
            foreach ($rows as $index => $row) {
                $rowNumber = $index + 2; // +2 because heading row is row 1, and array is 0-indexed

                // Skip empty rows
                if ($row->filter()->isEmpty()) {
                    continue;
                }

                // Map Excel columns to database fields
                $mappedData = $this->mapColumns($row);

                // Skip if no first name or last name
                if (empty($mappedData['first_name']) && empty($mappedData['last_name'])) {
                    $this->addWarning($rowNumber, 'Skipped: No name provided');
                    $this->warningCount++;
                    continue;
                }

                // Validate the row data
                $validator = $this->validateRow($mappedData, $rowNumber);

                if ($validator->fails()) {
                    $this->addError($rowNumber, $validator->errors()->all());
                    $this->errorCount++;
                    continue;
                }

                // Prepare data for insertion
                $studentData = $this->prepareStudentData($mappedData);

                // Check for existing student by ID number or student number
                $existingStudent = null;
                if (!empty($studentData['id_number'])) {
                    $existingStudent = Student::where('id_number', $studentData['id_number'])->first();
                }

                if (!$existingStudent && !empty($studentData['student_number'])) {
                    $existingStudent = Student::where('student_number', $studentData['student_number'])->first();
                }

                if (!$existingStudent && !empty($studentData['legacy_student_code'])) {
                    $existingStudent = Student::where('legacy_student_code', $studentData['legacy_student_code'])->first();
                }

                if ($existingStudent) {
                    // Update existing student
                    $existingStudent->update($studentData);
                    $this->importedCount++;
                } else {
                    // Create new student
                    Student::create($studentData);
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
     * Map Excel columns to database fields
     */
    protected function mapColumns($row)
    {
        $mapped = [];

        // Convert row to array with lowercase keys
        $rowArray = [];
        foreach ($row as $key => $value) {
            $rowArray[strtolower(trim($key))] = $value;
        }

        foreach ($this->columnMapping as $dbField => $excelHeaders) {
            foreach ($excelHeaders as $header) {
                $header = strtolower($header);
                if (isset($rowArray[$header]) && !is_null($rowArray[$header]) && $rowArray[$header] !== '') {
                    $mapped[$dbField] = $rowArray[$header];
                    break;
                }
            }
        }

        return $mapped;
    }

    /**
     * Validate a single row
     */
    protected function validateRow($data, $rowNumber)
    {
        return Validator::make($data, [
            'first_name' => 'nullable|string|max:100',
            'last_name' => 'nullable|string|max:100',
            'middle_name' => 'nullable|string|max:100',
            'email' => 'nullable|email|max:150',
            'phone' => 'nullable|string|max:20',
            'id_number' => 'nullable|string|max:30',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|in:male,female,other,Male,Female,Other,M,F',
            'graduation_year' => 'nullable|integer|min:1950|max:' . date('Y'),
            'status' => 'nullable|in:active,inactive,graduated,dropped,suspended,alumnus,prospective,historical,Active,Inactive,Graduated',
            'student_category' => 'nullable|in:regular,alumnus,staff_child,sponsored,scholarship,Regular,Alumnus',
        ]);
    }

    /**
     * Prepare student data for insert/update
     */
    protected function prepareStudentData($data)
    {
        // Set default values
        $studentData = [
            'campus_id' => $this->campusId,
            'import_batch' => $this->batchName,
            'registration_type' => 'excel_import',
            'requires_cleanup' => false,
            'status' => 'historical', // Default for imports
            'student_category' => 'regular', // Default category
            'country' => 'Kenya', // Default country
        ];

        // Map each field
        foreach ($data as $field => $value) {
            if ($value === null || $value === '') {
                continue;
            }

            switch ($field) {
                case 'first_name':
                case 'last_name':
                case 'middle_name':
                case 'title':
                case 'email':
                case 'phone':
                case 'id_number':
                case 'address':
                case 'city':
                case 'county':
                case 'postal_code':
                case 'next_of_kin_name':
                case 'next_of_kin_phone':
                case 'next_of_kin_relationship':
                case 'next_of_kin_address':
                case 'next_of_kin_email':
                case 'next_of_kin_id_number':
                case 'emergency_contact_name':
                case 'emergency_contact_phone':
                case 'emergency_contact_relationship':
                case 'emergency_contact_phone_alt':
                case 'education_level':
                case 'school_name':
                case 'mean_grade':
                case 'kcse_index_number':
                case 'medical_conditions':
                case 'allergies':
                case 'blood_group':
                case 'special_needs':
                case 'disability_type':
                case 'tshirt_size':
                case 'remarks':
                case 'student_number':
                case 'legacy_student_code':
                case 'legacy_code':
                    $studentData[$field] = trim($value);
                    break;

                case 'date_of_birth':
                    $studentData[$field] = $this->parseDate($value);
                    break;

                case 'registration_date':
                    $studentData[$field] = $this->parseDate($value);
                    break;

                case 'graduation_year':
                    $studentData[$field] = is_numeric($value) ? (int) $value : null;
                    break;

                case 'gender':
                    $gender = strtolower(trim($value));
                    if (in_array($gender, ['male', 'female', 'other'])) {
                        $studentData[$field] = $gender;
                    } elseif ($gender == 'm') {
                        $studentData[$field] = 'male';
                    } elseif ($gender == 'f') {
                        $studentData[$field] = 'female';
                    }
                    break;

                case 'id_type':
                    $idType = strtolower(trim($value));
                    if (in_array($idType, ['id', 'birth_certificate', 'passport'])) {
                        $studentData[$field] = $idType;
                    } elseif (str_contains($idType, 'birth')) {
                        $studentData[$field] = 'birth_certificate';
                    } elseif (str_contains($idType, 'passport')) {
                        $studentData[$field] = 'passport';
                    } else {
                        $studentData[$field] = 'id';
                    }
                    break;

                case 'marital_status':
                    $status = strtolower(trim($value));
                    $validStatuses = ['single', 'married', 'divorced', 'widowed', 'separated'];
                    if (in_array($status, $validStatuses)) {
                        $studentData[$field] = $status;
                    }
                    break;

                case 'student_category':
                    $category = strtolower(trim(str_replace(' ', '_', $value)));
                    if (in_array($category, ['regular', 'alumnus', 'staff_child', 'sponsored', 'scholarship'])) {
                        $studentData[$field] = $category;
                    }
                    break;

                case 'status':
                    $status = strtolower(trim($value));
                    $validStatuses = ['active', 'inactive', 'graduated', 'dropped', 'suspended', 'alumnus', 'prospective', 'historical'];
                    if (in_array($status, $validStatuses)) {
                        $studentData[$field] = $status;
                    }
                    break;

                case 'country':
                    $studentData[$field] = $value ?: 'Kenya';
                    break;
            }
        }

        // Check for missing required fields and flag for cleanup
        if (empty($studentData['first_name']) || empty($studentData['last_name'])) {
            $studentData['requires_cleanup'] = true;
            $studentData['import_notes'] = 'Missing first name or last name';
        }

        if (empty($studentData['id_number']) && empty($studentData['student_number']) && empty($studentData['legacy_student_code'])) {
            $studentData['requires_cleanup'] = true;
            $studentData['import_notes'] = ($studentData['import_notes'] ?? '') . '; No identification provided';
        }

        // Generate student number if not provided
        if (empty($studentData['student_number'])) {
            $studentData['student_number'] = $this->generateStudentNumber();
            $studentData['requires_cleanup'] = true;
            $studentData['import_notes'] = ($studentData['import_notes'] ?? '') . '; Auto-generated student number';
        }

        return $studentData;
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
                return Carbon::createFromFormat('Y-m-d', '1900-01-01')
                    ->addDays((int) $value - 2)
                    ->format('Y-m-d');
            }

            // Handle string dates
            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Generate a unique student number
     */
    protected function generateStudentNumber()
    {
        $prefix = 'IMP';
        $year = date('Y');
        $month = date('m');

        $lastStudent = Student::where('student_number', 'LIKE', "{$prefix}/{$year}/{$month}/%")
            ->orderBy('student_number', 'desc')
            ->first();

        if ($lastStudent) {
            $parts = explode('/', $lastStudent->student_number);
            $lastNumber = (int) end($parts);
            $newNumber = str_pad($lastNumber + 1, 5, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '00001';
        }

        return "{$prefix}/{$year}/{$month}/{$newNumber}";
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
            'errors' => $this->errorCount,
            'warnings' => $this->warningCount,
            'error_messages' => $this->errors,
            'warning_messages' => $this->warnings,
            'batch' => $this->batchName,
        ];
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
