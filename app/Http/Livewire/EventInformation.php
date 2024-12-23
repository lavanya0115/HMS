<?php

namespace App\Http\Livewire;

use App\Models\Appointment;
use App\Models\Event;
use App\Models\EventSeminarParticipant;
use App\Models\EventVisitor;
use App\Models\Exhibitor;
use App\Models\Seminar;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Livewire\Component;

class EventInformation extends Component
{
    public $event, $eventId, $exhibitorId, $visitorId, $isSalesPerson;

    public $scheduledCount, $rescheduledCount, $confirmedCount, $lapsedCount, $cancelledCount, $completedCount;

    public $appointmentCount, $mappedExhibitors;

    public $profileStatusValue = [], $profileExhibitorIds = [];
    public $seminarData = [];
    public $seminarId;
    public $seminars_to_attend = [];
    public $seminarCount;
    public $payment_option = 'register_and_pay_later';
    public $amount;

    #[On('exhibitorProfileValue')]
    public function statusValue($value)
    {
        $data = json_decode($value, true);
        if (is_array($data)) {
            if (isset($data['filledFields']) && $data['filledFields'] > 90) {
                $this->profileExhibitorIds[] = $data['exhibitorId'];
            }
        }
        $this->profileStatusValue[] = $data['exhibitorId'];
    }

    public function confirmAppointment($appointmentId)
    {
        try {
            $appointment = Appointment::find($appointmentId);
            if ($appointment) {
                $appointment->status = 'confirmed';
                $appointment->save();
                $isUpdated = $appointment->wasChanged('status');
                if ($isUpdated) {
                    sendAppointmentStatusChangeNotification($appointment->visitor->mobile_number, 'exhibitor', [
                        'senderName' => $appointment->exhibitor->name ?? '',
                        'receiverName' => $appointment->visitor->name ?? '',
                        'status' => ucfirst($appointment->status),
                        'scheduledAt' => Carbon::parse($appointment->scheduled_at)->toDayDateTimeString(),
                    ]);
                    session()->flash('success', 'Appointment Confirmed with ' . $appointment->visitor->name);
                    return;
                }
            }
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
            return;
        }
    }

    public function mount(Request $request)
    {

        $this->eventId = $request->eventId;
        $this->seminarCount = Seminar::where('event_id', $this->eventId)->where('is_active', 1)->count();
        $this->isSalesPerson = isSalesPerson();
        $this->seminarData = Seminar::where('event_id', $this->eventId)->where('is_active', 1)->get();
        $this->mappedExhibitors = mappedExhibitors($this->eventId);
        auth()->guard('exhibitor')->check() ?
            $this->exhibitorId = auth()->guard('exhibitor')->user()->id : '';
        auth()->guard('visitor')->check() ?
            $this->visitorId = auth()->guard('visitor')->user()->id : '';

        $this->event = Event::withCount('exhibitors', 'visitors')->where('id', $this->eventId)->first();

        if (auth()->guard('exhibitor')->check()) {
            $this->appointmentCount = Appointment::where('event_id', $this->eventId)->where('exhibitor_id', $this->exhibitorId)->count();
        } elseif (auth()->guard('visitor')->check()) {
            $this->appointmentCount = Appointment::where('event_id', $this->eventId)->where('visitor_id', $this->visitorId)->count();
        } else {
            $this->appointmentCount = Appointment::when($this->isSalesPerson, function ($query) {
                $query->whereIn('exhibitor_id', $this->mappedExhibitors);
            })->where('event_id', $this->eventId)->count();
        }
    }

    public function sendNotification($name, $mobileNumber)
    {
        try {
            $data = ['name' => $name, 'mobileNumber' => $mobileNumber];
            $message = sendIntimateNotification($data);
            if (!($message['status'] === 'success')) {
                return session()->flash('error', 'Notification send failed');
            }
            return session()->flash('success', 'Notification send to ' . $name . ' successfully');
        } catch (\Exception $e) {
            return session()->flash('error', $e->getMessage());
        }
    }

    // Livewire component method
    public function openAttendSeminarModal()
    {

        $this->dispatch('openAttendSeminarModal');
    }

