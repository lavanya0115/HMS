<?php

namespace App\Http\Controllers\Api;

use App\Models\Event;
use App\Models\Appointment;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Carbon\Carbon;

class PreviousEventController extends Controller
{
    public function showPreviousEvents()
    {
        try {
            $previousEventId = getPreviousEvents()->pluck('id')->toArray();
            $previousEvents = Event::whereIn('id', $previousEventId)->get();

            if ($previousEvents) {
                $formatedData = $previousEvents->map(function ($previousEvent) {
                    return [
                        'event_id' => $previousEvent->id,
                        'event_title' => $previousEvent->title,
                        'start_date' => Carbon::parse($previousEvent->start_date)->isoFormat('llll') ?? '',
                        'end_date' => Carbon::parse($previousEvent->end_date)->isoFormat('llll') ?? '',
                        'event_layout' => !empty($previousEvent->_meta['layout']) ? asset('storage/' . ($previousEvent->_meta['layout'])) : '',
                        'event_thumbnail' => !empty($previousEvent->_meta['thumbnail']) ? asset('storage/' . ($previousEvent->_meta['thumbnail'])) : '',
                    ];
                });
                return response()->json([
                    'status' => 'success',
                    'data' => $formatedData,
                ]);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'There is no previous event',
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function getPreviousEventCompletedAppointments(Request $request)
    {
        $eventId = $request->eventId;
        if (!$eventId) {
            return response()->json([
                'status' => 'error',
                'message' => 'Event Id missing',
            ]);
        }
        $exhibitorId = auth()->user()->id;
        if (!$exhibitorId) {
            return response()->json([
                'status' => 'error',
                'message' => 'Exhibitor Id missing',
            ]);
        }

        try {

            $previousEventIds = getPreviousEvents()->pluck('id')->toArray();

            if (in_array($eventId, $previousEventIds)) {

                $appointments = Appointment::where('event_id', $eventId)
                    ->where('exhibitor_id', $exhibitorId)
                    ->where('status', 'completed')->get();

                if (isset($appointments) && count($appointments) > 0) {
                    $formatedData = $appointments->map(function ($appointment) {
                        return [
                            'appointment_id' => $appointment->id,
                            'event_id' => $appointment->event?->id ?? '',
                            'event_title' => $appointment->event?->title ?? '',
                            'visitor_id' => $appointment->visitor?->id ?? '',
                            'visitor_name' => $appointment->visitor?->name ?? '',
                            'visitor_logo' => !empty($appointment->visitor?->_meta['logo']) ? asset('storage/' . ($appointment->visitor?->_meta['logo'])) : '',
                            'visitor_organization' => $appointment->visitor?->organization ?? '',
                            'visitor_designation' => $appointment->visitor?->designation ?? '',
                            'visitor_city' => $appointment->visitor?->address?->city ?? '',
                            'exhibitor_id' => $appointment->exhibitor?->id ?? '',
                            'exhibitor_name' => $appointment->exhibitor?->name ?? '',
                            'exhibitor_designation' => $appointment->exhibitor?->exhibitorContact?->designation ?? '',
                            'exhibitor_city' => $appointment->exhibitor?->address?->city ?? '',
                            'exhibitor_feedback_message' => $appointment->_meta['exhibitor_feedback']['message'] ?? '',
                            'exhibitor_feedback_timestamp' => $appointment->_meta['exhibitor_feedback']['timestamp'] ?? '',
                            'visitor_feedback_message' => $appointment->_meta['visitor_feedback']->message ?? '',
                            'visitor_feedback_timestamp' => $appointment->_meta['visitor_feedback']->timestamp ?? '',
                            'scheduled_on' => $appointment->scheduled_at->format('Y-m-d H:i:s') ?? '',
                            'status' => ucfirst($appointment->status) ?? '',
                            'notes' => $appointment->notes ?? '',
                        ];
                    });

                    return response()->json([
                        'status' => 'success',
                        'data' => $formatedData,
                    ]);
                } else {
                    return response()->json([
                        'status' => 'error',
                        'data' => 'There is no completed appointment for this event',
                    ]);
                }
            } else {
                return response()->json([
                    'status' => 'error',
                    'data' => 'This Event does not exist in previous event list',
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ]);
        }
    }
}
