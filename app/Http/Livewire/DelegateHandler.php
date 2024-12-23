<?php

namespace App\Http\Livewire;

use App\Models\Address;
use App\Models\Event;
use App\Models\EventSeminarParticipant;
use App\Models\EventVisitor;
use App\Models\Seminar;
use App\Models\Visitor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;

class DelegateHandler extends Component
{
    public $delegate = [
        'username' => '',
        'salutation' => 'Dr',
        'name' => '',
        'mobile_number' => '',
        'email' => '',
        'organization' => '',
        'designation' => '',
        'known_source' => '',
        'newsletter' => 0,
        'seminars_to_attend' => [],
        'registration_type' => 'web',
        'event_id' => '',
        'coupen_code' => '',
        'pincode' => '',
        'city' => '',
        'state' => '',
        'country' => 'India',
        'address' => '',
        'payment_option' => '',
        'amount' => '',
        'query' => '',
    ];

    public $countries = [];
    public $eventId;
    public $events;
    public $profile_name;
    public $suggestedValue;
    public $currentEventSeminars = [];

    protected $rules = [
        'delegate.event_id' => 'required',
        'delegate.name' => 'required|regex:/^[a-zA-Z ]+$/',
        'delegate.email' => 'required|string|email',
        'delegate.mobile_number' => 'required|digits:10',
        'delegate.designation' => 'required',
        'delegate.organization' => 'required',
        'delegate.pincode' => 'required',
        'delegate.seminars_to_attend' => 'required',
        'delegate.address' => 'required',
    ];

    protected $messages = [
        'delegate.event_id.required' => 'Event is required',
        'delegate.name.required' => 'The Name field is required.',
        'delegate.name.regex' => 'Enter valid Name',
        'delegate.email.required' => 'The email field is required.',
        'delegate.email.email' => 'Enter valid email.',
        'delegate.mobile_number.required' => 'The mobile number field is required.',
        'delegate.mobile_number.digits' => 'Please give the valid mobile number',
        'delegate.organization.required' => 'Please select your organization',
        'delegate.designation.required' => 'Please enter your designation',
        'delegate.pincode.required' => 'Enter valid pincode/zipcode',
        'delegate.seminars_to_attend.required' => 'Please select atleast one seminar',
        'delegate.address.required' => 'Please enter your address',
    ];

    public function create()
    {
        $this->getProfileName();
        $this->validate();

        $DelegateEmailExists = Visitor::where('email', $this->delegate['email'])->first(); // Corrected variable name

        if ($DelegateEmailExists) {
            $this->addError('delegate.email', 'Delagate email address already exists'); // Corrected error message
            return;
        }

        $delegatePhoneNoExists = Visitor::where('mobile_number', $this->delegate['mobile_number'])->first();

        if ($delegatePhoneNoExists) {
            $this->addError('delegate.mobile_number', 'Delegate phone number already exists.');
            return;
        }
        $this->delegate['password'] = Hash::make(config('app.default_user_password'));
        try {
            DB::beginTransaction();

            $delegate = Visitor::create($this->delegate);

            if ($delegate) {

                $address = new Address($this->delegate);
                $delegate->address()->save($address);


                $newEventDelegate = new EventVisitor();
                $newEventDelegate->event_id = $this->delegate['event_id'];
                $newEventDelegate->visitor_id = $delegate->id;
                $newEventDelegate->is_delegates = true;
                $newEventDelegate->known_source = $this->delegate['known_source'];

                $meta = $newEventDelegate->_meta;
                $meta['coupen_code'] = $this->delegate['coupen_code'] ?? '';
                $meta['queries'] = $this->delegate['query'] ?? '';
                $newEventDelegate->_meta = $meta;
                $newEventDelegate->save();

                $selectedSeminars = $this->delegate['seminars_to_attend'];

                foreach ($selectedSeminars as $seminarId) {
                    $amount = Seminar::find($seminarId)->amount;
                    $paymentStatus = $this->delegate['payment_option'] == 'register_and_pay' ? 'paid' : 'pay_later';
                    $paymentType = $this->delegate['payment_option'] == 'register_and_pay' ? 'manual' : '';

                    $registerForSeminar = new EventSeminarParticipant();
                    $registerForSeminar->event_id = $this->delegate['event_id'];
                    $registerForSeminar->visitor_id = $delegate->id;
                    $registerForSeminar->seminar_id = $seminarId;
                    $registerForSeminar->amount = $amount;
                    $registerForSeminar->payment_status = $paymentStatus;
                    $registerForSeminar->payment_type = $paymentType;
                    $registerForSeminar->save();
                }

                //  $authData = isset(getAuthData()->id) ? getAuthData()->id :null;
                $authData = getAuthData();
                if ($authData && isset($authData->user) && $authData->user->id) {
                    $authId = $authData->user->id;
                    $delegate->update(['created_by' => $authId]);
                }

                DB::commit();

                // TODO: Use job & queue to send welcome message
                // sendWelcomeMessageThroughWhatsappBot($this->visitor['mobile_number'], 'visitor');

                session()->flash('success', 'Seminar/Workshop Registered Successfully');
                return redirect()->to(route('delegate-registration', isset($this->eventId) ? ['eventId' => $this->eventId] : ''));
            }
        } catch (\Exception $e) {
            DB::rollBack();

            session()->flash('error', $e->getMessage());
        }
    }

