<?php

namespace App\Http\Livewire;

use App\Exports\VisitorsExport;
use App\Models\Event;
use App\Models\EventVisitor;
use App\Models\Visitor;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Number;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class VisitorSummary extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';
    public $eventId;
    #[Url]
    public $search = '';
    public $sortBy = 'id';
    public $sortDirection = 'desc';
    public $events = [];
    public $event_id;
    public $selectAll = false;
    public $showToggle = false;
    public $selectedVisitors = [];
    protected $queryString = ['search' => ['except' => ''],
        'participateStatus' => ['except' => ''], 'visitorRegId' => ['except' => '']];

    public $visitorId;
    public $visitorRegId;
    public $participateStatus;
    public $eventsList = [];
    public $requestEventId;
    public $visitorsTotalCount;
    public $eventVisitorsCount;
    public $currentEventId;
    public $startDate;
    public $endDate;
    public $exporting = false;
    public $countries = [];
    public $username_exists = false;
    public $suggestedValue = '';
    public $showFilter = true;
    public $visitorData = [
        'event_id' => [],
        'salutation' => 'Mr',
        'name' => '',
        'username' => '',
        'password' => '',
        'email' => '',
        'mobile_no' => '',
        'organization' => '',
        'designation' => '',
        'known_source' => '',
        'country' => 'India',
        'pincode' => '',
        'city' => '',
        'state' => '',
        'address' => '',
        'registration_type' => 'web',
    ];

    protected $listeners = [
        'message' => 'alertStatus',
        'dateRangeChanged',
    ];

    protected $rules = [
        'visitorData.event_id' => 'required',
        'visitorData.username' => 'required|string|unique:visitors,username',
        'visitorData.name' => 'required|regex:/^[a-zA-Z ]+$/',
        'visitorData.email' => 'required|email',
        'visitorData.mobile_no' => 'required|digits:10',
        'visitorData.designation' => 'required',
        'visitorData.organization' => 'required',
        'visitorData.pincode' => 'required|digits:6',
        'visitorData.country' => 'required',
        'visitorData.known_source' => 'required',
    ];
    protected $messages = [
        'visitorData.event_id.required' => 'Event is required',
        'visitorData.username.required' => 'Username is required',
        'visitorData.username.string' => 'Username must be string',
        'visitorData.username.unique' => 'Username already exists',
        'visitorData.name.required' => 'Name is required',
        'visitorData.name.regex' => 'Name must be alphabetic',
        'visitorData.email.required' => 'Email is required',
        'visitorData.email.email' => 'Email is not valid',
        'visitorData.mobile_no.required' => 'Mobile number is required',
        'visitorData.mobile_no.digits' => 'Mobile number must be 10 digits',
        'visitorData.designation.required' => 'Designation is required',
        'visitorData.organization.required' => 'Organization is required',
        'visitorData.pincode.required' => 'Pincode is required',
        'visitorData.pincode.digits' => 'Pincode must be 6 digits',
        'visitorData.country.required' => 'Country is required',
        'visitorData.known_source.required' => 'Known Source is required',
    ];

    public function gotoProfile($id, $type)
    {

        return redirect()->route('profile-view', ['profileId' => $id, 'type' => $type,'eventId' => $this->eventId]);
    }

    public function dateRangeChanged($startDate, $endDate)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function alertStatus($status = null, $message = null)
    {
        if ($status && $message) {
            session()->flash($status, $message);
        }
    }

    public function mount(Request $request)
    {
        $this->requestEventId = $request->eventId ?? null;
        if ($this->requestEventId) {
            $this->eventId = $this->requestEventId ?? null;
        }
        $currentEvent = getCurrentEvent();
        $this->currentEventId = $currentEvent->id;
    }
    public function sortColumn($field, $order = 'asc')
    {
        $this->sortDirection = $order;
        $this->sortBy = $field;
    }

    public function applySorting($query)
    {
        if ($this->sortBy && in_array($this->sortBy, ['id', 'name', 'mobile_number', 'email', 'organization'])) {
            $query->orderBy($this->sortBy, $this->sortDirection);
        }
    }
    public function updatedSelectAll($value)
    {
        if ($value) {
            $visitor = $this->getFilteredVisitors()->get();
            $this->selectedVisitors = $visitor->pluck('id');
        } else {
            $this->selectedVisitors = [];
        }
    }

    public function toggleEvents()
    {
        $this->showToggle = !$this->showToggle;
    }
    public function selectedVisitorsId()
    {
        $isCreated = null;
        if (count($this->selectedVisitors) > 0) {
            $visitorRecords = Visitor::whereIn('id', $this->selectedVisitors)->get();
            foreach ($visitorRecords as $visitorRecord) {
                $isExists = $visitorRecord->eventVisitors()->where('event_id', $this->event_id)->exists();
                if (!$isExists) {
                    $isCreated = $visitorRecord->eventVisitors()->create([
                        'event_id' => $this->event_id,
                    ]);
                }
            }
            if ($isCreated) {
                redirect()->route('visitors.summary');
                session()->flash('success', 'Visitors added successfully.');
            }
        } else {
            session()->flash('info', 'Please select atleast one visitor.');
        }
    }

    public function getVisitor($id)
    {
        $this->visitorId = $id;
        $this->dispatch('selectedVisitor', [$this->visitorId]);
    }

    private function getFilteredVisitors()
    {

        $query = Visitor::query()->withCount('visitor_logins');

        if ($this->startDate) {
            if ($this->eventId) {
                $query->whereHas('eventVisitors', function ($query) {
                    $query->where('event_id', $this->eventId)
                        ->where('created_at', '>=', Carbon::parse($this->startDate)->startOfDay());
                });
            } else {
                $query->whereDate('created_at', '>=', Carbon::parse($this->startDate)->startOfDay());
            }
        }

        if ($this->endDate) {
            if ($this->eventId) {
                $query->whereHas('eventVisitors', function ($query) {
                    $query->where('event_id', $this->eventId)
                        ->where('created_at', '<=', Carbon::parse($this->endDate)->endOfDay());
                });
            } else {
                $query->whereDate('created_at', '<=', Carbon::parse($this->endDate)->endOfDay());
            }
        }

        $query->when($this->eventId, function ($query) {
            $query->whereHas('eventVisitors', function ($query) {
                $query->where('event_id', $this->eventId)
                    ->where('is_delegates', '<>', 1)
                    ->when($this->participateStatus == 'visited', function ($query) {
                        $query->where('is_visited', 1);
                    })
                    ->when($this->participateStatus == 'not_visited', function ($query) {
                        $query->where('is_visited', 0);
                    });
            });
        })
        // ->unless($this->eventId, function ($query) {
        //     $query->whereDoesntHave('eventVisitors', function ($query) {
        //         $query->where('is_delegates', 1);
        //     });
        // })

            ->when($this->visitorRegId, function ($query) {
                $query->whereHas('eventVisitors', function ($subquery) {
                    $subquery->where('_meta->reference_no', 'like', '%' . trim($this->visitorRegId) . '%');
                });
            })

            ->when(trim($this->search), function ($query) {
                $query->where(function ($query) {
                    $trimmedSearch = trim($this->search);

                    $query->where('name', 'like', '%' . $trimmedSearch . '%')
                        ->orWhere('mobile_number', 'like', '%' . $trimmedSearch . '%')
                        ->orWhere('email', 'like', '%' . $trimmedSearch . '%')
                        ->orWhere('organization', 'like', '%' . $trimmedSearch . '%')
                        ->orWhere('designation', 'like', '%' . $trimmedSearch . '%');
                });
            })

            ->when($this->sortBy, function ($query) {
                if ($this->sortBy === 'appointments_count') {
                    $query->withCount([

                        'appointments' => function ($query) {
                            $query->when($this->eventId, function ($query) {
                                $query->where('event_id', $this->eventId);
                            });
                        },

                    ])
                        ->orderBy($this->sortBy, $this->sortDirection);
                } else if ($this->sortBy === 'event_visitors_count') {
                    $query->withCount([

                        'eventVisitors' => function ($query) {
                            $query->where('is_visited', 1);
                        },

                    ])
                        ->orderBy($this->sortBy, $this->sortDirection);
                } else {
                    $this->applySorting($query);
                }
            });
        return $query;
    }
    public function render()
    {
        $visitorsQuery = $this->getFilteredVisitors();
        $visitors = $visitorsQuery->paginate(25);

        $this->events = Event::where('start_date', '>=', now()->format('Y-m-d'))
            ->orWhere('end_date', '>', now()->format('Y-m-d'))
            ->pluck('title', 'id');

        $this->eventsList = Event::pluck('title', 'id');

        $totalVisitors = Visitor::when($this->requestEventId, function ($query) {
            $query->whereHas('eventVisitors', function ($query) {
                $query->where('event_id', $this->requestEventId);
            });
        })->count();

        $currentEventVisitors = EventVisitor::where('event_id', $this->currentEventId)
            ->where('is_delegates', 0)
            ->count();

        $this->visitorsTotalCount = Number::abbreviate($totalVisitors);
        $this->eventVisitorsCount = Number::abbreviate($currentEventVisitors);

        $this->countries = getCountries();

        return view('livewire.visitor-summary', [
            'visitors' => $visitors,
        ])->layout('layouts.admin');
    }

    public function exportToExcel()
    {

        $params = [
            'startDate' => $this->startDate,
            'endDate' => $this->endDate,
            'eventId' => $this->eventId,
            'visitorRegId' => $this->visitorRegId,
            'participateStatus' => $this->participateStatus,
            'search' => $this->search,
            'sortBy' => $this->sortBy,
        ];

        try {
            $paramsCollection = collect($params);
            $export = new VisitorsExport($this->requestEventId, $paramsCollection);
            return $export->download('visitors.xlsx');
        } catch (\Exception $e) {
            session()->flash('error', 'An error occurred while exporting the data. Please try again later.');
        }

    }

    public function updated($propertyName)
    {
        $propertiesToResetPage = ['search', 'participateStatus', 'visitorRegId', 'eventId', 'startDate', 'endDate'];

        if (in_array($propertyName, $propertiesToResetPage)) {
            $this->resetPage();
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
    public function pincode()
    {
        if (strtolower($this->visitorData['country']) == 'india' && isset($this->visitorData['pincode'])) {
            // You can call getPincodeData directly here or adjust it as needed.
            $pincodeData = getPincodeData($this->visitorData['pincode']);

            if ($pincodeData['state'] === null && $pincodeData['city'] === null) {
                $this->addError("visitorData.pincode", "Pincode is not Exists");
            } else {
                $this->resetErrorBag('visitorData.pincode');
                $this->visitorData['state'] = $pincodeData['state'];
                $this->visitorData['city'] = $pincodeData['city'];
            }
        }
    }

    public function clearAddressFields()
    {
        $this->visitorData['city'] = null;
        $this->visitorData['state'] = null;
        $this->visitorData['pincode'] = null;
    }

    public function checkUserName()
    {
        $this->username_exists = true;
        $this->validate([
            'visitorData.username' => 'string|unique:visitors,username',
        ]);

        $this->username_exists = false;
    }

    public function getProfileName()
    {
        $profile_name = str_replace(' ', '', $this->visitorData['name']);
        $this->visitorData['username'] = $profile_name;
        $this->suggestedValue = $profile_name . rand(0, 9999);
    }

    public function setSuggestedValue()
    {
        $this->visitorData['username'] = $this->suggestedValue;
        $this->checkUserName();
    }

    public function createVisitor()
    {
        $this->validate();

        $VisitorEmailExists = Visitor::where('email', $this->visitorData['email'])->first();

        if ($VisitorEmailExists) {
            $this->addError('visitorData.email', 'Email already exists');
            return;
        }

        $visitorPhoneNoExists = Visitor::where('mobile_number', $this->visitorData['mobile_no'])->first();

        if ($visitorPhoneNoExists) {
            $this->addError('visitorData.mobile_no', 'Mobile no already exists.');
            return;
        }

        $this->visitorData['password'] = Hash::make(config('app.default_user_password'));
        try {
            DB::beginTransaction();

            $visitor = Visitor::create([
                'username' => $this->visitorData['username'],
                'salutation' => $this->visitorData['salutation'],
                'name' => $this->visitorData['name'],
                'password' => $this->visitorData['password'],
                'mobile_number' => $this->visitorData['mobile_no'],
                'email' => $this->visitorData['email'],
                'organization' => $this->visitorData['organization'],
                'designation' => $this->visitorData['designation'],
                'known_source' => $this->visitorData['known_source'],
                'registration_type' => $this->visitorData['registration_type'],
            ]);

            foreach ($this->visitorData['event_id'] as $eventId) {
                $visitor->eventVisitors()->create([
                    'event_id' => $eventId,
                    'visitor_id' => $visitor->id,
                    'known_source' => $this->visitorData['known_source'],
                    'registration_type' => $this->visitorData['registration_type'],
                ]);
            }

            $visitor->address()->create([
                'pincode' => $this->visitorData['pincode'],
                'city' => $this->visitorData['city'],
                'state' => $this->visitorData['state'],
                'country' => $this->visitorData['country'],
                'address' => $this->visitorData['address'],
            ]);

            $authData = getAuthData();
            if ($authData !== null) {
                $visitor->update(['created_by' => $authData->id]);
            } else {
                $visitor->update(['created_by' => null]);
            }

            DB::commit();

            sendWelcomeMessageThroughWhatsappBot($visitor->mobile_number, 'visitor');
            $this->closeModal();
            session()->flash('success', 'Visitor created successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', $e->getMessage());
        }
    }
    public function closeModal()
    {
        $this->dispatch('closeVisitorModal');
        $this->resetErrorBag();
    }
    public function toggleFilter()
    {
        $this->showFilter = !$this->showFilter;
    }
}