    public function updateDelegateSeminars()
    {
        $this->validate([
            'seminars_to_attend' => 'required|array',
            'payment_option' => 'required'
        ], [
            'seminars_to_attend.required' => 'Please select at least one seminar',
            'payment_option.required' => 'Please select payment option'
        ]);

        try {

            $eventVisitor = EventVisitor::where('visitor_id', $this->visitorId)->where('event_id', $this->eventId)->first();

            $_meta = $eventVisitor->_meta ?? [];
            $_meta['converted_into_delegates'] = "yes";
            $eventVisitor->is_delegates = 1;
            // Store _meta as an associative array
            $eventVisitor->_meta = $_meta;
            $eventVisitor->save();

            foreach ($this->seminars_to_attend as $seminarId) {
                $seminarExists = EventSeminarParticipant::where('event_id', $this->eventId)->where('visitor_id', $this->visitorId)->where('seminar_id', $seminarId)->first();
                $seminarAmount = Seminar::find($seminarId)->amount;
                $paymentStatus = ($this->payment_option == 'register_and_pay') ? 'paid' : 'pay_later';
                $paymentType = ($this->payment_option == 'register_and_pay') ? 'online' : '';

                if (!$seminarExists) {
                    $registerForSeminar = new EventSeminarParticipant();
                    $registerForSeminar->event_id = $this->eventId;
                    $registerForSeminar->visitor_id = $this->visitorId;
                    $registerForSeminar->seminar_id = $seminarId;
                    $registerForSeminar->amount = $seminarAmount;
                    $registerForSeminar->payment_status = $paymentStatus;
                    $registerForSeminar->payment_type = $paymentType;
                    $registerForSeminar->save();
                    redirect()->route('event-informations', ['eventId' => $this->eventId])->with('success', 'Successfully Registered for seminar.');
                } else {
                    $seminarExists->payment_status = $paymentStatus;
                    $seminarExists->payment_type = $paymentType;
                    $seminarExists->save();
                    redirect()->route('event-informations', ['eventId' => $this->eventId])->with('success', 'Successfully Paid for this seminar.');
                }
            }

        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
            return;
        }
    }

    public function clearError()
    {
        $this->resetErrorBag();
        $this->payment_option = '';
        $this->amount = 0;
        $this->seminarId = null;
        $this->dispatch('closeModal');
    }

