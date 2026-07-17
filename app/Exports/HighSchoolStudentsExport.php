<?php
// app/Exports/HighSchoolStudentsExport.php

namespace App\Exports;

use App\Models\HighSchoolStudent;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class HighSchoolStudentsExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $filters;

    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = HighSchoolStudent::with(['cardAccount']);

        if (isset($this->filters['status'])) {
            $query->where('status', $this->filters['status']);
        }

        if (isset($this->filters['has_card'])) {
            if ($this->filters['has_card'] === 'yes') {
                $query->has('cardAccount');
            } else {
                $query->doesntHave('cardAccount');
            }
        }

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'Admission Number',
            'First Name',
            'Last Name',
            'Full Name',
            'Email',
            'Phone',
            'Date of Birth',
            'Gender',
            'Class',
            'Guardian Name',
            'Guardian Phone',
            'Guardian Email',
            'Address',
            'Status',
            'Has Card',
            'Card Number',
            'Card Balance',
            'Created At'
        ];
    }

    public function map($student): array
    {
        return [
            $student->admission_number,
            $student->first_name,
            $student->last_name,
            $student->full_name,
            $student->email,
            $student->phone,
            $student->date_of_birth,
            $student->gender,
            $student->class,
            $student->guardian_name,
            $student->guardian_phone,
            $student->guardian_email,
            $student->address,
            $student->status,
            $student->cardAccount ? 'Yes' : 'No',
            $student->cardAccount?->card_number ?? 'N/A',
            $student->cardAccount?->balance ?? 0,
            $student->created_at?->format('Y-m-d H:i:s'),
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']]],
            'A1:R1' => ['fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'B91C1C']]],
        ];
    }
}
