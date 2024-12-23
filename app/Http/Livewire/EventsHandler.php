<?php

namespace App\Http\Livewire;

use Throwable;
use Carbon\Carbon;
use App\Models\Event;
use App\Models\Address;
use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Livewire\WithFileUploads;

class EventsHandler extends Component
{
    use WithFileUploads;
    public $event = [
        'country' => 'India',
        'pincode',
        'state',
        'city',
        'address',
        'title',
        'startDate',
        'endDate',
        'organizer' => null,
        'description' => null,
        'contact' => null,
        'latitude',
        'longitude',
        'eventPeriod',
        'description',
        'code',
        'invoiceTitle',
    ];
    public $eventId = null, $pincodeData;
    public $hallLayout;
    public $exhibitorList;
    public $location, $edition, $year_of_event;
    public $years = [];
    public $photo;

    protected $listeners = [
        'setEventPeriod' => 'setEventPeriod',
    ];

    protected $rules = [
        'event.title' => 'required',
        'event.startDate' => 'required',
        'event.endDate' => 'required|after_or_equal:event.startDate',
        // 'event.organizer' => 'required|string',
        // 'event.contact' => 'required|regex:/^[0-9]{10}$/|starts_with:6,7,8,9',
        'event.country' => 'required',
        'event.pincode' => 'required',
        'event.address' => 'required',
        'event.latitude' => 'required',
        'event.longitude' => 'required',
        'event.eventPeriod' => 'required',
        'event.description' => 'required',
        'event.invoiceTitle' => 'required',
        'location' => 'required',
        'edition' => 'required',
        'year_of_event' => 'required',
    ];

    protected $messages = [
        'event.title.required' => 'Enter The Event Title.',
        'event.startDate.required' => 'Enter The Event Start Date.',
        'event.endDate.required' => 'Enter The Event End Date.',
        'event.endDate.after_or_equal' => 'Enter Currnet or Future Date From The Start Date',
        // 'event.organizer.required' => 'Organizer Field is Required.',
        // 'event.organizer.string' => 'Organizer Field Must Be a String.',
        // 'event.contact.required' => 'Contact field is Required.',
        // 'event.contact.regex' => 'Enter Valid Contact Number',
        // 'event.contact.starts_with' => 'Check The Contact Number is Valid',
        'event.address.required' => 'Address Field is Required.',
        'event.country.required' => 'Country Field is Required.',
        'event.pincode.required' => 'This Field is Required.',
        'event.latitude.required' => 'Latitude Field is Required.',
        'event.longitude.required' => 'Longitude Field is Required.',
        'event.eventPeriod.required' => 'Event period Field is Required.',
        'event.description.required' => 'Event Name Field is Required.',
        'event.invoiceTitle.required' => 'Invoice Title Field is Required.',
        'location' => 'location Field is Required.',
        'edition' => 'Edition Field is Required.',
        'year_of_event' => 'Year of event field is required.',
    ];

    public function pincode()
    {
        if ($this->event['country'] == 'India' && isset($this->event['pincode'])) {
            $pincodeData = getPincodeData($this->event['pincode']);
            if ($pincodeData['state'] === null && $pincodeData['city'] === null) {
                $this->addError("event.pincode", "Pincode is not Exists");
                $this->event['state'] = null;
                $this->event['city'] = null;
            } else {
                $this->resetErrorBag('event.pincode');
                $this->event['state'] = $pincodeData['state'];
                $this->event['city'] = $pincodeData['city'];
            }
        } else {
            $this->event['pincode'] = null;
            $this->event['city'] = null;
            $this->event['state'] = null;
        }
    }

    public function resetFields()
    {
        $this->reset();
    }


