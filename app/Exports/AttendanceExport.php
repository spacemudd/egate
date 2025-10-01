<?php

namespace App\Exports;

use App\Models\AttendanceRecord;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AttendanceExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths
{
    protected $records;

    public function __construct($records)
    {
        $this->records = $records;
    }

    public function collection()
    {
        return $this->records;
    }

    public function headings(): array
    {
        return [
            'User ID',
            'Name',
            'Employee ID',
            'Department',
            'Device',
            'Punch Time',
            'Punch Type',
            'Verify Mode',
            'Status',
            'Work Code',
            'Notes'
        ];
    }

    public function map($record): array
    {
        return [
            $record->device_user_id,
            $record->biometricUser->name ?? 'Unknown',
            $record->biometricUser->employee_id ?? '',
            $record->biometricUser->department ?? '',
            $record->device->device_name ?? $record->device->serial_number,
            $record->punch_time->format('Y-m-d H:i:s'),
            $record->punch_type_label,
            $record->verify_mode_label,
            $record->status ?? '',
            $record->work_code ?? '',
            $record->notes ?? '',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 10, // User ID
            'B' => 20, // Name
            'C' => 15, // Employee ID
            'D' => 15, // Department
            'E' => 20, // Device
            'F' => 20, // Punch Time
            'G' => 15, // Punch Type
            'H' => 15, // Verify Mode
            'I' => 10, // Status
            'J' => 15, // Work Code
            'K' => 30, // Notes
        ];
    }
}
