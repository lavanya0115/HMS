<?php

namespace App\Http\Livewire;

use App\Models\Event;
use Carbon\Carbon;
use App\Models\Visitor;
// use Illuminate\Support\Facades\Auth;
use App\Models\Exhibitor;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Log;
use Spatie\Activitylog\Models\Activity;
use App\Http\Controllers\OneSignalController;

class MyAppointments extends Component
{
    use WithPagination;

    public $paginationTheme = 'bootstrap';

    public $perPage = 10;

    public $toggleContent = false;

    #[Url(as: 's')]
    public $search;
    #[Url(as: 'df')]
    public $dateFilter;

    public $sortApplied = false;

    public $sortcolumnName = 'scheduled_at';

    public $sortDirection = 'desc';

    public $sortByUserOrder, $sortByUserColumnName;

    public $appointmentId, $eventId, $visitorId, $exhibitorId;

    public $feedback, $feedbackType;
    public $appointmentStatus;

    public $activities, $modelClass;
    public $currentEvent;

    protected $listeners = [
        'showAlertListener' => 'showAlert',
    ];

    public function showAlert($status, $message)
    {
        $this->dispatch('closeModal');
        session()->flash($status, $message);
    }
    public function sortBy($columnName, $order)
    {
        $this->sortDirection = $order;
        $this->sortcolumnName = $columnName;
    }

    public function sortByUsers($columnName, $order)
    {
        $this->sortByUserOrder = $order;
        $this->sortByUserColumnName = $columnName;
    }

    public function resetField()
    {
        return $this->search = null;
    }

    public function resetDate()
    {
        return $this->dateFilter = null;
    }

    public function toggleBtn()
    {
        $this->toggleContent = !$this->toggleContent;
    }