    public function create()
    {
        $this->authorize('Create Event');
        $this->validate();

        $eventExists = Event::where('title', $this->event['title'])->first();
        if ($eventExists) {
            $this->addError('event.title', 'Event Title Already Exists.');
            return;
        }

        DB::beginTransaction();
        try {
            $authId = getAuthData()->id;
            $layoutPath = '';
            $imagePath = '';
            $exhibitorListPath = '';
            // dd($this->photo);
            if ($this->photo) {
                $imageFolderPath = 'thumbnail/' . date('Y/m');
                $imageName = $this->photo->getClientOriginalName();
                $imagePath = $this->photo->storeAs($imageFolderPath, $imageName, 'public');
            }
            if ($this->hallLayout) {
                $fileFolderPath = 'layout/' . date('Y/m');
                $fileName = $this->hallLayout->getClientOriginalName();
                $layoutPath = $this->hallLayout->storeAs($fileFolderPath, $fileName, 'public');
            }
            if ($this->exhibitorList) {
                $fileFolderPath = 'exhibitorList/' . date('Y/m');
                $fileName = $this->exhibitorList->getClientOriginalName();
                $exhibitorListPath = $this->exhibitorList->storeAs($fileFolderPath, $fileName, 'public');
            }
            $event = Event::create(
                [
                    "created_by" => $authId,
                    "updated_by" => $authId,
                    "title" => $this->event['title'],
                    "start_date" => $this->event['startDate'],
                    "end_date" => $this->event['endDate'],
                    "_meta" => [
                        'thumbnail' => $imagePath,
                        'layout' => $layoutPath,
                        'latitude' => $this->event['latitude'],
                        'longitude' => $this->event['longitude'],
                        'exhibitorList' => $exhibitorListPath,
                        'location' => $this->location,
                        'edition' => $this->edition,
                        'year_of_event' => $this->year_of_event,
                    ],
                    'event_description' => $this->event['description'],
                    'event_period' => $this->event['eventPeriod'],
                    'event_code' => $this->event['code'],
                    'invoice_title' => $this->event['invoiceTitle'],
                    // "organizer" => $this->event['organizer'],
                    // "contact" => $this->event['contact'],
                    // "description" => $this->event['description'] ?? null,
                ]
            );

            $event->address()->create([
                'country' => $this->event['country'],
                'state' => $this->event['country'] == "India" ? $this->event['state'] : null,
                'city' => $this->event['country'] == "India" ? $this->event['city'] : null,
                'pincode' => $this->event['pincode'],
                'address' => $this->event['address'],
            ]);
            DB::commit();

            if ($event) {
                session()->flash('success', 'Event created successfully.');
                return redirect(route('events'));
            }
            session()->flash('error', 'Event was not created ');
            return;
        } catch (\Exception $e) {
            DB::rollback();
            session()->flash('error', $e->getMessage());
            return;
        }
    }

    public function update()
    {
        $this->validate();
        $eventExists = Event::where('title', $this->event['title'])->where('id', '!=', $this->eventId)->first();
        if ($eventExists) {
            $this->addError('event.title', 'Event Title Already Exists.');
            return;
        }

        try {
            $event = Event::find($this->eventId);
            if ($event) {
                $imagePath = $event['_meta']['thumbnail'] ?? '';

                if (isset($this->photo)) {
                    if (isset($event['_meta']['thumbnail']) && !empty($event['_meta']['thumbnail'])) {
                        $filepath = public_path('storage/' . $event['_meta']['thumbnail']);
                        if (file_exists($filepath)) {
                            unlink($filepath);
                        }
                    }

                    $imageFolderPath = 'thumbnail/' . date('Y/m');
                    $imageName = $this->photo->getClientOriginalName();
                    $imagePath = $this->photo->storeAs($imageFolderPath, $imageName, 'public');
                    // dd($this->photo, $event['_meta']['thumbnail'], $imagePath);
                }

                $layoutPath = $event['_meta']['layout'] ?? '';
                if (isset($this->hallLayout)) {
                    // dd($event['_meta']['layout']);
                    if (isset($event['_meta']['layout']) && !empty($event['_meta']['layout'])) {
                        $filepath = public_path('storage/' . $event['_meta']['layout']);
                        if (file_exists($filepath)) {
                            unlink($filepath);
                        }
                    }

                    $fileFolderPath = 'layout/' . date('Y/m');
                    $fileName = $this->hallLayout->getClientOriginalName();
                    $layoutPath = $this->hallLayout->storeAs($fileFolderPath, $fileName, 'public');
                }

                $exhibitorListPath = $event['_meta']['exhibitorList'] ?? '';
                if (isset($this->exhibitorList)) {
                    if (isset($event['_meta']['exhibitorList']) && !empty($event['_meta']['exhibitorList'])) {
                        $filepath = public_path('storage/' . $event['_meta']['exhibitorList']);
                        if (file_exists($filepath)) {
                            unlink($filepath);
                        }
                    }

                    $fileFolderPath = 'exhibitorList/' . date('Y/m');
                    $fileName = $this->exhibitorList->getClientOriginalName();
                    $exhibitorListPath = $this->exhibitorList->storeAs($fileFolderPath, $fileName, 'public');
                }
                $event->update([
                    "updated_by" => getAuthData()->id,
                    "title" => $this->event['title'],
                    "start_date" => $this->event['startDate'],
                    "end_date" => $this->event['endDate'],
                    "_meta" => [
                        'thumbnail' => $imagePath,
                        'layout' => $layoutPath,
                        'latitude' => $this->event['latitude'],
                        'longitude' => $this->event['longitude'],
                        'exhibitorList' => $exhibitorListPath,
                        'location' => $this->location,
                        'edition' => $this->edition,
                        'year_of_event' => $this->year_of_event,
                    ],
                    'event_description' => $this->event['description'],
                    'event_period' => $this->event['eventPeriod'],
                    'event_code' => $this->event['code'],
                    'invoice_title' => $this->event['invoiceTitle'],
                    // "organizer" => $this->event['organizer'],
                    // "contact" => $this->event['contact'],
                    // "description" => $this->event['description'] ?? null,


                ]);

                $event->address()->update([
                    'country' => $this->event['country'],
                    'state' => $this->event['country'] == "India" ? $this->event['state'] : null,
                    'city' => $this->event['country'] == "India" ? $this->event['city'] : null,
                    'pincode' => $this->event['country'] == "India" ? $this->event['pincode'] : null,
                    'address' => $this->event['address'],
                ]);

                session()->flash("success", "Event Details Successfully Updated");
                return redirect(route('events'));
            }
            session()->flash("error", "Unable to Update the Event Details");
            return;
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
            return;
        }
    }

