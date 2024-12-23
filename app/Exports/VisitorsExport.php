<?php

namespace App\Exports;

use App\Models\Visitor;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadings;

class VisitorsExport implements FromCollection, WithHeadings, WithChunkReading
{
    use Exportable;

    protected $eventId;
    protected $paramsCollection;

    public function __construct($eventId, Collection $paramsCollection)
    {
        $this->eventId = $eventId;
        $this->paramsCollection = $paramsCollection;
    }

    public function collection()
    {
        $index = 1;

        $data = $this->fetchVisitorsInChunks();
        if(empty($data)){
            return collect([]);
        }

        return $data->map(function ($visitor) use (&$index) {
            $participatedEvents = $visitor->eventVisitors ?? [];

            $productNames = $this->getProductNames($participatedEvents);
            $numberOfAppointments = $this->getNumberOfAppointments($visitor);
            $numberOfAppointmentsCount = ($numberOfAppointments > 0) ? $numberOfAppointments : 'No Appointment';
            $city = $visitor->address->city ?? '';

            $source = $this->getSource($visitor);
            $createdAt = $this->getTimestamp($visitor);

            return [
                'Id' => $index++,
                'Name' => $visitor->name,
                'Mobile Number' => $visitor->mobile_number,
                'Email' => $visitor->email,
                'Nature of Business' => $visitor->category->name ?? '',
                'Organization' => $visitor->organization,
                'Designation' => $visitor->designation,
                'Reason for Visit' => $visitor->reason_for_visit,
                'Product Looking for' => $productNames,
                'Source' => $source,
                'Known Source' => $visitor->known_source,
                'City' => $city,
                'Timestamp' => $createdAt,
                'No of Appointments' => $numberOfAppointmentsCount,
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
            'Nature of Business',
            'Organization',
            'Designation',
            'Reason for Visit',
            'Product Looking for',
            'Source',
            'Known Source',
            'City',
            'Timestamp',
            'No of Appointments',
        ];
    }

    public function chunkSize(): int
    {
        return 1000;
    }

    private function getProductNames($participatedEvents)
    {
        $productNames = '';
        foreach ($participatedEvents as $participatedEvent) {
            $productNames .= $participatedEvent->getProductNames();
        }
        return $productNames;
    }

    private function fetchVisitorsInChunks()
    {
        $visitors = collect();

        $startDate = $this->paramsCollection['startDate'] ?? null;
        $endDate = $this->paramsCollection['endDate'] ?? null;
        $visitorRegId = $this->paramsCollection['visitorRegId'] ?? null;
        $participateStatus = $this->paramsCollection['participateStatus'] ?? null;
        $search = $this->paramsCollection['search'] ?? null;
        $sortBy = $this->paramsCollection['sortBy'] ?? null;

        $query = Visitor::with(['eventVisitors', 'appointments', 'address', 'category'])
            ->when($this->eventId, function ($query) {
                $query->whereHas('eventVisitors', function ($query) {
                    $query->where('event_id', $this->eventId);
                });
            });
        if ($startDate) {
            $query->whereDate('created_at', '>=', Carbon::parse($startDate)->startOfDay());
        }

        if ($endDate) {
            $query->whereDate('created_at', '<=', Carbon::parse($endDate)->endOfDay());
        }

        $query->when($this->eventId, function ($query) use ($participateStatus) {
            $query->whereHas('eventVisitors', function ($query) use ($participateStatus) {
                $query->where('event_id', $this->eventId)
                    ->where('is_delegates', '<>', 1)
                    ->when($participateStatus == 'visited', function ($query) {
                        $query->where('is_visited', 1);
                    })
                    ->when($participateStatus == 'not_visited', function ($query) {
                        $query->where('is_visited', 0);
                    });
            });
        })
            ->when($visitorRegId, function ($query) use ($visitorRegId) {
                $query->whereHas('eventVisitors', function ($subquery) use ($visitorRegId) {
                    $subquery->where('_meta->reference_no', 'like', '%' . trim($visitorRegId) . '%');
                });
            })
            ->when(trim($search), function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $trimmedSearch = trim($search);
                    $query->where('name', 'like', '%' . $trimmedSearch . '%')
                        ->orWhere('mobile_number', 'like', '%' . $trimmedSearch . '%')
                        ->orWhere('email', 'like', '%' . $trimmedSearch . '%')
                        ->orWhere('organization', 'like', '%' . $trimmedSearch . '%')
                        ->orWhere('designation', 'like', '%' . $trimmedSearch . '%');
                });
            })
            ->when($sortBy, function ($query) use ($sortBy) {
                if ($sortBy === 'appointments_count') {
                    $query->withCount(['appointments' => function ($query) {
                        if ($this->eventId) {
                            $query->where('event_id', $this->eventId);
                        }
                    }])
                        ->orderBy('appointments_count', 'desc');
                } elseif ($sortBy === 'event_visitors_count') {
                    $query->withCount(['eventVisitors' => function ($query) {
                        $query->where('is_visited', 1);
                    }])
                        ->orderBy('event_visitors_count', 'desc');
                }

            });
        $query->chunk(5000, function ($chunk) use ($visitors) {
            Log::info("Chunk size: ");
            Log::info(count($chunk));
            $visitors->push(...$chunk);
        });

        return $visitors;
    }

    private function getNumberOfAppointments($visitor)
    {
        return $this->eventId ? $visitor->appointments->where('event_id', $this->eventId)->count() : $visitor->appointments->count();
    }

    private function getSource($visitor)
    {
        if ($this->eventId) {
            $currentEventVisitor = $visitor->eventVisitors->where('event_id', $this->eventId)->first();
            if ($currentEventVisitor) {
                return $currentEventVisitor->registration_type;
            }
        }
        return $visitor->registration_type;
    }

    private function getTimestamp($visitor)
    {
        if ($this->eventId) {
            $currentEventVisitor = $visitor->eventVisitors->where('event_id', $this->eventId)->first();
            if ($currentEventVisitor) {
                return $currentEventVisitor->created_at;
            }
        }
        return $visitor->created_at;
    }
}
