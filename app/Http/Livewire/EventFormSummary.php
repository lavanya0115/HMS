<?php

namespace App\Http\Livewire;

use App\Models\Appointment;
use App\Models\Event;
use App\Models\EventExhibitor;
use App\Models\EventVisitor;
use App\Models\Exhibitor;
use App\Models\UserLoginActivity;
use App\Models\Visitor;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Number;
use Livewire\Component;

class EventFormSummary extends Component
{
    public $perPage = 4;

    public $currentEventId;

    public $eventId, $route;

    public $mappedExhibitors;

    public $isPreviousEvent = false;

    public $isUpcomingEvent = false;

    public $type = 'user';

    public $logType = 'userLogs';

    public $startDate;
    public $endDate;
    public $registrationTypes = [];

    public function registerVisitor($eventId)
    {
        $visitorInfo = Visitor::find(getAuthData()->id);
        $visitorInfo->eventVisitors()->create([
            'event_id' => $eventId,
            'registration_type' => 'web',

        ]);
        session()->flash('success', 'Registered Successfully.');
    }

    public function registerExhibitor($eventId)
    {
        $exhibitorInfo = Exhibitor::find(getAuthData()->id);
        $exhibitorInfo->eventExhibitors()->create([
            'event_id' => $eventId,
        ]);
        session()->flash('success', 'Registered Successfully.');
    }

    public function mount()
    {
      
    }

    // public function getLoginLogs(Request $request)
    // {
    //     $dateFilterType = $request->get('date_filter_type', 'all');

    //     $visitorLogs = UserLoginActivity::with('visitor')
    //         ->where('userable_type', 'App\Models\Visitor')
    //         ->select('userable_id', DB::raw('count(*) as login_count'))
    //         ->when("last_7_days" === $dateFilterType, function ($query) {
    //             return $query->where('created_at', '>=', now()->subDays(7));
    //         })
    //         ->when("last_15_days" === $dateFilterType, function ($query) {
    //             return $query->where('created_at', '>=', now()->subDays(15));
    //         })
    //         ->when("last_30_days" === $dateFilterType, function ($query) {
    //             return $query->where('created_at', '>=', now()->subDays(30));
    //         })
    //         ->groupBy('userable_id')
    //         ->orderBy('login_count', 'desc')
    //         ->take(10)
    //         ->get();

    //     $exhibitorLogs = UserLoginActivity::with('exhibitor')
    //         ->where('userable_type', 'App\Models\Exhibitor')
    //         ->select('userable_id', DB::raw('count(*) as login_count'))
    //         ->when("last_7_days" === $dateFilterType, function ($query) {
    //             return $query->where('created_at', '>=', now()->subDays(7));
    //         })
    //         ->when("last_15_days" === $dateFilterType, function ($query) {
    //             return $query->where('created_at', '>=', now()->subDays(15));
    //         })
    //         ->when("last_30_days" === $dateFilterType, function ($query) {
    //             return $query->where('created_at', '>=', now()->subDays(30));
    //         })
    //         ->groupBy('userable_id')
    //         ->orderBy('login_count', 'desc')
    //         ->take(10)
    //         ->get();

    //     $userLogs = UserLoginActivity::with('user')
    //         ->where('userable_type', 'App\Models\User')
    //         ->select('userable_id', DB::raw('count(*) as login_count'))
    //         ->when("last_7_days" === $dateFilterType, function ($query) {
    //             return $query->where('created_at', '>=', now()->subDays(7));
    //         })
    //         ->when("last_15_days" === $dateFilterType, function ($query) {
    //             return $query->where('created_at', '>=', now()->subDays(15));
    //         })
    //         ->when("last_30_days" === $dateFilterType, function ($query) {
    //             return $query->where('created_at', '>=', now()->subDays(30));
    //         })
    //         ->groupBy('userable_id')
    //         ->orderBy('login_count', 'desc')
    //         ->take(10)
    //         ->get();

    //     $totalVisitorLoginsCount = count($visitorLogs);
    //     $totalExhibitorLoginsCount = count($exhibitorLogs);
    //     $totalUserLoginsCount = count($userLogs);

    //     $label = 'All';
    //     if ("last_7_days" === $dateFilterType) {
    //         $label = 'Last 7 Days';
    //     } elseif ("last_15_days" === $dateFilterType) {
    //         $label = 'Last 15 Days';
    //     } elseif ("last_30_days" === $dateFilterType) {
    //         $label = 'Last 30 Days';
    //     }

    //     return [
    //         'visitorLogs' => $visitorLogs,
    //         'exhibitorLogs' => $exhibitorLogs,
    //         'userLogs' => $userLogs,
    //         'totalVisitorLoginsCount' => $totalVisitorLoginsCount,
    //         'totalExhibitorLoginsCount' => $totalExhibitorLoginsCount,
    //         'totalUserLoginsCount' => $totalUserLoginsCount,
    //         'label' => $label,
    //     ];
    // }

    public function getKnownsourceWiseCount()
    {
        $regTypeWiseKnownSourceCounts = [];
        $knownSources = getKnownSourceData();
        $eventVisitors = EventVisitor::select('registration_type', 'known_source', \DB::raw('COUNT(*) as count'))
            ->where('event_id', $this->currentEventId)
            ->whereNotNull('known_source')
            ->when($this->startDate, function ($query) {
                $query->whereDate('created_at', '>=', $this->startDate);
            })
            ->when($this->endDate, function ($query) {
                $query->whereDate('created_at', '<=', $this->endDate);
            })
            ->groupBy('registration_type', 'known_source')
            ->get();

        foreach ($eventVisitors as $visitor) {
            $registrationType = match (strtolower($visitor->registration_type)) {
                'medicall', 'online' => 'Web',
                'web' => 'CRM',
                default => $visitor->registration_type,
            };
            if (!isset($regTypeWiseKnownSourceCounts[$registrationType])) {
                $regTypeWiseKnownSourceCounts[$registrationType] = [
                    'label' => $registrationType,
                    'data' => array_fill(0, count($knownSources), 0),
                ];
            }

            foreach (array_keys($knownSources) as $index => $knownSourceKey) {
                $knownSourceValue = $knownSources[$knownSourceKey];
                if ($visitor->known_source === $knownSourceKey || $visitor->known_source === $knownSourceValue) {
                    $regTypeWiseKnownSourceCounts[$registrationType]['data'][$index] += $visitor->count;
                }
            }
        }
        $datasets = array_values($regTypeWiseKnownSourceCounts);
        return [
            'labels' => array_values($knownSources),
            'datasets' => $datasets,
        ];
    }

    public function dateRangeFilter($startDate, $endDate)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $source = $this->getKnownsourceWiseCount();
        $this->dispatch('updateChart', ['labels' => $source['labels'], 'datasets' => $source['datasets']]);
    }

    public function render(Request $request)
    {
        return view('livewire.event-form-summary')->layout('layouts.admin');
    }

    // public function changeTitle($type, $logType)
    // {
    //     $this->type = $type;
    //     $this->logType = $logType;
    // }
}
