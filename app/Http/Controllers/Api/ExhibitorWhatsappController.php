<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Exhibitor;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ExhibitorWhatsappController extends Controller
{
    public function getConfirmedAppointmentsByMobile(Request $request)
    {
        $mobile = $request->input('mobile_number');
        $mobile = cleanWhatsappNumber($mobile);

        $exhibitor = Exhibitor::where('mobile_number', $mobile)
            ->orWhereHas('contact_persons', function ($query) use ($mobile) {
                $query->where('contact_number', $mobile);
            })
            ->first();

        Log::info('Exhibitor: ' . json_encode($exhibitor));

        if (!$exhibitor) {
            return response()->json([
                'status' => 'error',
                'message' => 'Appointment not found',
            ]);
        }

        $currentEventId = getCurrentEvent()->id ?? 0;
        $confirmedAppointments = Appointment::where('exhibitor_id', $exhibitor->id)
            ->where('event_id', $currentEventId)
            ->where('status', 'scheduled')
            ->with('visitor')
            ->with('eventExhibitorInfo')
            ->cursorPaginate(10);

        $formattedConfirmedAppointments = $confirmedAppointments->map(function ($appointment) {

            return [
                'appointment_id' => $appointment->id,
                'exhibitor_id' => $appointment->exhibitor_id,
                'visitor_id' => $appointment->visitor_id,
                'date_time' => $appointment->scheduled_at->format('d-m-Y h:i'),
                'visitor_name' => $appointment->visitor->name ?? '',
                'designation' => $appointment->visitor->designation ?? '',
                'organization' => $appointment->visitor->organization ?? '',
                'stall_no' => $appointment->eventExhibitorInfo->stall_no ?? '',
            ];
        });
        return response()->json([
            'status' => 'success',
            'message' => 'Successfully get the confirm appointments',
            'confirmed_appointments' => $formattedConfirmedAppointments,
            'nextPageCursorValue' => $this->getCursorValueFromGivenUrl($confirmedAppointments->nextPageUrl()),
            'nextPageUrl' => $confirmedAppointments->nextPageUrl(),
        ]);
    }

    public function getcompletedAppointmentsByMobile(Request $request)
    {
        $mobile = $request->input('mobile_number');
        $mobile = cleanWhatsappNumber($mobile);

        $exhibitor = Exhibitor::where('mobile_number', $mobile)
            ->orWhereHas('contact_persons', function ($query) use ($mobile) {
                $query->where('contact_number', $mobile);
            })
            ->first();

        if (!$exhibitor) {
            return response()->json([
                'status' => 'error',
                'message' => 'Appointment not found',
            ]);
        }

        $currentEventId = getCurrentEvent()->id ?? 0;
        $completedAppointments = Appointment::where('exhibitor_id', $exhibitor->id)
            ->where('event_id', $currentEventId)
            ->where('status', 'completed')
            ->with('visitor')
            ->with('eventExhibitorInfo')
            ->cursorPaginate(10);

        $formattedCompletedAppointments = $completedAppointments->map(function ($appointment) {

            return [
                'appointment_id' => $appointment->id,
                'exhibitor_id' => $appointment->exhibitor_id,
                'visitor_id' => $appointment->visitor_id,
                'date_time' => $appointment->scheduled_at->format('d-m-Y h:i'),
                'visitor_name' => $appointment->visitor->name ?? '',
                'designation' => $appointment->visitor->designation ?? '',
                'organization' => $appointment->visitor->organization ?? '',
                'stall_no' => $appointment->eventExhibitorInfo->stall_no ?? '',
            ];
        });
        return response()->json([
            'status' => 'success',
            'message' => 'Successfully get the completed appointments',
            'completed_appointments' => $formattedCompletedAppointments,
            'nextPageCursorValue' => $this->getCursorValueFromGivenUrl($completedAppointments->nextPageUrl()),
            'nextPageUrl' => $completedAppointments->nextPageUrl(),
        ]);
    }

    public function getCursorValueFromGivenUrl($givenUrl)
    {
        $parsedUrl = (string) $givenUrl;
        $queryString = parse_url($parsedUrl, PHP_URL_QUERY);
        $params = [];
        parse_str($queryString, $params);
        $cursorValue = $params['cursor'] ?? '';
        return $cursorValue;
    }

    protected function updateAppointmentStatus($appointmentId, $action)
    {
        $appointment = Appointment::find($appointmentId);

        if (!$appointment) {
            return response()->json([
                'status' => 'error',
                'message' => 'Appointment not found',
            ]);
        }

        $status = $action === 'confirm' ? 'confirmed' : 'cancelled';
        $appointment->status = $status;
        $appointment->save();

        $visitorName = $appointment->visitor->name ?? '';
        $exhibitorName = $appointment->exhibitor->name ?? '';
        $scheduledAt = $appointment->scheduled_at->format('d M Y h:i A');

        $isVisitor = auth()->guard('visitor')->check();
        $isExhibitor = auth()->guard('exhibitor')->check();
        $isOrganizer = isOrganizer();

        $receiverEmailData = [
            'receiverEmail' => $isVisitor ? $appointment->exhibitor->email : $appointment->visitor->email,
            'appointmentId' => $appointmentId,
        ];
        $senderEmailData = [
            'receiverEmail' => $isVisitor ? $appointment->visitor->email : $appointment->exhibitor->email,
            'appointmentId' => $appointmentId,
        ];

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
        }

        $eventId = $appointment->event_id;
        $exhibitorId = $appointment->exhibitor_id;
        $exhibitorName = $appointment->exhibitor->name ?? '';
        $dateTime = $appointment->scheduled_at->format('d-m-Y h:i');
        $stallNo = $appointment->eventExhibitorInfo->stall_no ?? '';

        return response()->json([
            'status' => 'success',
            'message' => "Appointment {$status} successfully",
            'event_id' => $eventId,
            'date_time' => $dateTime,
            'exhibitor_id' => $exhibitorId,
            'exhibitor_name' => $exhibitorName,
            'stall_no' => $stallNo,
        ]);
    }

    public function confirmAppointment($appointmentId)
    {

        return $this->updateAppointmentStatus($appointmentId, 'confirm');
    }

    public function cancelAppointment($appointmentId)
    {
        return $this->updateAppointmentStatus($appointmentId, 'cancel');
    }

    public function confirmWhatsappAppointment(Request $request)
    {
        $appointmentId = $request->input('appointmentId');
        return $this->updateAppointmentStatus($appointmentId, 'confirm');
    }

    // public function cancelWhatsappAppointment(Request $request)
    // {
    //     $appointmentId = $request->input('appointmentId');
    //     return $this->updateAppointmentStatus($appointmentId, 'cancel');
    //     $visitorName = $appointment->visitor->name ?? '';
    //     $exhibitorName = $appointment->exhibitor->name ?? '';
    //     $scheduledAt = $appointment->scheduled_at ?? '';
    //     $scheduledAt = Carbon::parse($scheduledAt)->format('d M Y h:i A');

    //     $isVisitor = auth()->guard('visitor')->check();
    //     $isExhibitor = auth()->guard('exhibitor')->check();
    //     $isOrganizer = isOrganizer();
    //     $receiverEmailData = [
    //         'receiverEmail' => $isVisitor ? $appointment->exhibitor->email : $appointment->visitor->email,
    //         'appointmentId' => $appoinmentId,
    //     ];
    //     $senderEmailData = [
    //         'receiverEmail' => $isVisitor ? $appointment->visitor->email : $appointment->exhibitor->email,
    //         'appointmentId' => $appoinmentId,
    //     ];

    //     if ($isOrganizer || $isExhibitor) {
    //         sendAppointmentStatusChangeNotification($appointment->visitor->mobile_number, 'visitor', [
    //             'senderName' => $exhibitorName,
    //             'receiverName' => $visitorName,
    //             'status' => ucfirst($status),
    //             'scheduledAt' => Carbon::parse($scheduledAt)->toDayDateTimeString(),
    //         ]);
    //         sendAppointmentStatusChangeEmail($receiverEmailData, [
    //             'senderName' => $exhibitorName,
    //             'receiverName' => $visitorName,
    //             'status' => ucfirst($status),
    //             'scheduledAt' => Carbon::parse($scheduledAt)->toDayDateTimeString(),
    //         ]);

    //         sendAppointmentStatusChangeEmail($senderEmailData, [
    //             'senderName' => $exhibitorName,
    //             'receiverName' => $visitorName,
    //             'status' => ucfirst($status),
    //             'scheduledAt' => Carbon::parse($scheduledAt)->toDayDateTimeString(),
    //         ]);
    //     }

    //     if ($isOrganizer || $isVisitor) {
    //         $exhibitor = Exhibitor::with('exhibitorContact')->find($appointment->exhibitor->id);
    //         $exhibitorContactPersonMobileNumber = $exhibitor->exhibitorContact->contact_number ?? null;
    //         $exhibitorMobileNumber = $exhibitor->mobile_number ?? null;

    //         sendAppointmentStatusChangeNotification($exhibitorContactPersonMobileNumber, 'exhibitor', [
    //             'senderName' => $visitorName,
    //             'receiverName' => $exhibitorName,
    //             'status' => ucfirst($status),
    //             'scheduledAt' => Carbon::parse($scheduledAt)->toDayDateTimeString(),
    //         ]);
    //         sendAppointmentStatusChangeNotification($exhibitorMobileNumber, 'exhibitor', [
    //             'senderName' => $visitorName,
    //             'receiverName' => $exhibitorName,
    //             'status' => ucfirst($status),
    //             'scheduledAt' => Carbon::parse($scheduledAt)->toDayDateTimeString(),
    //         ]);
    //         sendAppointmentStatusChangeEmail($receiverEmailData, [
    //             'senderName' => $visitorName,
    //             'receiverName' => $exhibitorName,
    //             'status' => ucfirst($status),
    //             'scheduledAt' => Carbon::parse($scheduledAt)->toDayDateTimeString(),
    //         ]);

    //         sendAppointmentStatusChangeEmail($senderEmailData, [
    //             'senderName' => $visitorName,
    //             'receiverName' => $exhibitorName,
    //             'status' => ucfirst($status),
    //             'scheduledAt' => Carbon::parse($scheduledAt)->toDayDateTimeString(),
    //         ]);
    //     }

    //     $this->showAlert('success', 'Appointment status updated successfully');
    // }
    public function cancelWhatsappAppointment(Request $request)
    {
        $appointmentId = $request->input('appointmentId');
        $appointment = Appointment::find($appointmentId);

        if (!$appointment) {
            return response()->json([
                'status' => 'error',
                'message' => 'Appointment not found',
            ]);
        }

        $visitorName = $appointment->visitor->name ?? '';
        $exhibitorName = $appointment->exhibitor->name ?? '';
        $scheduledAt = $appointment->scheduled_at ?? '';
        $scheduledAt = Carbon::parse($scheduledAt)->format('d M Y h:i A');

        $isVisitor = auth()->guard('visitor')->check();
        $isExhibitor = auth()->guard('exhibitor')->check();
        $isOrganizer = isOrganizer();

        $receiverEmailData = [
            'receiverEmail' => $isVisitor ? $appointment->exhibitor->email : $appointment->visitor->email,
            'appointmentId' => $appointmentId,
        ];
        $senderEmailData = [
            'receiverEmail' => $isVisitor ? $appointment->visitor->email : $appointment->exhibitor->email,
            'appointmentId' => $appointmentId,
        ];

        if ($isOrganizer || $isExhibitor) {
            sendAppointmentStatusChangeNotification($appointment->visitor->mobile_number, 'visitor', [
                'senderName' => $exhibitorName,
                'receiverName' => $visitorName,
                'status' => 'cancel',
                'scheduledAt' => $scheduledAt,
            ]);
            sendAppointmentStatusChangeEmail($receiverEmailData, [
                'senderName' => $exhibitorName,
                'receiverName' => $visitorName,
                'status' => 'cancel',
                'scheduledAt' => $scheduledAt,
            ]);

            sendAppointmentStatusChangeEmail($senderEmailData, [
                'senderName' => $exhibitorName,
                'receiverName' => $visitorName,
                'status' => 'cancel',
                'scheduledAt' => $scheduledAt,
            ]);
        }

        if ($isOrganizer || $isVisitor) {
            $exhibitor = Exhibitor::with('exhibitorContact')->find($appointment->exhibitor->id);
            $exhibitorContactPersonMobileNumber = $exhibitor->exhibitorContact->contact_number ?? null;
            $exhibitorMobileNumber = $exhibitor->mobile_number ?? null;

            sendAppointmentStatusChangeNotification($exhibitorContactPersonMobileNumber, 'exhibitor', [
                'senderName' => $visitorName,
                'receiverName' => $exhibitorName,
                'status' => 'cancel',
                'scheduledAt' => $scheduledAt,
            ]);
            sendAppointmentStatusChangeNotification($exhibitorMobileNumber, 'exhibitor', [
                'senderName' => $visitorName,
                'receiverName' => $exhibitorName,
                'status' => 'cancel',
                'scheduledAt' => $scheduledAt,
            ]);
            sendAppointmentStatusChangeEmail($receiverEmailData, [
                'senderName' => $visitorName,
                'receiverName' => $exhibitorName,
                'status' => 'cancel',
                'scheduledAt' => $scheduledAt,
            ]);

            sendAppointmentStatusChangeEmail($senderEmailData, [
                'senderName' => $visitorName,
                'receiverName' => $exhibitorName,
                'status' => 'cancel',
                'scheduledAt' => $scheduledAt,
            ]);
        }

        return $this->updateAppointmentStatus($appointmentId, 'cancel');
    }

    public function rescheduleAppointment($appointmentId, Request $request)
    {

        $newAppointmentDate = $request->new_appointment_date;
        $newAppointmentTime = $request->new_appointment_time;
        $appointment = Appointment::find($appointmentId);

        if (!$appointment) {
            return response()->json([
                'status' => 'error',
                'type' => 'APPOINTMENT_NOT_FOUND',
                'message' => 'Appointment not found.',
            ]);
        }

        $newAppointmentTimeTimestamp = strtotime($newAppointmentTime);
        $startTimeTimestamp = strtotime('10:00 AM');
        $endTimeTimestamp = strtotime('6:00 PM');

        if ($newAppointmentTimeTimestamp < $startTimeTimestamp || $newAppointmentTimeTimestamp > $endTimeTimestamp) {
            return response()->json([
                'status' => 'error',
                'type' => 'INVALID_APPOINTMENT_TIME',
                'message' => 'Invalid appointment time. Please provide a valid time between 10 AM and 6 PM.',
            ]);
        }

        $eventStartDate = strtotime(date('Y-m-d', strtotime($appointment->event->start_date)));
        $eventEndDate = strtotime(date('Y-m-d', strtotime($appointment->event->end_date)));
        $newAppointmentDateTimestamp = strtotime(date('Y-m-d', strtotime($newAppointmentDate)));

        if ($newAppointmentDateTimestamp < $eventStartDate || $newAppointmentDateTimestamp > $eventEndDate) {
            return response()->json([
                'status' => 'error',
                'type' => 'INVALID_APPOINTMENT_DATE',
                'message' => 'Invalid appointment date. Please provide a date within the event dates.',
            ]);
        }

        $appointment->status = 'rescheduled';
        $appointment->scheduled_at = date('Y-m-d H:i:s', strtotime($newAppointmentDate . " " . $newAppointmentTime));
        $appointment->save();

        return response()->json([
            'status' => 'success',
            'type' => 'APPOINTMENT_RESCHEDULED',
            'message' => 'Appointment rescheduled successfully.',
            'appointment' => $appointment,
        ]);
    }
    public function getcompletedAppointments(Request $request)
    {
        $mobile = $request->input('mobile_number');
        $mobile = cleanWhatsappNumber($mobile);

        $exhibitor = Exhibitor::where('mobile_number', $mobile)
            ->orWhereHas('contact_persons', function ($query) use ($mobile) {
                $query->where('contact_number', $mobile);
            })
            ->first();

        if (!$exhibitor) {
            return response()->json([
                'status' => 'error',
                'message' => 'Exhibitor not found',
            ]);
        }

        $currentEventId = getCurrentEvent()->id ?? 0;
        $threeHoursAgo = now()->subHours(3);
        $completedAppointments = Appointment::where('exhibitor_id', $exhibitor->id)
            ->where('event_id', $currentEventId)
            ->where('status', 'completed')
            ->where('completed_at', '<=', $threeHoursAgo)
            ->with('visitor')
            ->with('eventExhibitorInfo')
            ->cursorPaginate(10);

        $formattedCompletedAppointments = $completedAppointments->map(function ($appointment) {
            return [
                'appointment_id' => $appointment->id,
                'exhibitor_id' => $appointment->exhibitor_id,
                'visitor_id' => $appointment->visitor_id,
                'date_time' => $appointment->completed_at->format('d-m-Y h:i'),
                'visitor_name' => $appointment->visitor->name ?? '',
                'designation' => $appointment->visitor->designation ?? '',
                'organization' => $appointment->visitor->organization ?? '',
                'stall_no' => $appointment->eventExhibitorInfo->stall_no ?? '',
            ];
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Successfully retrieved the completed appointments',
            'completed_appointments' => $formattedCompletedAppointments,
            'nextPageCursorValue' => $this->getCursorValueFromGivenUrl($completedAppointments->nextPageUrl()),
            'nextPageUrl' => $completedAppointments->nextPageUrl(),
        ]);
    }

}
