<?php

namespace App\Http\Livewire;

use App\Exports\ExhibitorsExport;
use App\Models\Address;
use App\Models\Appointment;
use App\Models\Event;
use App\Models\EventExhibitor;
use App\Models\Exhibitor;
use App\Models\ExhibitorContact;
use App\Models\ExhibitorProduct;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Number;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class ExhibitorSummary extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';
    #[Url]
    public $search;
    public $is_checked = false;
    public $exhibitor_id = null;
    public $stall_space;
    public $square_space;
    public $stall_no;
    #[Url]
    public $eventId;
    #[Url( as :'product')]
    public $productSearch;
    public $products = [];
    public $sortName = 'id';
    public $sortDirection = 'desc';
    public $sortedTable;
    public $events = [];
    public $event_id;
    public $selectAll = false;
    public $showToggle = false;
    public $selectedExhibitors = [];
    public $startDate;
    public $endDate;
    public $eventsList = [];
    public $requestEventId;
    public $exhibitorsTotalCount;
    public $eventExhibitorsCount;
    public $currentEventId;
    public $showFilter = true;
    protected $listeners = [
        'deleteExhibitor' => 'deleteExhibitor',
        'dateRangeChanged' => 'dateRangeChanged',
    ];
    protected $rules = [
        // 'stall_space' => 'required',
        // 'square_space' => 'required',
        'stall_no' => 'required',
    ];
    protected $messages = [
        // 'stall_space.required' => 'Stall Space is required',
        // 'square_space.required' => 'Square Space is required',
        'stall_no.required' => 'Stall No is required',
    ];
    public function dateRangeChanged($startDate, $endDate)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function mount(Request $request)
    {
        $this->requestEventId = $request->eventId ?? null;
        if ($this->requestEventId) {
            $this->eventId = $this->requestEventId ?? null;
        }
        $currentEvent = getCurrentEvent();
        $this->currentEventId = $currentEvent->id;

        $this->productSearch = request()->product;

        $this->products = Product::pluck('name', 'id');
        $this->events = Event::where('start_date', '>=', now()->format('Y-m-d'))
            ->orWhere('end_date', '>', now()->format('Y-m-d'))
            ->pluck('title', 'id');
        $this->eventsList = Event::pluck('title', 'id');

        $totalExhibitors = Exhibitor::when($this->requestEventId, function ($query) {
            $query->whereHas('eventExhibitors', function ($query) {
                $query->where('event_id', $this->requestEventId);
            });
        })->count();

        $currentEventExhibitors = EventExhibitor::where('event_id', $this->currentEventId)
            ->count();

        $this->exhibitorsTotalCount = Number::abbreviate($totalExhibitors);
        $this->eventExhibitorsCount = Number::abbreviate($currentEventExhibitors);
    }

    public function updatedSelectAll($value)
    {
        if ($value) {
            $exhibitor = $this->getFilteredExhibitors()->get();
            $this->selectedExhibitors = $exhibitor->pluck('id');
        } else {
            $this->selectedExhibitors = [];
        }
    }

    public function toggleEvents()
    {
        $this->showToggle = !$this->showToggle;
    }
    public function selectedExhibitorsId()
    {
        $isCreated = null;
        if (count($this->selectedExhibitors) > 0) {
            $exhibitorRecords = Exhibitor::whereIn('id', $this->selectedExhibitors)->get();
            foreach ($exhibitorRecords as $exhibitorRecord) {
                $isExists = $exhibitorRecord->eventExhibitors()->where('event_id', $this->event_id)->exists();
                if (!$isExists) {
                    $isCreated = $exhibitorRecord->eventExhibitors()->create([
                        'event_id' => $this->event_id,
                    ]);
                }
            }
            if ($isCreated) {
                redirect()->route('exhibitor.summary');
                session()->flash('success', 'Exhibitors added successfully.');
            }
        } else {
            session()->flash('info', 'Please select atleast one exhibitor.');
        }
    }

    public function getExhibitorId($id)
    {
        $this->exhibitor_id = $id;
        $eventExhibitorInfo = EventExhibitor::where('exhibitor_id', $this->exhibitor_id)->where('event_id', $this->eventId)->first();
        $this->stall_no = $eventExhibitorInfo->stall_no ?? '';
        $this->stall_space = $eventExhibitorInfo->_meta['stall_space'] ?? '';
        $this->square_space = $eventExhibitorInfo->_meta['square_space'] ?? '';
    }
    public function updateStallDetail()
    {

        $this->validate();
        $isStallNoExists = EventExhibitor::where('event_id', $this->eventId)
            ->where('stall_no', $this->stall_no)
            ->where('exhibitor_id', '!=', $this->exhibitor_id)
            ->first();
        if ($isStallNoExists) {
            $this->addError('stall_no', 'Stall No already exists');
            return;
        }
        if ($this->exhibitor_id) {
            $exhibitor = Exhibitor::find($this->exhibitor_id);
            $exhibitor->eventExhibitors()->where('event_id', $this->eventId)->update([
                'stall_no' => $this->stall_no,
                '_meta' => [
                    'stall_space' => $this->stall_space,
                    'square_space' => $this->square_space,
                ],
            ]);
        }
        $this->dispatch('closeModal');
        session()->flash('success', 'Stall detail updated successfully.');
    }

    public function sortBy($sortTable, $sortName, $sortDirection)
    {
        $this->sortedTable = $sortTable;
        $this->sortName = $sortName;
        $this->sortDirection = $sortDirection;
    }
    public function clearError()
    {
        $this->resetErrorBag();
        $this->stall_no = null;
        $this->stall_space = null;
        $this->square_space = null;
        $this->dispatch('closeModal');
    }

    private function getFilteredExhibitors()
    {

        $query = Exhibitor::with(['eventExhibitors', 'exhibitorContact', 'address'])
            ->when(isSalesPerson(), function ($query) {
                $query->whereIn('exhibitors.id', mappedExhibitors($this->eventId));
            });

        if ($this->eventId) {
            $query->whereHas('eventExhibitors', function ($query) {
                $query->where('event_id', $this->eventId)
                    ->when(!empty($this->startDate), function ($query) {
                        $query->where('created_at', '>=', Carbon::parse($this->startDate)->startOfDay());
                    })
                    ->when(!empty($this->endDate), function ($query) {
                        $query->where('created_at', '<=', Carbon::parse($this->endDate)->endOfDay());
                    })->when(!empty($this->productSearch), function ($query) {
                    $query->whereJsonContains('products', $this->productSearch);
                });
            });
        } else {
            $query->when(!empty($this->startDate), function ($query) {
                $query->whereDate('created_at', '>=', Carbon::parse($this->startDate)->startOfDay());
            })->when(!empty($this->endDate), function ($query) {
                $query->whereDate('created_at', '<=', Carbon::parse($this->endDate)->endOfDay());
            })->when(!empty($this->productSearch), function ($query) {
                $query->whereHas('exhibitorProducts', function ($query) {
                    $query->where('product_id', $this->productSearch);
                });
            });
        }

        $query->where(function ($query) {
            // General Search
            if (!empty($this->search)) {
                $trimmedSearch = trim($this->search);
                $query->where(function ($query) use ($trimmedSearch) {
                    $query->where('exhibitors.name', 'LIKE', '%' . $trimmedSearch . '%')
                        ->orWhere('exhibitors.mobile_number', 'LIKE', '%' . $trimmedSearch . '%')
                        ->orWhere('exhibitors.email', 'LIKE', '%' . $trimmedSearch . '%')
                        ->orWhereHas('exhibitorContact', function ($query) use ($trimmedSearch) {
                            $query->where('exhibitor_contacts.contact_number', 'LIKE', '%' . $trimmedSearch . '%')
                                ->orWhere('exhibitor_contacts.name', 'LIKE', '%' . $trimmedSearch . '%');
                        });
                    // ->orWhereHas('address', function ($query) use ($trimmedSearch) {
                    //     $query->where('addresses.city', 'LIKE', '%' . $trimmedSearch . '%')
                    //         ->where('addresses.addressable_type', 'App\Models\Exhibitor');
                    // });
                });
            }
        });
        if ($this->sortedTable === 'contact_person') {
            $query->join('exhibitor_contacts', 'exhibitor_contacts.exhibitor_id', '=', 'exhibitors.id')
                ->select('exhibitors.*')
                ->orderBy('exhibitor_contacts.' . $this->sortName, $this->sortDirection);
        }
        if ($this->sortedTable === 'address') {
            $query->join('addresses', function ($join) {
                $join->on('addresses.addressable_id', '=', 'exhibitors.id')
                    ->where('addresses.addressable_type', '=', 'App\Models\Exhibitor');
            })
                ->select('exhibitors.*')
                ->orderBy('addresses.' . $this->sortName, $this->sortDirection);
        }
        if ($this->sortedTable === 'appointments') {
            $query->withCount([
                'appointments' => function ($query) {
                    $query->when($this->eventId, function ($query) {
                        $query->where('event_id', $this->eventId);
                    });
                },
            ])
                ->orderBy($this->sortName, $this->sortDirection);
        }
        return $query->orderBy($this->sortName, $this->sortDirection);
    }
    public function render()
    {
        $exhibitors = $this->getFilteredExhibitors()->paginate(25);

        return view('livewire.exhibitor-summary', compact('exhibitors'))->layout('layouts.admin');
    }

    public function exportToExcel()
    {
        $exhibitorsData = $this->getFilteredExhibitors()->get();
        if (count($exhibitorsData) > 0) {
            return (new ExhibitorsExport($this->requestEventId, $exhibitorsData))->download('exhibitors.xlsx');
        }
    }

    public function updated($propertyName)
    {
        $propertiesToResetPage = ['search', 'productSearch', 'eventId', 'startDate', 'endDate'];

        if (in_array($propertyName, $propertiesToResetPage)) {
            $this->resetPage();
        }
    }

    public function deleteExhibitor($exhibitorId)
    {
        $this->exhibitor_id = $exhibitorId;
        $doesntParticipatePreviousEvents = EventExhibitor::where('exhibitor_id', $this->exhibitor_id)
            ->where('event_id', '!=', $this->eventId)
            ->doesntExist();

        if ($doesntParticipatePreviousEvents) {
            $exhibitor = Exhibitor::find($this->exhibitor_id);

            if (!$exhibitor) {
                session()->flash('error', 'Exhibitor not found!');
                return;
            }
            Address::where('addressable_id', $this->exhibitor_id)
                ->where('addressable_type', 'App\Models\Exhibitor')
                ->forceDelete();

            ExhibitorContact::where('exhibitor_id', $this->exhibitor_id)
                ->forceDelete();

            ExhibitorProduct::where('exhibitor_id', $this->exhibitor_id)
                ->forceDelete();

            EventExhibitor::where('exhibitor_id', $this->exhibitor_id)
                ->forceDelete();

            $exhibitor->forceDelete();

            session()->flash('success', 'Exhibitor and associated records forcefully deleted successfully!');
        } else {
            $currentEventExhibitor = EventExhibitor::where('exhibitor_id', $this->exhibitor_id)
                ->where('event_id', $this->eventId)
                ->first();

            if (!$currentEventExhibitor) {
                session()->flash('error', 'Exhibitor not found!');
                return;
            }

            Appointment::where('exhibitor_id', $this->exhibitor_id)
                ->where('event_id', $this->eventId)
                ->forceDelete();

            EventExhibitor::where('exhibitor_id', $this->exhibitor_id)
                ->where('event_id', $this->eventId)
                ->forceDelete();

            session()->flash('success', 'Exhibitor and associated records forcefully deleted successfully!');
        }
    }
    public function filterCurrentEventRecords()
    {
        $this->eventId = $this->currentEventId;
    }

    public function resetEventId()
    {
        $this->eventId = null;
    }

    public function toggleFilter()
    {
        $this->showFilter = !$this->showFilter;
    }

    public function gotoProfile($id, $type)
    {
        return redirect()->route('profile-view', ['profileId' => $id, 'type' => $type, 'eventId' => $this->currentEventId]);
    }
    public function resetPassword($exhibitorId)
    {
        $exhibitor = Exhibitor::find($exhibitorId);
        if ($exhibitor) {
            $defaultPassword = config('app.default_user_password');

            $exhibitor->password = Hash::make($defaultPassword);
            $exhibitor->save();
            session()->flash('success', 'Password reset successfully.');
        } else {
            session()->flash('error', 'Exhibitor not found.');
        }
    }
}