    public function getAppointmentId($appointmentId)
    {
        $this->appointmentId = $appointmentId;
        $this->dispatch('openModel');
        $this->dispatch('getAppointmentIdListener', $this->appointmentId);
    }
    public function exhibitorAppointmentId($appointmentId, $status)
    {
        $this->appointmentId = $appointmentId;
        $this->feedbackType = $status;
    }
    public function appointmentComplete()
    {
        $appointment = Appointment::find($this->appointmentId);
        $meta = $appointment->_meta ?? null;

        if ($this->feedbackType == 'completed') {
            $this->validate([
                'feedback' => 'required',
            ]);
        }
        $feedbackData = [
            'timestamp' => now()->format('Y-m-d H:i:s'),
            'message' => $this->feedback,

        ];

        $isVisitor = auth()->guard('visitor')->check();
        $isExhibitor = auth()->guard('exhibitor')->check();
        $isOrganizer = isOrganizer();

        if ($isVisitor && !empty($feedbackData['message'])) {
            $meta['visitor_feedback'] = $feedbackData;
        } else if ($isExhibitor && !empty($feedbackData['message'])) {
            $meta['exhibitor_feedback'] = $feedbackData;
        }

        $appointment->status = 'completed';
        $appointment->_meta = $meta;
        $appointment->completed_at = Carbon::now();
        $appointment->completable_id = auth()->user()->id;
        $appointment->completable_type = get_class(auth()->user());
        $appointment->save();

        $this->closeFeedbackModal();

        $visitorName = $appointment->visitor->name ?? '';
        $exhibitorName = $appointment->exhibitor->name ?? '';
        $scheduledAt = $appointment->scheduled_at ?? '';
        $scheduledAt = Carbon::parse($scheduledAt)->format('d M Y h:i A');
        $receiverEmailData = [
            'receiverEmail' => $isVisitor ? $appointment->exhibitor->email : $appointment->visitor->email,
            'appointmentId' => $this->appointmentId,
        ];
        $senderEmailData = [
            'senderEmail' => $isVisitor ? $appointment->visitor->email : $appointment->exhibitor->email,
            'appointmentId' => $this->appointmentId,
        ];

        $mobileNumber = $isVisitor ? $appointment->exhibitor?->mobile_number : $appointment->visitor?->mobile_number;
        $contactPerson = $appointment->exhibitor?->exhibitorContact?->contact_number;
        $message = 'Appointment completed with ' . $exhibitorName . ' at ' . $this->currentEvent->title;
        $oneSignal = new OneSignalController();

        if (($isOrganizer || $isExhibitor) && $this->feedbackType == 'completed') {
            sendAppointmentStatusChangeNotification($appointment->visitor->mobile_number, 'visitor', [
                'senderName' => $exhibitorName,
                'receiverName' => $visitorName,
                'status' => ucfirst($appointment->status),
                'scheduledAt' => Carbon::parse($scheduledAt)->toDayDateTimeString(),
            ]);
            sendAppointmentStatusChangeEmail($receiverEmailData, [
                'senderName' => $exhibitorName,
                'receiverName' => $visitorName,
                'status' => ucfirst($appointment->status),
                'scheduledAt' => Carbon::parse($scheduledAt)->toDayDateTimeString(),
            ]);
            sendAppointmentStatusChangeEmail($senderEmailData, [
                'senderName' => $exhibitorName,
                'receiverName' => $visitorName,
                'status' => ucfirst($appointment->status),
                'scheduledAt' => Carbon::parse($scheduledAt)->toDayDateTimeString(),
            ]);

            $oneSignal->sendNotification(
                'Appointment Completed',
                $message,
                $mobileNumber
            );
        }

        if (($isOrganizer || $isVisitor) && $this->feedbackType == 'completed') {

            $exhibitor = Exhibitor::with('exhibitorContact')->find($appointment->exhibitor->id);
            $exhibitorContactPersonMobileNumber = $exhibitor->exhibitorContact->contact_number ?? null;
            $exhibitorMobileNumber = $exhibitor->mobile_number ?? null;
            sendAppointmentStatusChangeNotification($exhibitorContactPersonMobileNumber, 'exhibitor', [
                'senderName' => $visitorName,
                'receiverName' => $exhibitorName,
                'status' => ucfirst($appointment->status),
                'scheduledAt' => Carbon::parse($scheduledAt)->toDayDateTimeString(),
            ]);
            sendAppointmentStatusChangeNotification($exhibitorMobileNumber, 'exhibitor', [
                'senderName' => $visitorName,
                'receiverName' => $exhibitorName,
                'status' => ucfirst($appointment->status),
                'scheduledAt' => Carbon::parse($scheduledAt)->toDayDateTimeString(),
            ]);
            sendAppointmentStatusChangeEmail($receiverEmailData, [
                'senderName' => $visitorName,
                'receiverName' => $exhibitorName,
                'status' => ucfirst($appointment->status),
                'scheduledAt' => Carbon::parse($scheduledAt)->toDayDateTimeString(),
            ]);

            sendAppointmentStatusChangeEmail($senderEmailData, [
                'senderName' => $visitorName,
                'receiverName' => $exhibitorName,
                'status' => ucfirst($appointment->status),
                'scheduledAt' => Carbon::parse($scheduledAt)->toDayDateTimeString(),
            ]);

            $oneSignal->sendNotification(
                'Appointment Completed',
                $message,
                $contactPerson
            );
            $oneSignal->sendNotification(
                'Appointment Completed',
                $message,
                $mobileNumber
            );
        }

        $this->feedback = '';
        $isUpdated = $appointment->wasChanged('_meta');
        if ($isUpdated) {
            $this->showAlert('success', 'Updated successfully');
        }
    }
    public function closeFeedbackModal()
    {
        $this->resetErrorBag();
        $this->dispatch('closeFeedbackModal');
    }
    public function exhibitorAppointmentStatus($appoinmentId, $status)
    {
        $authUser = getAuthData();
        $appointment = Appointment::find($appoinmentId);
        $appointment->status = $status;
        if ($status == 'cancelled') {
            $appointment->cancelled_by = $authUser->id;
            $appointment->cancelled_at = Carbon::now();
            $appointment->cancelled_type = get_class($authUser);
        }

        $appointment->save();

        $visitorName = $appointment->visitor->name ?? '';
        $exhibitorName = $appointment->exhibitor->name ?? '';
        $scheduledAt = $appointment->scheduled_at ?? '';
        $scheduledAt = Carbon::parse($scheduledAt)->format('d M Y h:i A');

        $isVisitor = auth()->guard('visitor')->check();
        $isExhibitor = auth()->guard('exhibitor')->check();
        $isOrganizer = isOrganizer();
        $receiverEmailData = [
            'receiverEmail' => $isVisitor ? $appointment->exhibitor->email : $appointment->visitor->email,
            'appointmentId' => $appoinmentId,
        ];
        $senderEmailData = [
            'receiverEmail' => $isVisitor ? $appointment->visitor->email : $appointment->exhibitor->email,
            'appointmentId' => $appoinmentId,
        ];

        $mobileNumber = $isVisitor ? $appointment->exhibitor?->mobile_number : $appointment->visitor?->mobile_number;
        $contactPerson = $appointment->exhibitor?->exhibitorContact?->contact_number;
        $message = 'Appointment' . ' ' . $status . ' with ' . $exhibitorName . ' at ' . $this->currentEvent->title;
        $oneSignal = new OneSignalController();

        if ($isOrganizer || $isExhibitor) {
            sendAppointmentStatusChangeNotification($appointment->visitor->mobile_number, 'visitor', [
                'senderName' => $exhibitorName,
                'receiverName' => $visitorName,
                'status' => ucfirst($status),
                'scheduledAt' => Carbon::parse($scheduledAt)->toDayDateTimeString(),
            ]);
            sendAppointmentStatusChangeEmail($receiverEmailData, [
                'senderName' => $exhibitorName,
                'receiverName' => $visitorName,
                'status' => ucfirst($status),
                'scheduledAt' => Carbon::parse($scheduledAt)->toDayDateTimeString(),
            ]);

            sendAppointmentStatusChangeEmail($senderEmailData, [
                'senderName' => $exhibitorName,
                'receiverName' => $visitorName,
                'status' => ucfirst($status),
                'scheduledAt' => Carbon::parse($scheduledAt)->toDayDateTimeString(),
            ]);

            $oneSignal->sendNotification(
                'Appointment ' . ucfirst($status),
                $message,
                $mobileNumber
            );
        }

        if ($isOrganizer || $isVisitor) {
            $exhibitor = Exhibitor::with('exhibitorContact')->find($appointment->exhibitor->id);
            $exhibitorContactPersonMobileNumber = $exhibitor->exhibitorContact->contact_number ?? null;
            $exhibitorMobileNumber = $exhibitor->mobile_number ?? null;

            sendAppointmentStatusChangeNotification($exhibitorContactPersonMobileNumber, 'exhibitor', [
                'senderName' => $visitorName,
                'receiverName' => $exhibitorName,
                'status' => ucfirst($status),
                'scheduledAt' => Carbon::parse($scheduledAt)->toDayDateTimeString(),
            ]);
            sendAppointmentStatusChangeNotification($exhibitorMobileNumber, 'exhibitor', [
                'senderName' => $visitorName,
                'receiverName' => $exhibitorName,
                'status' => ucfirst($status),
                'scheduledAt' => Carbon::parse($scheduledAt)->toDayDateTimeString(),
            ]);
            sendAppointmentStatusChangeEmail($receiverEmailData, [
                'senderName' => $visitorName,
                'receiverName' => $exhibitorName,
                'status' => ucfirst($status),
                'scheduledAt' => Carbon::parse($scheduledAt)->toDayDateTimeString(),
            ]);

            sendAppointmentStatusChangeEmail($senderEmailData, [
                'senderName' => $visitorName,
                'receiverName' => $exhibitorName,
                'status' => ucfirst($status),
                'scheduledAt' => Carbon::parse($scheduledAt)->toDayDateTimeString(),
            ]);

            $oneSignal->sendNotification(
                'Appointment ' . $status,
                $message,
                $mobileNumber
            );
            $oneSignal->sendNotification(
                'Appointment ' . $status,
                $message,
                $contactPerson
            );
        }

        $this->showAlert('success', 'Appointment status updated successfully');
    }
    public function mount(Request $request)
    {
        $this->eventId = $request->eventId;
        $this->appointmentStatus = $request->appointmentStatus;
        $this->currentEvent = Event::find($this->eventId);
        $this->visitorId = auth()->guard('visitor')->check() ? auth()->guard('visitor')->user()->id : null;
        $this->exhibitorId = auth()->guard('exhibitor')->check() ? auth()->guard('exhibitor')->user()->id : null;
    }

