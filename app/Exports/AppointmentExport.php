<?php

namespace App\Exports;

use App\Models\Appointment;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class AppointmentExport implements FromCollection, WithMapping, WithHeadings, WithChunkReading
{
    /**
     * @return \Illuminate\Support\Collection
     */

    use Exportable;


    protected $selectedRecordIds, $orderByName = 'scheduled_at', $orderBy = 'asc';

    private $serialCount = 1;

    public function __construct($selectedIds, $sortName, $sortBy)
    {

        $this->selectedRecordIds = $selectedIds;
        $this->orderByName = $sortName;
        $this->orderBy = $sortBy;
    }

    public function map($appointment): array
    {

        return [
            $this->serialCount++,
            $appointment->eventVisitorInfo ? $appointment->eventVisitorInfo->getProductNames() ?? 'No products' : '',
            $appointment->visitor->name ?? '',
            $appointment->visitor->designation ?? '',
            $appointment->visitor->organization ?? '',
            $appointment->exhibitor->name ?? '',
            $appointment->scheduled_at->isoFormat('llll') ?? '',
            ucwords($appointment->status) ?? '',
            $appointment->created_at->isoFormat('llll') ?? '',
        ];
    }

    public function headings(): array
    {
        return [
            '#',
            'Products',
            'Visitor Name',
            'Visitor Designation',
            'Visitor Organization',
            'Exhibitor Name',
            'Scheduled Datetime',
            'Status',
            'Created At',
        ];
    }

    public function collection()
    {
        $appointments = collect();

        $query = Appointment::with(['visitor', 'exhibitor', 'eventVisitorInfo'])->whereIn('appointments.id', $this->selectedRecordIds)
            ->when($this->orderByName, function ($sort) {
                if ($this->orderByName == 'visitor') {
                    $sort->join('visitors', 'appointments.visitor_id', '=', 'visitors.id')
                        ->select('appointments.*')->orderBy('visitors.name', $this->orderBy);
                } else if ($this->orderByName == 'designation') {
                    $sort->join('visitors', 'appointments.visitor_id', '=', 'visitors.id')
                        ->select('appointments.*')->orderBy('visitors.designation', $this->orderBy);
                } else if ($this->orderByName == 'exhibitor') {
                    $sort->join('exhibitors', 'appointments.exhibitor_id', '=', 'exhibitors.id')
                        ->select('appointments.*')->orderBy('exhibitors.name', $this->orderBy);
                } else {
                    $sort->orderBy($this->orderByName, $this->orderBy);
                }
            });

        $query->chunk(1000, function ($chunk) use ($appointments) {
            $appointments->push(...$chunk);
        });

        return $appointments;
    }

    public function chunkSize(): int
    {
        return 1000;
    }
}
