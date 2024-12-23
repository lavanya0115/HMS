<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Event;
use Carbon\Carbon;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\OneSignalController;

class AppointmentController extends Controller
{
    public function showAppointments(Request $request)
    {
        $eventId = $request->eventId;
        if (!$eventId) {
            return response()->json([
                'status' => 'error',
                'message' => 'Event Id Not Found',
            ]);
        }
        $event = Event::find($eventId);
        if (!$event) {
            return response()->json([
                'status' => 'error',
                'message' => 'Event Not Found',
            ]);
        }
        $authId = auth()->user()->id;
        if (!$authId) {
            return response()->json([
                'status' => 'error',
                'message' => 'Exhibitor Id Not Found',
            ]);
        }
        try {

            $appointments = Appointment::where('event_id', $eventId)
                ->where('exhibitor_id', $authId)->orderBy('scheduled_at', 'desc')
                ->get();

            if (!(isset($appointments) && count($appointments) > 0)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'There is No Appointments Available',
                ]);
            }

            $formattedAppointments = $appointments->map(function ($appointment) {
                return [
                    'appointment_id' => $appointment->id,
                    'event_id' => $appointment->event?->id ?? '',
                    'event_title' => $appointment->event?->title ?? '',
                    'visitor_id' => $appointment->visitor?->id ?? '',
                    'visitor_name' => $appointment->visitor?->name ?? '',
                    'visitor_organization' => $appointment->visitor?->organization ?? '',
                    'visitor_designation' => $appointment->visitor?->designation ?? '',
                    'visitor_city' => $appointment->visitor?->address?->city ?? '',
                    'visitor_logo' => !empty($appointment->visitor?->_meta['logo']) ? asset('storage/' . ($appointment->visitor?->_meta['logo'])) : '',
                    'exhibitor_id' => $appointment->exhibitor?->id ?? '',
                    'exhibitor_name' => $appointment->exhibitor?->name ?? '',
                    'exhibitor_designation' => $appointment->exhibitor?->exhibitorContact?->designation ?? '',
                    'exhibitor_city' => $appointment->exhibitor?->address?->city ?? '',
                    'exhibitor_feedback_message' => $appointment->_meta['exhibitor_feedback']['message'] ?? '',
                    'exhibitor_feedback_timestamp' => $appointment->_meta['exhibitor_feedback']['timestamp'] ?? '',
                    'visitor_feedback_message' => $appointment->_meta['visitor_feedback']->message ?? '',
                    'visitor_feedback_timestamp' => $appointment->_meta['visitor_feedback']->timestamp ?? '',
                    'scheduled_on' => $appointment->scheduled_at->isoFormat('llll') ?? '',
                    'date_time' => $appointment->scheduled_at->format('Y-m-d H:i:s'),
                    'status' => ucfirst($appointment->status) ?? '',
                    'notes' => $appointment->notes ?? '',

                ];
            });

            return response()->json(['status' => 'success', 'data' => $formattedAppointments]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function statusUpdate(Request $request, OneSignalController $oneSignal)
    {
        $appointmentId = $request->appointmentId;
        $status = $request->status;
        $eventId = $request->eventId;
        $exhibitorId = auth()->user()->id;
        $date = $request->date;
        $time = $request->time;
        $scheduledAt = $date . $time;
        $exhibitorFeedbackMessage = $request->feedback;

        if (!$appointmentId) {
            return response()->json([
                'status' => 'error',
                'message' => 'Appointment Id Missing',
            ]);
        }
        if (!$status) {
            return response()->json([
                'status' => 'error',
                'message' => 'Status is Missing',
            ]);
        }
        if (!$eventId) {
            return response()->json([
                'status' => 'error',
                'message' => 'Event Id is Missing',
            ]);
        }
        if (!$exhibitorId) {
            return response()->json([
                'status' => 'error',
                'message' => 'Exhibitor Id is Missing',
            ]);
        }

        if ($status === 'completed' && !$exhibitorFeedbackMessage) {
            return response()->json([
                'status' => 'error',
                'message' => 'Feedback is required',
            ]);
        }

        try {
            $event = Event::find($eventId);
            if (!$event) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Event not found',
                ]);
            }
            $appointments = Appointment::find($appointmentId);
            $currentEventId = $appointments['event_id'] == $eventId;
            $currentExhibitorId = $appointments['exhibitor_id'] == $exhibitorId;

