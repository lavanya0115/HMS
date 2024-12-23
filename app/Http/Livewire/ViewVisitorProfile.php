<?php

namespace App\Http\Livewire;

use App\Models\Address;
use App\Models\Category;
use App\Models\EventExhibitor;
use App\Models\EventVisitor;
use App\Models\Exhibitor;
use App\Models\Product;
use App\Models\UserLoginActivity;
use App\Models\Visitor;
use Illuminate\Http\Request;
use Livewire\Component;
use Spatie\Activitylog\Models\Activity;

class ViewVisitorProfile extends Component
{
    public $visitor;
    public $seminars = [];
    public $events;
    public $colors = [
        'red',
        'blue',
        'azure',
        'indigo',
        'purple',
        'pink',
        'orange',
        'yellow',
        'lime',
        'green',
        'teal',
        'cyan',
        'dark',
    ];
    public $previousEvents;
    public $upcomingEvents;
    public $currentEvent;
    public $eventVisitors;
    public $type;
    public $profileId;
    public $exhibitor;
    public $eventExhibitors;
    public $newHospital;
    public $oldHospital;
    public $underConstruction;
    public $yearsToComplete;
    public $yearsOld;
    public $editMode = false;
    public $user_name, $organization, $designation, $contact_no, $mail_id, $address, $nature_of_business, $contact_person_name;
    public $categories;
    public $is_correct_address = false;
    public $previousProfile;
    public $nextProfile;
    public $previousProfileId, $nextProfileId;
    public $eventId;
    public function mount(Request $request)
    {
        $this->profileId = $request->profileId;
        $this->eventId = $request->eventId;
        $this->type = $request->type;
        if ($this->type == 'visitor' && !empty($this->profileId)) {
            $this->visitor = Visitor::find($this->profileId);
            $this->eventVisitors = $this->visitor->eventVisitors;
            $metaData = $this->visitor->_meta ?? [];
            $hospitalInfo = $metaData['hospitalInfo'] ?? [];

            $this->newHospital = $hospitalInfo['newHospital'] ?? false;
            $this->oldHospital = $hospitalInfo['oldHospital'] ?? false;
            $this->underConstruction = $hospitalInfo['underConstruction'] ?? null;
            $this->yearsToComplete = $hospitalInfo['yearsToComplete'] ?? null;
            $this->yearsOld = $hospitalInfo['yearsOld'] ?? null;
            $this->is_correct_address = $this->visitor->address->is_correct_address ?? false;

            foreach ($this->visitor->eventVisitors as $eventVisitor) {
                $this->seminars[] = [
                    'eventName' => $eventVisitor->event->title,
                    'seminarTitle' => $eventVisitor->getSeminarNames(),
                ];
            }
            $this->categories = Category::where('type', 'visitor_business_type')->where('is_active', 1)->get();
        } else {
            $this->exhibitor = Exhibitor::find($this->profileId);
            $this->eventExhibitors = $this->exhibitor->eventExhibitors;
            $this->categories = Category::where('type', 'exhibitor_business_type')->where('is_active', 1)->get();
        }
        $this->previousEvents = getPreviousEvents();
        $this->currentEvent = getCurrentEvent();
        $this->upcomingEvents = getUpComingEvents();
        if ($this->type === 'visitor' && $this->visitor) {
            if ($this->eventId) {
                $currentEventVisitorRecord = EventVisitor::where('visitor_id', $this->profileId)
                    ->where('event_id', $this->eventId)
                    ->first();
                // dd($currentEventExhibitorRecord);

                $previousProfile = EventVisitor::where('id', '<', $currentEventVisitorRecord->id)
                    ->where('event_id', $this->eventId)
                    ->orderBy('id', 'desc')
                    ->first();

                $nextProfile = EventVisitor::where('id', '>', $currentEventVisitorRecord->id)
                    ->where('event_id', $this->eventId)
                    ->orderBy('id', 'asc')
                    ->first();

                $this->previousProfileId = $previousProfile->visitor_id ?? null;
                $this->nextProfileId = $nextProfile->visitor_id ?? null;
            } else {
                $previousProfile = Visitor::where('id', '<', $this->profileId)
                    ->orderBy('id', 'asc')
                    ->first();

                $nextProfile = Visitor::where('id', '>', $this->profileId)
                    ->orderBy('id', 'desc')
                    ->first();
                $this->previousProfileId = $previousProfile->id ?? null;
                $this->nextProfileId = $nextProfile->id ?? null;
            }
        } elseif ($this->type === 'exhibitor' && $this->exhibitor) {

            if ($this->eventId) {
                $currentEventExhibitorRecord = EventExhibitor::where('exhibitor_id', $this->profileId)
                    ->where('event_id', $this->eventId)
                    ->first();
                // dd($currentEventExhibitorRecord);

                $previousProfile = EventExhibitor::where('id', '<', $currentEventExhibitorRecord->id)
                    ->where('event_id', $this->eventId)
                    ->orderBy('id', 'desc')
                    ->first();

                $nextProfile = EventExhibitor::where('id', '>', $currentEventExhibitorRecord->id)
                    ->where('event_id', $this->eventId)
                    ->orderBy('id', 'asc')
                    ->first();

                $this->previousProfileId = $previousProfile->exhibitor_id ?? null;
                $this->nextProfileId = $nextProfile->exhibitor_id ?? null;
            } else {
                $previousProfile = Exhibitor::where('id', '<', $this->profileId)
                    ->orderBy('id', 'asc')
                    ->first();

                $nextProfile = Exhibitor::where('id', '>', $this->profileId)
                    ->orderBy('id', 'desc')
                    ->first();
                $this->previousProfileId = $previousProfile->id ?? null;
                $this->nextProfileId = $nextProfile->id ?? null;
            }

        }

    }