    public function render()
    {

        $query = Appointment::query();

        if (auth()->guard('visitor')->check()) {
            $query->where('event_id', $this->eventId)
                ->where('visitor_id', $this->visitorId)
                ->when(isset($this->appointmentStatus), function ($status) {
                    $status->where('status', $this->appointmentStatus);
                });

            if ($this->search !== null) {
                $query->whereHas('exhibitor', function ($query) {
                    $query->where('name', 'like', '%' . $this->search . '%');
                });
            }

            if ($this->dateFilter !== null) {
                $query->whereDate('scheduled_at', $this->dateFilter);
            }

            if ($this->sortcolumnName === 'exhibitor_name') {
                $query->join('exhibitors', 'appointments.exhibitor_id', '=', 'exhibitors.id')
                    ->orderBy('exhibitors.name', $this->sortDirection)
                    ->select('appointments.*');
            }
        } elseif (auth()->guard('exhibitor')->check()) {
            $query->where('event_id', $this->eventId)
                ->where('exhibitor_id', $this->exhibitorId)
                ->when(isset($this->appointmentStatus), function ($status) {
                    $status->where('status', $this->appointmentStatus);
                });

            if ($this->search !== null) {
                $query->whereHas('visitor', function ($query) {
                    $query->where('name', 'like', '%' . $this->search . '%');
                });
            }

            if ($this->dateFilter !== null) {
                $query->whereDate('scheduled_at', $this->dateFilter);
            }

            if ($this->sortcolumnName === 'visitor_name') {
                $query->join('visitors', 'appointments.visitor_id', '=', 'visitors.id')
                    ->orderBy('visitors.name', $this->sortDirection)
                    ->select('appointments.*');
            }
        }

        // Apply sorting only if sort parameters are set
        if ($this->sortcolumnName && $this->sortDirection && !in_array($this->sortcolumnName, ['exhibitor_name', 'visitor_name'])) {
            $query->orderBy($this->sortcolumnName, $this->sortDirection);
        }

        $myappointments = $query->paginate($this->perPage);


        if (auth()->guard('visitor')->check()) {
            $this->modelClass = Visitor::class;
            $this->activities = Activity::where('log_name', 'appointment_log')
                ->where('causer_type', 'App\Models\Visitor')
                ->where('causer_id', $this->visitorId)
                ->orderBy('id', 'desc')->get();
        }

        if (auth()->guard('exhibitor')->check()) {
            $this->modelClass = Exhibitor::class;
            $this->activities = Activity::where('log_name', 'appointment_log')
                ->where('causer_type', 'App\Models\Exhibitor')
                ->where('causer_id', $this->exhibitorId)
                ->orderBy('id', 'desc')->get();
        }

        $updateCauser = Activity::orderBy('id', 'desc')->first();
        if ($updateCauser && $updateCauser->causer_type == null) {
            $updateCauser->update([
                'causer_id' => auth()->guard('visitor')->check() ? $this->visitorId : $this->exhibitorId,
                'causer_type' => $this->modelClass,
            ]);
        }

        return view('livewire.my-appointments', [
            'myappointments' => $myappointments,
            'activities' => $this->activities,
        ])->layout('layouts.admin');
    }

    public function generateICS($appointmentId)
    {
        $icsContent = generateICSFile($appointmentId);
        $fileName = 'medicall' . $appointmentId . '.ics';
        return response()->stream(
            function () use ($icsContent) {
                echo $icsContent;
            },
            200,
            [
                'Content-Type' => 'text/calendar; charset=utf-8',
                'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
            ]
        );
    }
    public function updated($propertyName)
    {
        if ($propertyName === 'search' || $propertyName === 'dateFilter') {
            $this->resetPage();
        }
    }
}