            $receiverEmailData = [
                'receiverEmail' => $appointments->visitor?->email,
                'appointmentId' => $appointmentId,
            ];

            $senderEmailData = [
                'receiverEmail' => $appointments->exhibitor?->email,
                'appointmentId' => $appointmentId,
            ];

            $visitorMobileNo = $appointments->visitor?->mobile_number;
            $message = 'Appointment' . ' ' . $status . ' with ' . $appointments->exhibitor?->name . ' at ' . $event->title;

            if ($currentEventId && $currentExhibitorId) {
                if ($status === 'rescheduled') {
                    $startDate = new DateTime($event->start_date);
                    $endDate = new DateTime($event->end_date);

                    $validation = Validator::make(
                        $request->all(),
                        [
                            'date' => "required|date_format:Y-m-d|after_or_equal:{$startDate->format('Y-m-d')}|before_or_equal:{$endDate->format('Y-m-d')}",
                            'time' => 'required|date_format:H:i|after_or_equal:10:00|before_or_equal:18:00',
                        ]
                    );

                    if ($validation->fails()) {
                        return response()->json([
                            'status' => 'error',
                            'message' => 'The given date and time is not valid',
                            'errors' => $validation->errors()->all(),
                        ]);
                    }

                    $appointments->update([
                        'scheduled_at' => $scheduledAt,
                        'status' => $status,
                        'updated_by' => $exhibitorId,
                        'updated_type' => get_class(auth()->user()),
                    ]);

                    sendAppointmentStatusChangeEmail($receiverEmailData, [
                        'senderName' => $appointments->exhibitor->name,
                        'receiverName' => $appointments->visitor->name,
                        'status' => ucfirst($status),
                        'scheduledAt' => Carbon::parse($scheduledAt)->toDayDateTimeString(),
                    ]);
                    sendAppointmentStatusChangeEmail($senderEmailData, [
                        'senderName' => $appointments->exhibitor->name,
                        'receiverName' => $appointments->visitor->name,
                        'status' => ucfirst($status),
                        'scheduledAt' => Carbon::parse($scheduledAt)->toDayDateTimeString(),
                    ]);

                    $oneSignal->sendNotification(
                        'Appointment Rescheduled',
                        $message,
                        $visitorMobileNo
                    );

                } elseif ($status === 'completed') {

                    $exhibitorFeedback = [
                        "message" => $exhibitorFeedbackMessage,
                        "timestamp" => now()->format('Y-m-d H:i:s'),
                    ];
                    $meta = $appointments->_meta;
                    $meta['exhibitor_feedback'] = $exhibitorFeedback;
                    $appointments->update([
                        '_meta' => $meta,
                        'status' => $status,
                        'completed_at' => Carbon::now(),
                        'completable_id' => auth()->user()->id,
                        'completable_type' => get_class(auth()->user()),

                    ]);

                    $isUpdated = $appointments->wasChanged('_meta', 'status');
                    if ($isUpdated) {
                        sendAppointmentStatusChangeNotification($appointments->visitor->mobile_number, 'visitor', [
                            'senderName' => $appointments->exhibitor->name,
                            'receiverName' => $appointments->visitor->name,
                            'status' => ucfirst($status),
                            'scheduledAt' => Carbon::parse($scheduledAt)->toDayDateTimeString(),
                        ]);
                        sendAppointmentStatusChangeEmail($receiverEmailData, [
                            'senderName' => $appointments->exhibitor->name,
                            'receiverName' => $appointments->visitor->name,
                            'status' => ucfirst($status),
                            'scheduledAt' => Carbon::parse($scheduledAt)->toDayDateTimeString(),
                        ]);
                        sendAppointmentStatusChangeEmail($senderEmailData, [
                            'senderName' => $appointments->exhibitor->name,
                            'receiverName' => $appointments->visitor->name,
                            'status' => ucfirst($status),
                            'scheduledAt' => Carbon::parse($scheduledAt)->toDayDateTimeString(),
                        ]);

                        $oneSignal->sendNotification(
                            'Appointment Completed',
                            $message,
                            $visitorMobileNo
                        );
                    }
                } elseif (in_array($status, ['confirmed', 'cancelled', 'no-show'])) {
                    $appointments->status = $status;
                    if ($status == 'cancelled') {
                        $appointments->cancelled_at = Carbon::now();
                        $appointments->cancelled_by = $exhibitorId;
                        $appointments->cancelled_type = get_class(auth()->user());
                    }
                    $appointments->save();

                    sendAppointmentStatusChangeNotification($appointments->visitor->mobile_number, 'visitor', [
                        'senderName' => $appointments->exhibitor->name,
                        'receiverName' => $appointments->visitor->name,
                        'status' => ucfirst($status),
                        'scheduledAt' => Carbon::parse($scheduledAt)->toDayDateTimeString(),
                    ]);
                    sendAppointmentStatusChangeEmail($receiverEmailData, [
                        'senderName' => $appointments->exhibitor->name,
                        'receiverName' => $appointments->visitor->name,
                        'status' => ucfirst($status),
                        'scheduledAt' => Carbon::parse($scheduledAt)->toDayDateTimeString(),
                    ]);
                    sendAppointmentStatusChangeEmail($senderEmailData, [
                        'senderName' => $appointments->exhibitor->name,
                        'receiverName' => $appointments->visitor->name,
                        'status' => ucfirst($status),
                        'scheduledAt' => Carbon::parse($scheduledAt)->toDayDateTimeString(),
                    ]);
                    $oneSignal->sendNotification(
                        'Appointment ' . $status,
                        $message,
                        $visitorMobileNo
                    );
                } else {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Check the status ' . $status . ' does not exist',
                    ]);
                }

                $formattedAppointments = [
                    'appointment_id' => $appointments->id,
                    'event_id' => $appointments->event->id ?? '',
                    'event_title' => $appointments->event->title ?? '',
                    'visitor_id' => $appointments->visitor->id ?? '',
                    'visitor_name' => $appointments->visitor->name ?? '',
                    'visitor_organization' => $appointments->visitor->organization ?? '',
                    'visitor_designation' => $appointments->visitor->designation ?? '',
                    'visitor_city' => $appointments->visitor->address->city ?? '',
                    'exhibitor_id' => $appointments->exhibitor->id ?? '',
                    'exhibitor_name' => $appointments->exhibitor->name ?? '',
                    'exhibitor_designation' => $appointments->exhibitor->exhibitorContact->designation ?? '',
                    'exhibitor_city' => $appointments->exhibitor->address->city ?? '',
                    'exhibitor_feedback_message' => $appointments->_meta['exhibitor_feedback']['message'] ?? '',
                    'exhibitor_feedback_timestamp' => $appointments->_meta['exhibitor_feedback']['timestamp'] ?? '',
                    'visitor_feedback_message' => $appointments->_meta['visitor_feedback']->message ?? '',
                    'visitor_feedback_timestamp' => $appointments->_meta['visitor_feedback']->timestamp ?? '',
                    'scheduled_on' => $appointments->scheduled_at->isoFormat('llll') ?? '',
                    'status' => ucfirst($appointments->status) ?? '',
                    'notes' => $appointments->notes ?? '',

                ];
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No Appointments for this exhibitor in this event',
                ]);
            }

            return response()->json([
                'status' => 'success',
                'data' => $formattedAppointments,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function getEventDates(Request $request)
    {
        try {
            $eventId = $request->event_id;
            $event = Event::find($eventId);
            if (!$event) {
                return response()->json([
                    'status' => "error",
                    'message' => 'Event not found.',
                ], 404);
            }

            if ($event->start_date == null) {
                return response()->json([
                    'status' => "error",
                    'message' => 'Start date is null.',
                ], 404);
            }

            if ($event->end_date == null) {
                return response()->json([
                    'status' => "error",
                    'message' => 'End date is null.',
                ], 404);
            }
            $start = Carbon::parse($event->start_date);
            $end = Carbon::parse($event->end_date);
            $dateList = [];
            for ($date = $start; $date->lte($end); $date->addDay()) {
                $dateList[] = $date->format('Y-m-d');
            }

            $formattedDates = [
                'event_dates' => $dateList,
            ];
            return response()->json([
                'status' => 'success',
                'data' => $formattedDates,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function getICSFile(Request $request)
    {
        $appointmentId = $request->appointmentId;
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
}
