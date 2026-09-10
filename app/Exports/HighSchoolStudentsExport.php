<?php

namespace App\Exports;

use App\Models\HighSchoolStudent;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class HighSchoolStudentsExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
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

        if (isset($this->filters['class'])) {
            $query->where('class', $this->filters['class']);
        }

        if (isset($this->filters['gender'])) {
            $query->where('gender', $this->filters['gender']);
        }

        if (isset($this->filters['is_boarder'])) {
            $query->where('is_boarder', $this->filters['is_boarder']);
        }

        return $query->orderBy('class')->orderBy('full_name')->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Admission Number',
            'Full Name',
            'Class',
            'Date of Birth',
            'Gender',
            'Admission Date',
            'Boarder',
            'Parent Phone',
            'Parent Name',
            'Status',
            'Created At',
            'Updated At',
            'Has Card',
            'Card Number',
            'Card Balance'
        ];
    }

    public function map($student): array
    {
        return [
            $student->id,
            $student->admission_number,
            $student->full_name,
            $student->class,
            $student->date_of_birth?->format('Y-m-d') ?? 'N/A',
            $student->gender ?? 'N/A',
            $student->admission_date?->format('Y-m-d') ?? 'N/A',
            $student->is_boarder ? 'Yes' : 'No',
            $student->parent_phone ?? 'N/A',
            $student->parent_name ?? 'N/A',
            $student->status,
            $student->created_at?->format('Y-m-d H:i:s') ?? 'N/A',
            $student->updated_at?->format('Y-m-d H:i:s') ?? 'N/A',
            $student->cardAccount ? 'Yes' : 'No',
            $student->cardAccount?->card_number ?? 'N/A',
            $student->cardAccount?->balance ?? 0,
        ];
    }
}