<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class DelegatesExport implements FromCollection, WithHeadings
{
    /**
     * @return \Illuminate\Support\Collection
     */
    use Exportable;

    protected $delegates;
    protected $eventId;
    public function __construct($eventId, Collection $delegates)
    {
        $this->eventId = $eventId;
        $this->delegates = $delegates;
    }
    public function collection()
    {
        $index = 1;
        return $this->delegates->map(function ($visitor) use (&$index) {

            $seminars = [];
            $eventDelegates = $visitor->eventDelegates?->where('event_id', $this->eventId);
            foreach ($eventDelegates as $eventDelegate) {
                $seminars[] = $eventDelegate->seminar?->title;
            }
            $seminarNames = implode(', ', $seminars);

            return [
                'Id' => $index++,
                'Name' => $visitor->name,
                'Mobile Number' => $visitor->mobile_number,
                'Email' => $visitor->email,
                'Organization' => $visitor->organization,
                'Designation' => $visitor->designation,
                'Seminar to Attend' => $seminarNames,

            ];
        });
    }

    public function headings(): array
    {
        return [
            'S.No.',
            'Name',
            'Mobile Number',
            'Email',
            'Organization',
            'Designation',
            'Seminar to Attend',

        ];
    }
}