    public function confirmPayment($seminarId)
    {
        $this->seminarId = $seminarId;
        $this->payment_option = 'register_and_pay';
        $this->dispatch('showSeminars', $seminarId);
    }
    public function render()
    {

        $exhibitors = Exhibitor::when($this->isSalesPerson, function ($query) {
            $query->where('sales_person_id', getAuthData()->id);
        })->whereHas('eventExhibitors', function ($query) {
            $query->where('event_id', $this->eventId);
        })
            // ->where('event_id', $this->eventId)
            ->orderBy('id', 'desc')->get();

        $seminarIds = $this->seminars_to_attend;
        $seminars = Seminar::whereIn('id', $seminarIds)->get();
        $this->amount = $seminars->sum('amount');

        $registeredSeminars = EventSeminarParticipant::where('event_id', $this->eventId)->where('visitor_id', $this->visitorId)->get();
        $registeredSeminarIds = $registeredSeminars->pluck('seminar_id')->toArray();

        $pendingAppointments = Appointment::where('exhibitor_id', $this->exhibitorId)
            ->where('event_id', $this->eventId)
            ->whereIn('status', ['scheduled', 'rescheduled'])
            ->select('id', 'visitor_id', 'scheduled_at', 'notes')
            ->orderBy('id', 'desc')->paginate(10);

        if (auth()->guard('visitor')->check()) {
            $this->scheduledCount = Appointment::where('event_id', $this->eventId)->where('visitor_id', $this->visitorId)->where('status', 'scheduled')->count();
            $this->rescheduledCount = Appointment::where('event_id', $this->eventId)->where('visitor_id', $this->visitorId)->where('status', 'rescheduled')->count();
            $this->confirmedCount = Appointment::where('event_id', $this->eventId)->where('visitor_id', $this->visitorId)->where('status', 'confirmed')->count();
            $this->lapsedCount = Appointment::where('event_id', $this->eventId)->where('visitor_id', $this->visitorId)->where('status', 'no-show')->count();
            $this->cancelledCount = Appointment::where('event_id', $this->eventId)->where('visitor_id', $this->visitorId)->where('status', 'cancelled')->count();
            $this->completedCount = Appointment::where('event_id', $this->eventId)->where('visitor_id', $this->visitorId)->where('status', 'completed')->count();
        }
        if (auth()->guard('exhibitor')->check()) {
            $this->scheduledCount = Appointment::where('event_id', $this->eventId)->where('exhibitor_id', $this->exhibitorId)->where('status', 'scheduled')->count();
            $this->rescheduledCount = Appointment::where('event_id', $this->eventId)->where('exhibitor_id', $this->exhibitorId)->where('status', 'rescheduled')->count();
            $this->confirmedCount = Appointment::where('event_id', $this->eventId)->where('exhibitor_id', $this->exhibitorId)->where('status', 'confirmed')->count();
            $this->lapsedCount = Appointment::where('event_id', $this->eventId)->where('exhibitor_id', $this->exhibitorId)->where('status', 'no-show')->count();
            $this->cancelledCount = Appointment::where('event_id', $this->eventId)->where('exhibitor_id', $this->exhibitorId)->where('status', 'cancelled')->count();
            $this->completedCount = Appointment::where('event_id', $this->eventId)->where('exhibitor_id', $this->exhibitorId)->where('status', 'completed')->count();
        }
        // Fetch the data
        // $knownSources = EventVisitor::where('event_id', $this->eventId)
        //     ->where('known_source', '<>', '')
        //     ->selectRaw('known_source as normalized_source, COUNT(*) as count')
        //     ->groupBy('normalized_source')
        //     ->pluck('count', 'normalized_source')
        //     ->toArray();

        // $knownSourceData = getKnownSourceData();
        // $knownSourceValues = array_values($knownSourceData);
        // $knownSourceKeys = array_keys($knownSourceData);

        // $totalVisitors = EventVisitor::where('event_id', $this->eventId)
        //     ->count();

        // $knownSourcesPercentages = [];

        // // Ensure all known sources are mapped to their labels
        // foreach ($knownSources as $source => $count) {
        //     $selectedItemLabel = '';

        //     foreach ($knownSourceData as $knowSourceKey => $knowSourceLabel) {
        //         if ($knowSourceKey == $source || $knowSourceLabel == $source) {
        //             $selectedItemLabel = $knowSourceLabel;
        //             break;
        //         }
        //     }

        //     if ($selectedItemLabel) {
        //         if (isset($knownSourcesPercentages[$selectedItemLabel])) {
        //             $knownSourcesPercentages[$selectedItemLabel]['count'] += $count;
        //         } else {
        //             $knownSourcesPercentages[$selectedItemLabel] = [
        //                 'label' => $selectedItemLabel,
        //                 'count' => $count,
        //             ];
        //         }
        //     }
        // }

        // $knownSourcesPercentages = array_values($knownSourcesPercentages);

        // // Calculate percentages
        // foreach ($knownSourcesPercentages as $knownSourcesPercentageIndex => $knownSources) {
        //     $percentage = ($knownSources['count'] / $totalVisitors) * 100;
        //     $percentage = round($percentage, 0);
        //     $knownSourcesPercentages[$knownSourcesPercentageIndex]['percentage'] = $percentage;
        // }

        //    dd($knownSourcesPercentages);

        return view(
            'livewire.event-information',
            [
                'pendingAppointments' => $pendingAppointments,
                'scheduledCount' => $this->scheduledCount,
                'rescheduledCount' => $this->rescheduledCount,
                'confirmedCount' => $this->confirmedCount,
                'lapsedCount' => $this->lapsedCount,
                'canceledCount' => $this->cancelledCount,
                'exhibitors' => $exhibitors,
                'registeredSeminars' => $registeredSeminars,
                'registeredSeminarIds' => $registeredSeminarIds,
                // 'knownSources' => $knownSources,
                // 'knownSourcesPercentages' => $knownSourcesPercentages,

            ]
        )->layout('layouts.admin');
    }
}
