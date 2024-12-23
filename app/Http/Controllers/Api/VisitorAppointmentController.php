<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Event;
use App\Models\EventExhibitor;
use App\Models\EventVisitor;
use Illuminate\Http\Request;

class VisitorAppointmentController extends Controller
{

    public function makeAppointment(Request $request)
    {
        $appointmentDate = $request->appointment_date;
        $appointmentTime = $request->appointment_time;

        $selectedEvent = Event::find($request->event_id);
        if (!$selectedEvent) {
            return response()->json(
                [
                    'status' => 'error',
                    'message' => 'Not Found',
                ]
            );
        }

        $registeredVisitor = EventVisitor::where('visitor_id', $request->visitor_id)->where('event_id', $request->event_id)->first();
        if (!$registeredVisitor) {
            return response()->json(
                [
                    'status' => 'error',
                    'message' => 'Visitor Not Register for Current Event',
                ]
            );
        }

        $registeredExhibitor = EventExhibitor::where('exhibitor_id', $request->exhibitor_id)->where('event_id', $request->event_id)->first();
        if (!$registeredExhibitor) {
            return response()->json([
                'status' => 'error',
                'message' => 'Exhibitor Not Participate for Current Event',

            ]);
        }

        $appointmentTimeTimestamp = strtotime($appointmentTime);
        $startTimeTimestamp = strtotime('10:00 AM');
        $endTimeTimestamp = strtotime('6:00 PM');

        if ($appointmentTimeTimestamp < $startTimeTimestamp || $appointmentTimeTimestamp > $endTimeTimestamp) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid appointment time. Please provide a valid time between 10 AM and 6 PM.',
            ]);
        }

        $eventStartDate = strtotime(date('Y-m-d', strtotime($selectedEvent->start_date)));
        $eventEndDate = strtotime(date('Y-m-d', strtotime($selectedEvent->end_date)));
        $appointmentDateTimestamp = strtotime(date('Y-m-d', strtotime($appointmentDate)));

        if ($appointmentDateTimestamp < $eventStartDate || $appointmentDateTimestamp > $eventEndDate) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid appointment date. Please provide a date within the event dates.',
            ]);
        }

        $appointment = new Appointment();
        $appointment->event_id = $request->event_id;
        $appointment->visitor_id = $request->visitor_id;
        $appointment->exhibitor_id = $request->exhibitor_id;
        $appointment->scheduled_at = date('Y-m-d H:i:s', strtotime($appointmentDate . " " . $appointmentTime));
        $appointment->status = 'scheduled';
        $appointment->save();

        return response()->json(
            [
                'status' => 'success',
                'messgae' => 'Appointment Created',
                'params' => $request->all(),
                'appointment' => $appointment,
            ]
        );

    }
}