    public function updateHospitalInfo()
    {

        $metaData = $this->visitor->_meta ?? [];

        $metaData['hospitalInfo'] = [
            'newHospital' => $this->newHospital ?? false,
            'oldHospital' => $this->oldHospital ?? false,
            'underConstruction' => !empty($this->underConstruction) ? $this->underConstruction : null,
            'yearsToComplete' => !empty($this->yearsToComplete) ? $this->yearsToComplete : null,
            'yearsOld' => !empty($this->yearsOld) ? $this->yearsOld : null,
        ];

        $this->visitor->_meta = $metaData;
        $this->visitor->save();
        session()->flash('success', 'Hospital details saved successfully.');
        $this->reloadVisitorData();
    }
    public function reloadVisitorData()
    {
        $this->visitor->refresh();
        $metaData = $this->visitor->_meta ?? [];
        $hospitalInfo = $metaData['hospitalInfo'] ?? [];

        $this->newHospital = $hospitalInfo['newHospital'] ?? false;
        $this->oldHospital = $hospitalInfo['oldHospital'] ?? false;
        $this->underConstruction = $hospitalInfo['underConstruction'] ?? null;
        $this->yearsToComplete = $hospitalInfo['yearsToComplete'] ?? null;
        $this->yearsOld = $hospitalInfo['yearsOld'] ?? null;
    }
    public function getDatagridItems($data)
    {
        $address = $this->getAddress($data->address);
        $eventId = getCurrentEvent()->id;
        $dataGridItems = [
            [
                'id' => 1,
                'icon' => 'icons.user',
                'label' => 'User Name',
                'fieldName' => 'user_name',
                'value' => $data->username ?? '--',

            ],
            [
                'id' => 2,
                'icon' => ($this->type === 'visitor') ? 'icons.building-skyscraper' : 'icons.user-circle',
                'label' => ($this->type === 'visitor') ? 'Organization' : 'Contact Person Name',
                'fieldName' => ($this->type === 'visitor') ? 'organization' : 'contact_person_name',
                'value' => ($this->type === 'visitor') ? $data->organization : $data?->exhibitorContact?->salutation . ' ' . $data->exhibitorContact->name ?? '--',
            ],
            [
                'id' => 3,
                'icon' => 'icons.briefcase',
                'label' => 'Designation',
                'fieldName' => 'designation',
                'value' => ($this->type === 'visitor') ? $data->designation : $data?->exhibitorContact?->designation ?? '--',
            ],
            [
                'id' => 4,
                'icon' => 'icons.phone',
                'label' => 'Contact No.',
                'fieldName' => 'contact_no',
                'value' => ($this->type === 'visitor') ? $data->mobile_number : $data?->exhibitorContact?->contact_number ?? '--',
            ],
            [
                'id' => 5,
                'icon' => 'icons.email',
                'label' => 'Mail-Id',
                'fieldName' => 'mail_id',
                'value' => $data->email ?? '--',
            ],
            [
                'id' => 6,
                'icon' => 'icons.map-pin',
                'label' => 'Address',
                'fieldName' => 'address',
                'value' => $address,
            ],
            [
                'id' => 7,
                'icon' => 'icons.category',
                'label' => 'Nature of Business',
                'fieldName' => 'nature_of_business',
                'value' => $data?->category?->name ?? '--',
            ],
            [
                'id' => 8,
                'icon' => 'icons.appointment',
                'label' => 'No. of Appointments',
                'value' => '<span class="badge bg-cyan text-cyan-fg ms-2">' . ($data->appointments->count() ?? '0') . '</span>',
            ],
            [
                'id' => 9,
                'icon' => 'icons.file-description',
                'label' => 'Registration Type',
                'value' => $data->registration_type ?? '--',
            ],
        ];

        if ($this->type === 'visitor') {

            $knownSource = $data->known_source ?? '';
            if ($eventId) {
                $knownSource = $data->eventVisitors?->where('event_id', $eventId)->first()->known_source ?? $knownSource;
            }

            if (is_numeric($knownSource)) {
                $knownSourceLabel = getKnownSourceDataById($knownSource);
            } else {
                $knownSourceLabel = getKnownSourceLabelByGivenSource($knownSource);
            }

            $dataGridItems[] = [
                'id' => 9,
                'icon' => 'icons.file-description',
                'label' => 'Known Source',
                'value' => $knownSourceLabel,
            ];
        }

        return $dataGridItems;
    }
    public function roleMapping($type)
    {
        if ($type === 'App\Models\Visitor' || $type === 'App\Models\Exhibitor' || $type === 'App\Models\User') {
            $roles = [
                'App\Models\Visitor' => 'Visitor',
                'App\Models\Exhibitor' => 'Exhibitor',
                'App\Models\User' => 'Admin',
            ];
            $role = $roles[$type] ?? '';
            return $role;
        } else {
            $types = [
                'visitor' => 'App\Models\Visitor',
                'exhibitor' => 'App\Models\Exhibitor',
                'admin' => 'App\Models\User',
            ];
            $type = $types[$type] ?? '';
            return $type;
        }
    }
    public function render()
    {
        $whishlistExhibitors = [];
        $whishlistProducts = [];

        if ($this->type === 'visitor') {
            foreach ($this->eventVisitors as $eventVisitor) {
                $eventId = $eventVisitor->event->id;
                $eventName = $eventVisitor->event->title;
                $exhibitorIds = $eventVisitor->wishlist
                    ->where('event_id', $eventId)
                    ->whereNotNull('exhibitor_id')
                    ->pluck('exhibitor_id')
                    ->toArray();

                $exhibitors = Exhibitor::whereIn('id', $exhibitorIds)->pluck('name');
                $whishlistExhibitors[$eventName] = $exhibitors;
                $productsId = $eventVisitor->wishlist
                    ->where('event_id', $eventId)
                    ->whereNotNull('product_id')
                    ->pluck('product_id')
                    ->toArray();

                $products = Product::whereIn('id', $productsId)->pluck('name');
                $whishlistProducts[$eventName] = $products;
            }
        }

        $logs = Activity::when($this->type == 'visitor', function ($query) {
            $query->where(function ($query) {
                $query->where('subject_id', $this->profileId)
                    ->where('subject_type', 'LIKE', '%Visitor%')
                    ->orWhere(function ($query) {
                        $query->where('causer_id', $this->profileId)
                            ->where('causer_type', 'LIKE', '%Visitor%');
                    });
            })->whereIn('log_name', ['visitor_log', 'appointment_log', 'event_visitor_log']);
        })
            ->when($this->type == 'exhibitor', function ($query) {
                $query->where(function ($query) {
                    $query->where('subject_id', $this->profileId)
                        ->where('subject_type', 'LIKE', '%Exhibitor%')
                        ->orWhere(function ($query) {
                            $query->where('causer_id', $this->profileId)
                                ->where('causer_type', 'LIKE', '%Exhibitor%');
                        });
                })->whereIn('log_name', ['exhibitor_log', 'appointment_log', 'event_visitor_log', 'exhibitor_contact_log', 'exhibitor_product_log']);
            })
            ->orderBy('id', 'desc')
            ->paginate(10);

        $userLogs = UserLoginActivity::when(!empty($this->profileId), function ($query) {
            $query->where('userable_id', $this->profileId);
        })->when(!empty($this->type), function ($query) {
            $query->where('userable_type', $this->roleMapping($this->type));
        })
            ->orderBy('id', 'desc')->paginate(10);

        return view('livewire.view-visitor-profile', [
            'whishlistProducts' => $whishlistProducts,
            'whishlistExhibitors' => $whishlistExhibitors,
            'logs' => $logs,
            'userLogs' => $userLogs,
            'previousProfile' => $this->previousProfile,
            'nextProfile' => $this->nextProfile,
        ])->layout('layouts.admin');
    }
    public function getVisitorOrExhibitorName($id, $type)
    {
        $name = '';
        if ($type === 'visitor') {
            $name = Visitor::find($id)->name;
        } elseif ($type === 'exhibitor') {
            $name = Exhibitor::find($id)->name;
        }
        return $name;
    }
    public function getAddress($address)
    {
        $fullAddress = '--';
        if ($address) {
            $fullAddress = $address->address;
            if ($address->city) {
                $fullAddress .= ', ' . $address->city;
            }
            if ($address->state) {
                $fullAddress .= ', ' . $address->state;
            }
            if ($address->country) {
                $fullAddress .= ', ' . $address->country;
            }
        }
        return $fullAddress;
    }
    public function editDetails()
    {
        $this->editMode = true;
        if ($this->type === 'visitor') {
            $address = $this->getAddress($this->visitor?->address);
            $this->user_name = $this->visitor->username;
            $this->organization = $this->visitor->organization;
            $this->designation = $this->visitor->designation;
            $this->contact_no = $this->visitor->mobile_number;
            $this->mail_id = $this->visitor->email;
            $this->nature_of_business = $this->visitor?->category_id;
            $this->address = $this->visitor?->address->address ?? $address;
        } else {
            $address = $this->getAddress($this->exhibitor?->address);
            $this->user_name = $this->exhibitor?->username;
            $this->contact_person_name = $this->exhibitor?->exhibitorContact->name;
            $this->designation = $this->exhibitor?->exhibitorContact?->designation;
            $this->contact_no = $this->exhibitor?->exhibitorContact?->contact_number;
            $this->mail_id = $this->exhibitor?->email;
            $this->nature_of_business = $this->exhibitor?->category_id;
            $this->address = $this->exhibitor?->address->address ?? $address;
        }
    }
    public function updateUserDetails($id)
    {
        if ($this->type === 'visitor') {

            $visitor = Visitor::find($id);
            $visitor->organization = $this->organization;
            $visitor->designation = $this->designation;
            $visitor->mobile_number = $this->contact_no;
            $visitor->email = $this->mail_id;
            $visitor->category_id = $this->nature_of_business;
            $visitor->save();

            $address = Address::where('addressable_id', $id)
                ->where('addressable_type', 'App\Models\Visitor')
                ->first();

            $address->address = $this->address;
            $address->save();

            $isUpdated = $visitor->wasChanged(['organization', 'designation', 'mobile_number', 'email']);
            $this->editMode = false;
            if ($isUpdated) {
                session()->flash('success', 'User details updated successfully.');
                return redirect()->route('profile-view', ['profileId' => $id, 'type' => $this->type]);
            } else {
                session()->flash('info', 'No changes found. Try again later.');
                return redirect()->route('profile-view', ['profileId' => $id, 'type' => $this->type]);
            }
        } else {

            $exhibitor = Exhibitor::find($id);
            $exhibitor->email = $this->mail_id;
            $exhibitor->category_id = $this->nature_of_business;
            $exhibitor->save();

            $exhibitorContact = $exhibitor->exhibitorContact;
            $exhibitorContact->name = $this->contact_person_name;
            $exhibitorContact->contact_number = $this->contact_no;
            $exhibitorContact->designation = $this->designation;
            $exhibitorContact->save();

            $address = Address::where('addressable_id', $id)
                ->where('addressable_type', 'App\Models\Exhibitor')
                ->first();

            $address->address = $this->address;
            $address->save();

            $isUpdatedExhibitor = $exhibitor->wasChanged(['category_id', 'email']);
            $isUpdatedExhibitorContact = $exhibitorContact->wasChanged(['name', 'designation', 'contact_number']);
            $this->editMode = false;
            if ($isUpdatedExhibitor || $isUpdatedExhibitorContact) {
                session()->flash('success', 'User details updated successfully.');
                return redirect()->route('profile-view', ['profileId' => $id, 'type' => $this->type]);
            } else {
                session()->flash('info', 'No changes found. Try again later.');
                return redirect()->route('profile-view', ['profileId' => $id, 'type' => $this->type]);
            }
        }
    }

    public function updateIsCorrectAddress()
    {
        if (isset($this->visitor->address)) {
            $this->visitor->address->is_correct_address = $this->is_correct_address;
            $this->visitor->address->save();

            session()->flash('success', 'Address status updated successfully.');
            return redirect()->route('profile-view', ['profileId' => $this->visitor->id, 'type' => 'visitor']);

        }

    }

}
