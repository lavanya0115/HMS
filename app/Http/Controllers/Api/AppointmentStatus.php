<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use Illuminate\Http\Request;

class AppointmentStatus extends Controller
{
    public function rescheduleAppointment(Request $request)
    {
        $appointmentId = $request->appointment_id;
        $newAppointmentDate = $request->new_appointment_date;
        $newAppointmentTime = $request->new_appointment_time;

        $appointment = Appointment::find($appointmentId);

        if (!$appointment) {
            return response()->json([
                'status' => 'error',
                'message' => 'Appointment not found.',
            ]);
        }

        if ($appointment->status !== 'scheduled') {
            return response()->json([
                'status' => 'error',
                'message' => 'Cannot reschedule appointment. It may already be canceled or rescheduled.',
            ]);
        }

        $newAppointmentTimeTimestamp = strtotime($newAppointmentTime);
        $startTimeTimestamp = strtotime('10:00 AM');
        $endTimeTimestamp = strtotime('6:00 PM');

        if ($newAppointmentTimeTimestamp < $startTimeTimestamp || $newAppointmentTimeTimestamp > $endTimeTimestamp) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid appointment time. Please provide a valid time between 10 AM and 6 PM.',
            ]);
        }

        $eventStartDate = strtotime(date('Y-m-d', strtotime($appointment->event->start_date)));
        $eventEndDate = strtotime(date('Y-m-d', strtotime($appointment->event->end_date)));
        $newAppointmentDateTimestamp = strtotime(date('Y-m-d', strtotime($newAppointmentDate)));

        if ($newAppointmentDateTimestamp < $eventStartDate || $newAppointmentDateTimestamp > $eventEndDate) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid appointment date. Please provide a date within the event dates.',
            ]);
        }

        $appointment->status = 'rescheduled';
        $appointment->scheduled_at = date('Y-m-d H:i:s', strtotime($newAppointmentDate . " " . $newAppointmentTime));
        $appointment->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Appointment rescheduled successfully.',
            'appointment' => $appointment,
        ]);
    }

    public function cancelAppointment(Request $request)
    {
        $appointmentId = $request->appointment_id;
        $appointment = Appointment::find($appointmentId);

        if (!$appointment) {
            return response()->json([
                'status' => 'error',
                'message' => 'Appointment not found.',
            ]);
        }

        $appointment->status = 'canceled';
        $appointment->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Appointment canceled successfully.',
            'appointment' => $appointment,
        ]);
    }

}
