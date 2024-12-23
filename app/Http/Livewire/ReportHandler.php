<?php

namespace App\Http\Livewire;

use App\Models\Address;
use App\Models\EventVisitor;
use App\Models\Visitor;
use Carbon\Carbon;
use Livewire\Component;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\WithPagination;
use DB;
use Illuminate\Http\Request;

class ReportHandler extends Component
{
    use WithPagination;

    public $currentEventId;
    public $currentDate;
    public $startDate;
    public $endDate;
    public function mount(Request $request)
    {
        $this->currentDate = Carbon::today();
        $this->currentEventId = $request->eventId;
    }

    public function dateRangeFilter($startDate, $endDate)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    private function getTopLocationDetails($locationType)
    {
        $topLocations = Address::select($locationType, DB::raw('count(*) as total'))
            ->where('addresses.addressable_type', 'App\Models\Visitor')
            ->whereNotNull($locationType)
            ->where($locationType, '<>', '')
            ->join('event_visitors', 'addresses.addressable_id', '=', 'event_visitors.visitor_id')
            ->where('event_visitors.event_id', '=', $this->currentEventId)
            ->when($this->startDate, function ($query) {
                $query->whereDate('event_visitors.created_at', '>=', $this->startDate);
            })
            ->when($this->endDate, function ($query) {
                $query->whereDate('event_visitors.created_at', '<=', $this->endDate);
            })
            ->groupBy($locationType)
            ->orderBy('total', 'desc')
            ->take(5)
            ->get();

        foreach ($topLocations as $location) {
            $today_count = Address::where('addressable_type', 'App\Models\Visitor')
                ->where($locationType, $location->$locationType)
                ->join('event_visitors', 'addresses.addressable_id', '=', 'event_visitors.visitor_id')
                ->where('event_visitors.event_id', '=', $this->currentEventId)
                ->whereDate('event_visitors.created_at', $this->currentDate)
                ->count();
            $location->today_count = $today_count;
        }

        return [
            'top5Locations' => $topLocations,
            'overAllCount' => $topLocations->sum('total'),
            'overAllTodayCount' => $topLocations->sum('today_count'),
        ];
    }

    private function getLastSevenDaysVisitorsCount()
    {
        $lastSevenDays = [];
        $totalCount = 0;

        for ($i = 0; $i < 7; $i++) {
            $date = Carbon::today()->subDays($i);
            $visitorCount = EventVisitor::where('event_id', $this->currentEventId)->whereDate('created_at', $date)->count();

            $lastSevenDays[] = [
                'date' => $date->format('d-m-Y'),
                'count' => $visitorCount,
            ];

            $totalCount += $visitorCount;
        }

        return [
            'lastSevenDays' => $lastSevenDays,
            'total' => $totalCount,
        ];
    }


    private function getBusinessTypeDetails()
    {
        $businessTypes = Visitor::select('category_id', DB::raw('count(*) as total'))
            ->whereNotNull('category_id')
            ->where('category_id', '<>', '')
            ->whereHas('eventVisitors', function ($query) {
                $query->where('event_id', $this->currentEventId)
                    ->when($this->startDate, function ($query) {
                        $query->whereDate('created_at', '>=', $this->startDate);
                    })
                    ->when($this->endDate, function ($query) {
                        $query->whereDate('created_at', '<=', $this->endDate);
                    });
            })
            ->whereHas('category', function ($query) {
                $query->where('name', '<>', '')
                    ->whereNotNull('name');
            })
            ->groupBy('category_id')
            ->orderBy('total', 'desc')
            ->take(5)
            ->get();

        foreach ($businessTypes as $businessType) {
            $today_count = Visitor::where('category_id', $businessType->category_id)
                ->whereHas('eventVisitors', function ($query) {
                    $query->where('event_id', $this->currentEventId)
                        ->whereDate('created_at', $this->currentDate);
                })
                ->count();
            $businessType->today_count = $today_count;
            $businessType->name = $businessType->category?->name;
        }

        return [
            'businessTypes' => $businessTypes,
            'overAllCount' => $businessTypes->sum('total'),
            'overAllTodayCount' => $businessTypes->sum('today_count'),
        ];
    }