    public function setEventPeriod($startDate, $endDate)
    {
        $this->event['eventPeriod'] = "$startDate to $endDate";
    }

    public function mount()
    {
        $this->eventId = request('eventId') !== null  ? request('eventId') : null;

        $currentEdition = Event::orderBy('id', 'desc')->first();
        $meta = $currentEdition->_meta;
        $nextEdition = isset($meta['edition']) ? getNextEdition($meta['edition']) : getNextEdition($currentEdition->title);
        $this->edition = $nextEdition;

        if ($this->eventId) {
            $this->authorize('Update Event');
            $eventData = Event::find($this->eventId);
            // $event ? $eventDetails = $event->toArray() : [];
            // $event ? $eventAddress =$event->address->toArray() :[];
            if (!empty($eventData)) {
                $this->event['title'] = $eventData['title'];
                $this->event['startDate'] = Carbon::parse($eventData['start_date'])->format('Y-m-d');
                $this->event['endDate'] = Carbon::parse($eventData['end_date'])->format('Y-m-d');
                $this->event['organizer'] = $eventData['organizer'];
                $this->event['contact'] = $eventData['contact'];
                $this->event['description'] = $eventData['description'] ?? null;
                $this->event['thumbnail'] = $eventData['_meta']['thumbnail'] ?? null;
                $this->event['pincode'] = $eventData->address?->pincode ?? '';
                $this->event['country'] = $eventData->address?->country ?? '';
                $this->event['state'] = $eventData->address?->state ?? '';
                $this->event['city'] = $eventData->address?->city ?? '';
                $this->event['address'] = $eventData->address?->address ?? '';
                $this->event['latitude'] = $eventData['_meta']['latitude'] ?? null;
                $this->event['longitude'] = $eventData['_meta']['longitude'] ?? null;
                $this->location = $eventData['_meta']['location'] ?? null;
                $this->edition = $eventData['_meta']['edition'] ?? null;
                $this->year_of_event = $eventData['_meta']['year_of_event'] ?? null;
                $this->event['description'] = $eventData['event_name'] ?? null;
                $this->event['eventPeriod'] = $eventData['event_period'] ?? null;
                $this->event['code'] = $eventData['event_code'] ?? null;
                $this->event['invoiceTitle'] = $eventData['invoice_title'] ?? null;
            }
            return;
        }
    }

    public function render()
    {
        $currentYear = now()->year;
        $this->years = collect(range($currentYear, $currentYear + 5));

        $countries = getCountries();
        $this->event['description'] = " $this->edition Medicall ($this->location - $this->year_of_event)";

        if (!empty($this->location) && !empty($this->year_of_event)) {
            $location = getShrtFormOfLocation($this->location);
            $this->event['code'] = "$location - $this->year_of_event";
        }
        return view(
            'livewire.events-handler',
            [
                'countries' => $countries,
            ]
        )->layout('layouts.admin');
    }
}
