<?php

namespace App\Exports;

use App\Models\Student;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Carbon\Carbon;

class StudentsExport implements FromQuery, WithHeadings, WithMapping, WithTitle, ShouldAutoSize, WithStyles, WithEvents
{
    protected $request;
    protected $user;

    public function __construct($request)
    {
        $this->request = $request;
        $this->user = auth()->user();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query()
    {
        $query = Student::query()
            ->with(['campus', 'application.course']);

        // Apply filters
        if ($this->request->filled('status')) {
            $query->where('status', $this->request->status);
        }

        if ($this->request->filled('campus_id') && $this->user->role == 2) {
            $query->where('campus_id', $this->request->campus_id);
        }

        if ($this->request->filled('gender')) {
            $query->where('gender', $this->request->gender);
        }

        if ($this->request->filled('student_category')) {
            $query->where('student_category', $this->request->student_category);
        }

        if ($this->request->filled('registration_date_from')) {
            $query->whereDate('registration_date', '>=', $this->request->registration_date_from);
        }

        if ($this->request->filled('registration_date_to')) {
            $query->whereDate('registration_date', '<=', $this->request->registration_date_to);
        }

        // Campus restriction for non-admin users
        if ($this->user->role != 2) {
            $query->where('campus_id', $this->user->campus_id);
        }

        return $query->orderBy('created_at', 'desc');
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'Student Number',
            'Legacy Code',
            'Title',
            'First Name',
            'Last Name',
            'Middle Name',
            'Email',
            'Phone',
            'ID Type',
            'ID Number',
            'Date of Birth',
            'Gender',
            'Marital Status',
            'Campus',
            'Address',
            'City',
            'County',
            'Postal Code',
            'Country',
            'Next of Kin Name',
            'Next of Kin Phone',
            'Next of Kin Relationship',
            'Next of Kin Address',
            'Next of Kin Email',
            'Next of Kin ID',
            'Emergency Contact',
            'Emergency Phone',
            'Emergency Relationship',
            'Emergency Phone Alt',
            'Education Level',
            'School Name',
            'Graduation Year',
            'Mean Grade',
            'KCSE Index',
            'Medical Conditions',
            'Allergies',
            'Blood Group',
            'Special Needs',
            'Disability Type',
            'T-Shirt Size',
            'Student Category',
            'Status',
            'Registration Date',
            'Registration Type',
            'Import Batch',
            'Requires Cleanup',
            'Created At',
            'Updated At',
        ];
    }

    /**
     * @param mixed $student
     * @return array
     */
    public function map($student): array
    {
        return [
            $student->student_number,
            $student->legacy_student_code,
            $student->title,
            $student->first_name,
            $student->last_name,
            $student->middle_name,
            $student->email,
            $student->phone,
            ucfirst(str_replace('_', ' ', $student->id_type ?? 'id')),
            $student->id_number,
            $student->date_of_birth ? Carbon::parse($student->date_of_birth)->format('Y-m-d') : null,
            ucfirst($student->gender ?? ''),
            $student->marital_status,
            $student->campus->name ?? '',
            $student->address,
            $student->city,
            $student->county,
            $student->postal_code,
            $student->country,
            $student->next_of_kin_name,
            $student->next_of_kin_phone,
            $student->next_of_kin_relationship,
            $student->next_of_kin_address,
            $student->next_of_kin_email,
            $student->next_of_kin_id_number,
            $student->emergency_contact_name,
            $student->emergency_contact_phone,
            $student->emergency_contact_relationship,
            $student->emergency_contact_phone_alt,
            $student->education_level,
            $student->school_name,
            $student->graduation_year,
            $student->mean_grade,
            $student->kcse_index_number,
            $student->medical_conditions,
            $student->allergies,
            $student->blood_group,
            $student->special_needs,
            $student->disability_type,
            $student->tshirt_size,
            ucfirst(str_replace('_', ' ', $student->student_category)),
            ucfirst($student->status),
            $student->registration_date ? Carbon::parse($student->registration_date)->format('Y-m-d') : null,
            ucfirst(str_replace('_', ' ', $student->registration_type)),
            $student->import_batch,
            $student->requires_cleanup ? 'Yes' : 'No',
            $student->created_at ? $student->created_at->format('Y-m-d H:i:s') : null,
            $student->updated_at ? $student->updated_at->format('Y-m-d H:i:s') : null,
        ];
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return 'Students Export ' . Carbon::now()->format('Y-m-d');
    }

    /**
     * @param Worksheet $sheet
     */
    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as bold
            1 => ['font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '1E40AF']]],
        ];
    }

    /**
     * @return array
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                // Set auto filter
                $event->sheet->getDelegate()->setAutoFilter(
                    $event->sheet->getDelegate()->calculateWorksheetDimension()
                );

                // Freeze header row
                $event->sheet->getDelegate()->freezePane('A2');

                // Wrap text for header
                $event->sheet->getDelegate()->getStyle('1')->getAlignment()->setWrapText(true);
            },
        ];
    }
}
