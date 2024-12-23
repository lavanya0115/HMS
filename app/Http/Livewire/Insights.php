<?php

namespace App\Http\Livewire;

use App\Models\EventExhibitor;
use App\Models\EventVisitor;
use App\Models\Exhibitor;
use App\Models\Seminar;
use App\Models\Visitor;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\WithPagination;

class Insights extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $selectedRange = 'all';
    public $visitor10tCount;
    public $visitorOnlineCount;
    public $visitorWhatsapptCount;
    public $totalVisitorCount;
    public $visitorOfflineCount;
    public $TotalSeminarCount;
    public $totalExhibitorCount;
    public $totalDelegatesCount;
    public $exibitorOfflineCount;
    public $exibitorOnlineCount;

    public $eventId;
    public $tableTitle = 'Visitor Offline Registration List';
    public $isExhibitor;
    public $visitorCountByRegistrationType = [];
    public $exhibitorCountByRegistrationType = [];

    public function setRange($duration)
    {
        $this->selectedRange = $duration;
    }

    public function changeTitle($title)
    {
        $this->tableTitle = $title;
        Log::info("Title: " . $this->tableTitle);
        $this->isExhibitor = explode(' ', $this->tableTitle)[0] == 'Exhibitors' ? true : false;
        $this->resetPage();
    }

    public function mount()
    {
        $this->eventId = request()->eventId;

        $startDate = Carbon::now()->subDays(7)->startOfDay();
        $endDate = Carbon::now()->endOfDay();

        $visitorCountByRegistrationType = EventVisitor::where('event_id', $this->eventId)
            ->select('registration_type', DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as visitor_count'))
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('date', 'registration_type')
            ->get()
            ->groupBy('date')->toArray();

        $distinctRegistrationTypes = Visitor::whereNotIn('registration_type', ['medicall', 'online'])
            ->distinct('registration_type')
            ->pluck('registration_type');

        foreach ($visitorCountByRegistrationType as $date => $registrationTypes) {

            $existingTypes = [];
            $totalRegTypes = [];
            $medicallRegType = null;
            $onlineRegType = null;

            foreach ($registrationTypes as $count) {
                $existingTypes[] = $count['registration_type'];

                if ($count['registration_type'] === 'medicall') {
                    $medicallRegType = $count;
                } elseif ($count['registration_type'] === 'online') {
                    $onlineRegType = $count;
                } else {
                    $totalRegTypes[] = $count;
                }
            }

            $missingTypes = $distinctRegistrationTypes->diff($existingTypes)->toArray();

            foreach ($missingTypes as $missingType) {
                $totalRegTypes[] = [
                    'date' => $date,
                    'registration_type' => $missingType,
                    'visitor_count' => 0,
                ];
            }

            $totalRegTypes[] = [
                'date' => $date,
                'registration_type' => 'medicall',
                'visitor_count' => ($medicallRegType['visitor_count'] ?? 0) + ($onlineRegType['visitor_count'] ?? 0),
            ];

            $visitorCountByRegistrationType[$date] = $totalRegTypes;
        }

        $this->visitorCountByRegistrationType = $visitorCountByRegistrationType;

        $exhibitorCountByRegistrationType = EventExhibitor::where('event_id', $this->eventId)->join('exhibitors', 'event_exhibitors.exhibitor_id', '=', 'exhibitors.id')
            ->select('exhibitors.registration_type', DB::raw('DATE(event_exhibitors.created_at) as date'), DB::raw('COUNT(*) as exhibitor_count'))
            ->whereBetween('event_exhibitors.created_at', [$startDate, $endDate])
            ->groupBy('exhibitors.registration_type', DB::raw('DATE(event_exhibitors.created_at)'))
            ->get()
            ->groupBy('date');

        $this->exhibitorCountByRegistrationType = $exhibitorCountByRegistrationType->toArray();

        // dd($this->exhibitorCountByRegistrationType);

    }

    public function render()
    {

        $startDate = Carbon::now();
        // dd($startDate);
        $endDate = Carbon::now();

        if ($this->selectedRange === 'last7Days') {
            $startDate->subDays(6);
        } elseif ($this->selectedRange === 'last30Days') {
            $startDate->subDays(29);
        } elseif ($this->selectedRange === 'last3Months') {
            $startDate->subMonths(2)->startOfMonth();
        }

        $this->totalVisitorCount = Visitor::when($this->eventId != null, function ($query) {
            $query->whereHas('eventVisitors', function ($getEventVisitors) {
                $getEventVisitors->where('event_id', $this->eventId);
            });
        })->count();

        $this->visitorOfflineCount = EventVisitor::when($this->eventId != null, function ($query) use ($startDate, $endDate) {
            $query->where('event_id', $this->eventId)
                ->whereIn('registration_type', ['medicall', 'online'])
                ->when($this->selectedRange !== 'all', function ($query) use ($startDate, $endDate) {
                    $query->whereBetween('created_at', [$startDate, $endDate]);
                });
        })->count();

        $this->visitorOnlineCount = EventVisitor::when($this->eventId != null, function ($query) use ($startDate, $endDate) {
            $query->where('event_id', $this->eventId)
                ->where('registration_type', 'web')
                ->when($this->selectedRange !== 'all', function ($query) use ($startDate, $endDate) {
                    $query->whereBetween('created_at', [$startDate, $endDate]);
                });
        })->count();

        $this->visitorWhatsapptCount = EventVisitor::when($this->eventId != null, function ($query) use ($startDate, $endDate) {
            $query->where('event_id', $this->eventId)
                ->where('registration_type', 'whatsapp')
                ->when($this->selectedRange !== 'all', function ($query) use ($startDate, $endDate) {
                    $query->whereBetween('created_at', [$startDate, $endDate]);
                });
        })->count();

        $this->visitor10tCount = EventVisitor::when($this->eventId != null, function ($query) use ($startDate, $endDate) {
            $query->where('event_id', $this->eventId)
                ->where('registration_type', '10t')
                ->when($this->selectedRange !== 'all', function ($query) use ($startDate, $endDate) {
                    $query->whereBetween('created_at', [$startDate, $endDate]);
                });
        })->count();

        $this->TotalSeminarCount = Seminar::when($this->eventId != null, function ($query) use ($startDate, $endDate) {
            $query->where('event_id', $this->eventId)
                ->where('is_active', 1)
                ->when($this->selectedRange !== 'all', function ($query) use ($startDate, $endDate) {
                    $query->whereBetween('created_at', [$startDate, $endDate]);
                });
        })->count();

        $this->totalExhibitorCount = Exhibitor::when($this->eventId != null, function ($query) {
            $query->whereHas('eventExhibitors', function ($getEventExhibitors) {
                $getEventExhibitors->where('event_id', $this->eventId);
            });
        })->count();

        $this->exibitorOnlineCount = EventExhibitor::when($this->eventId != null, function ($query) use ($startDate, $endDate) {
            $query->where('event_id', $this->eventId)
                ->whereHas('exhibitor', function ($getExhibitors) {
                    $getExhibitors->where('registration_type', 'import-online');
                })
                ->when($this->selectedRange !== 'all', function ($query) use ($startDate, $endDate) {
                    $query->whereBetween('created_at', [$startDate, $endDate]);
                });
        })->count();

        $this->exibitorOfflineCount = EventExhibitor::when($this->eventId != null, function ($query) use ($startDate, $endDate) {
            $query->where('event_id', $this->eventId)
                ->whereHas('exhibitor', function ($getExhibitors) {
                    $getExhibitors->where('registration_type', 'web');
                })
                ->when($this->selectedRange !== 'all', function ($query) use ($startDate, $endDate) {
                    $query->whereBetween('created_at', [$startDate, $endDate]);
                });
        })->count();

        $this->totalDelegatesCount = EventVisitor::when($this->eventId != null, function ($query) use ($startDate, $endDate) {
            $query->where('event_id', $this->eventId)
                ->where('is_delegates', '1')
                ->when($this->selectedRange !== 'all', function ($query) use ($startDate, $endDate) {
                    $query->whereBetween('created_at', [$startDate, $endDate]);
                });
        })->count();

        $users = [];

        if (!$this->isExhibitor) {
            $users = EventVisitor::when($this->eventId != null, function ($query) use ($startDate, $endDate) {
                $query->where('event_id', $this->eventId)
                    ->when($this->tableTitle == 'Visitors Online Registration List', function ($type) {
                        $type->whereIn('registration_type', ['medicall', 'online']);
                    })
                    ->when($this->tableTitle == 'Visitors HMS Registration List', function ($type) {
                        $type->whereIn('registration_type', ['web']);
                    })
                    ->when($this->tableTitle == 'Visitors Whatsapp Registraion List', function ($type) {
                        $type->whereIn('registration_type', ['whatsapp']);
                    })
                    ->when($this->tableTitle == 'Visitors 10T Registraion List', function ($type) {
                        $type->whereIn('registration_type', ['10t']);
                    })
                    ->when($this->tableTitle == 'Delegates List', function ($type) {
                        return $type->whereIn('is_delegates', [1]);
                    })
                    ->when($this->selectedRange !== 'all', function ($query) use ($startDate, $endDate) {
                        $query->whereBetween('created_at', [$startDate, $endDate]);
                    });
            })
                ->orderBy('id', 'desc')
                ->paginate(10);
        } else {
            $users = EventExhibitor::when($this->eventId != null, function ($query) use ($startDate, $endDate) {
                $query->where('event_id', $this->eventId)
                    ->whereHas('exhibitor', function ($getExhibitors) {

                        $getExhibitors->when($this->tableTitle == 'Exhibitors Online Registraion List', function ($type) {
                            $type->where('registration_type', 'import-online');
                        });
                        $getExhibitors->when($this->tableTitle == 'Exhibitors Offline Registraion List', function ($type) {
                            $type->where('registration_type', 'web');
                        });
                    })
                    ->when($this->selectedRange !== 'all', function ($query) use ($startDate, $endDate) {
                        $query->whereBetween('created_at', [$startDate, $endDate]);
                    });
            })
                ->orderBy('id', 'desc')
                ->paginate(10);
        }
        $seminarList = Seminar::where('event_id', $this->eventId)
            ->where('is_active', 1)
            ->when($this->selectedRange !== 'all', function ($query) use ($startDate, $endDate) {
                $query->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()]);
            })
            ->orderBy('id', 'desc')
            ->paginate(10);

        // dd($seminarList);

        // $knowingSourceVisitor = EventVisitor::when($this->evnetId != null, function ($query){
        //     $query->where('event_id', $this->evnetId)
        //     ->whereHas('visitor', function($getVisitorSource){
        //         $getVisitorSource->groupBy('known_source');
        //     });
        // });
        // $knowingSourceVisitor = EventVisitor::when($this->eventId != null, function ($query) {
        //     $query->where('event_id', $this->eventId);
        // })->with('visitor')->get();

        // $knowingSourceVisitorCounts = $knowingSourceVisitor->groupBy('visitor.known_source')->map->count();

        return view('livewire.insights', [
            'users' => $users,
            'seminarList' => $seminarList,
            // 'knowingSourceVisitorCounts' => $knowingSourceVisitorCounts,
        ])->layout('layouts.admin');
    }
}