    public function mount(Request $request)
    {
        $this->eventId = request()->eventId;
        $currentDate = now();

        $this->events = Event::where('start_date', '>=', now()->format('Y-m-d'))
            ->orWhere('end_date', '>', now()->format('Y-m-d'))
            ->pluck('title', 'id');

        $this->countries = getCountries();
        $this->delegate['event_id'] = isset($this->eventId) ? $this->eventId : '';
        $this->getCurrentEventSeminars();
    }

    public function pincode()
    {
        if (strtolower($this->delegate['country']) == 'india' && isset($this->delegate['pincode'])) {
            // You can call getPincodeData directly here or adjust it as needed.
            $pincodeData = getPincodeData($this->delegate['pincode']);

            if ($pincodeData['state'] === null && $pincodeData['city'] === null) {
                $this->addError("delegate.pincode", "Pincode is not Exists");
            } else {
                $this->resetErrorBag('delegate.pincode');
                $this->delegate['state'] = $pincodeData['state'];
                $this->delegate['city'] = $pincodeData['city'];
            }
        }
    }

    public function clearAddressFields()
    {
        $this->delegate['city'] = null;
        $this->delegate['state'] = null;
        $this->delegate['pincode'] = null;
    }

    public function getProfileName()
    {
        $this->profile_name = str_replace(' ', '', $this->delegate['name']); // Replace spaces with underscores or any other character
        $this->suggestedValue = $this->profile_name . rand(0, 9999);
        $existingDelegate = Visitor::where('username', $this->suggestedValue)->first();

        if ($existingDelegate) {
            $this->suggestedValue = $this->profile_name . rand(10000, 99999);
        }

        $this->delegate['username'] = $this->suggestedValue; // Set the generated username in delegate array
    }
    public function getCurrentEventSeminars()
    {
        $selectedEventId = $this->delegate['event_id'];

        if ($selectedEventId) {
            $this->currentEventSeminars = Seminar::where('event_id', $selectedEventId)
                ->where('is_active', 1)
                ->get();
        }
        $this->dispatch('changeEvent', $this->currentEventSeminars);
    }

    public function render()
    {
        $seminarIds = $this->delegate['seminars_to_attend'];
        $seminars = Seminar::whereIn('id', $seminarIds)->get();
        $this->delegate['amount'] = $seminars->sum('amount');

        if (auth()->guard('visitor')->check() || auth()->guard('web')->check()) {
            return view('livewire.delegate-handler')
                ->layout('layouts.admin');
        }
    }
}