    private function getKnownSourceDetails()
    {
        $knownSources = EventVisitor::select('known_source', DB::raw('count(*) as total'))
            ->whereNotNull('known_source')
            ->where('known_source', '<>', '')
            ->where('event_id', $this->currentEventId)
            ->when($this->startDate, function ($query) {
                $query->whereDate('created_at', '>=', $this->startDate);
            })
            ->when($this->endDate, function ($query) {
                $query->whereDate('created_at', '<=', $this->endDate);
            })
            ->groupBy('known_source')
            ->orderBy('total', 'desc')
            ->take(5)
            ->get();

        foreach ($knownSources as $knownSource) {
            $today_count = EventVisitor::where('known_source', $knownSource->known_source)
                ->where('event_id', $this->currentEventId)
                ->whereDate('created_at', $this->currentDate)
                ->count();
            $knownSource->today_count = $today_count;
        }

        return [
            'knownSources' => $knownSources,
            'overAllCount' => $knownSources->sum('total'),
            'overAllTodayCount' => $knownSources->sum('today_count'),
        ];
    }

    private function getRegistrationTypewiseCounts()
    {
        $registrationTypes = EventVisitor::select('registration_type', DB::raw('count(*) as total'))
            ->whereNotNull('registration_type')
            ->where('registration_type', '<>', '')
            ->where('event_id', $this->currentEventId)
            ->when($this->startDate, function ($query) {
                $query->whereDate('created_at', '>=', $this->startDate);
            })
            ->when($this->endDate, function ($query) {
                $query->whereDate('created_at', '<=', $this->endDate);
            })
            ->groupBy('registration_type')
            ->orderBy('total', 'desc')
            ->get();

        $combinedCounts = [];
        $todayCounts = [];

        foreach ($registrationTypes as $type) {

            $todayCount = EventVisitor::where('registration_type', $type->registration_type)
                ->where('event_id', $this->currentEventId)
                ->whereDate('created_at', $this->currentDate)
                ->count();

            $label = $type->registration_type;
            if (in_array($type->registration_type, ['medicall', 'online'])) {
                $label = 'web';
            } else if ($type->registration_type == 'web') {
                $label = 'crm';
            } else {
                $label = $type->registration_type;
            }

            if (!isset($combinedCounts[$label])) {
                $combinedCounts[$label] = 0;
                $todayCounts[$label] = 0;
            }

            $combinedCounts[$label] += $type->total;
            $todayCounts[$label] += $todayCount;
        }

        $formattedData = collect($combinedCounts)->map(function ($value, $key) use ($todayCounts) {
            return [
                'total' => $value,
                'todayCount' => $todayCounts[$key]
            ];
        });

        return [
            'registrationTypes' => $formattedData,
            'overAllCount' => $formattedData->sum('total'),
            'overAllTodayCount' => $formattedData->sum('todayCount'),
        ];
    }

    public function render()
    {
        $top5Cities = $this->getTopLocationDetails('city');
        $top5States = $this->getTopLocationDetails('state');
        $top5Countries = $this->getTopLocationDetails('country');
        $lastSevenDaysCounts = $this->getLastSevenDaysVisitorsCount();
        $businessTypeCounts = $this->getBusinessTypeDetails();
        $knownSourceCounts = $this->getKnownSourceDetails();
        $registrationTypeCounts = $this->getRegistrationTypewiseCounts();

        return view('livewire.report-handler', [
            'top5Cities' => $top5Cities,
            'top5States' => $top5States,
            'top5Countries' => $top5Countries,
            'lastSevenDaysVisitors' => $lastSevenDaysCounts,
            'businessTypeCounts' => $businessTypeCounts,
            'knownSourceCounts' => $knownSourceCounts,
            'registrationTypeCounts' => $registrationTypeCounts,
        ])->layout('layouts.admin');
    }
}
