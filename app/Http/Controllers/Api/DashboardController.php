<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Event;
use App\Models\EventSeminarParticipant;
use App\Models\Exhibitor;
use App\Models\EventVisitor;
use App\Models\Seminar;
use App\Models\Visitor;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $currentEvent = getCurrentEvent();
        if (!$currentEvent) {
            return response()->json([
                'status' => "error",
                'message' => "Current event not found."
            ], 404);
        }

        $upcomingEvents = Event::where('id', '!=', $currentEvent->id)
            ->where('start_date', '>=', now()->format('Y-m-d'))
            ->orderBy('start_date', 'asc')
            ->paginate(4);

        $exhibitorId = auth()->user()->id;
        $exhibitor = Exhibitor::find($exhibitorId);
        if (!$exhibitor) {
            return response()->json([
                'status' => 'error',
                'message' => 'Exhibitor not found...'
            ]);
        }
        try {
            $currentEventStatus = [
                'eventId' => $currentEvent->id,
                'id' => $exhibitor->id,
                'type' => 'exhibitor'
            ];
            $data = [
                'currentEvent' => [
                    'id' => $currentEvent->id,
                    'title' => $currentEvent->title,
                    'start_date' => $currentEvent->start_date,
                    'end_date' => $currentEvent->end_date,
                    'thumbnail' => !empty($currentEvent->_meta['thumbnail']) ? asset('storage/' . ($currentEvent->_meta['thumbnail'])) : '',
                    'registrationStatus' => getRegistrationStatus($currentEventStatus),
                    'layout' => !empty($currentEvent->_meta['layout']) ? asset('storage/' . ($currentEvent->_meta['layout'])) : ''
                ],
                'upcomingEvents' => $upcomingEvents->map(function ($event) use ($exhibitor) {
                    $upcomingEventStatus = [
                        'eventId' => $event->id,
                        'id' => $exhibitor->id,
                        'type' => 'exhibitor'
                    ];
                    return [
                        'id' => $event->id,
                        'title' => $event->title,
                        'start_date' => $event->start_date,
                        'end_date' => $event->end_date,
                        'thumbnail' => !empty($event->_meta['thumbnail']) ? asset('storage/' . ($event->_meta['thumbnail'])) : '',
                        'registrationStatus' => getRegistrationStatus($upcomingEventStatus),
                        'layout' => !empty($event->_meta['layout']) ? asset('storage/' . ($event->_meta['layout'])) : ''
                    ];
                })
            ];

            return response()->json([
                'status' => "success",
                'data' => $data
            ], 200);
        } catch (\Exception $exception) {
            return response()->json([
                'status' => 'error',
                'message' => $exception->getMessage(),
            ], 500);
        }

    }

    public function store(Request $request)
    {
        $exhibitor_id = auth()->user()->id;
        $exhibitor = Exhibitor::find($exhibitor_id);
        if (!$exhibitor) {
            return response()->json([
                'status' => 'error',
                'message' => 'Exhibitor not found...'
            ]);
        }
        $eventId = $request->event_id;
        if (!$eventId) {
            return response()->json([
                'status' => 'error',
                'message' => 'Event Id is missing..'
            ]);
        }
        $event = Event::find($eventId);
        if (!$event) {
            return response()->json([
                'status' => 'error',
                'message' => 'Event not found..'
            ]);
        }
        $isExhibitorExists = $exhibitor->eventExhibitors()->where('event_id', $eventId)->first();
        if ($isExhibitorExists) {
            return response()->json([
                'message' => 'Exhibitor already registered',
                'status' => 'error',
            ], 201);
        }
        try {
            $exhibitor->eventExhibitors()->create([
                'event_id' => $eventId,
            ]);
            return response()->json([
                'message' => 'Exhibitor registered successfully',
                'status' => 'success',
            ], 201);
        } catch (\Exception $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
                'status' => 'error',
            ], 500);
        }
    }

    public function getVisitorDashboardData(Request $request)
    {
        $currentEvent = getCurrentEvent();
        if (!$currentEvent) {
            return response()->json([
                'status' => "error",
                'message' => "Current event not found."
            ], 404);
        }

        $upcomingEvents = Event::where('id', '!=', $currentEvent->id)
            ->where('start_date', '>=', now()->format('Y-m-d'))
            ->orderBy('start_date', 'asc')
            ->paginate(4);

        $visitorId = auth()->user()->id;
        $visitor = Visitor::find($visitorId);
        if (!$visitor) {
            return response()->json([
                'status' => 'error',
                'message' => 'Visitor not found...'
            ]);
        }
        try {
            $currentEventStatus = [
                'eventId' => $currentEvent->id,
                'id' => $visitor->id,
                'type' => 'visitor'
            ];

            $data = [
                'currentEvent' => [
                    'id' => $currentEvent->id,
                    'title' => $currentEvent->title,
                    'start_date' => $currentEvent->start_date,
                    'end_date' => $currentEvent->end_date,
                    'pincode' => $currentEvent->address?->pincode ?? '',
                    'city' => $currentEvent->address?->city ?? '',
                    'state' => $currentEvent->address?->state ?? '',
                    'country' => $currentEvent->address?->country ?? '',
                    'address' => $currentEvent->address?->address ?? '',
                    'thumbnail' => !empty($currentEvent->_meta['thumbnail']) ? asset('storage/' . ($currentEvent->_meta['thumbnail'])) : '',
                    'registrationStatus' => getRegistrationStatus($currentEventStatus),
                    'layout' => !empty($currentEvent->_meta['layout']) ? asset('storage/' . ($currentEvent->_meta['layout'])) : ''
                ],
                'upcomingEvents' => $upcomingEvents->map(function ($event) use ($visitor) {
                    $upcomingEventStatus = [
                        'eventId' => $event->id,
                        'id' => $visitor->id,
                        'type' => 'visitor'
                    ];
                    return [
                        'id' => $event->id,
                        'title' => $event->title,
                        'start_date' => $event->start_date,
                        'end_date' => $event->end_date,
                        'pincode' => $event->address?->pincode ?? '',
                        'city' => $event->address?->city ?? '',
                        'state' => $event->address?->state ?? '',
                        'country' => $event->address?->country ?? '',
                        'address' => $event->address?->address ?? '',
                        'thumbnail' => !empty($event->_meta['thumbnail']) ? asset('storage/' . ($event->_meta['thumbnail'])) : '',
                        'registrationStatus' => getRegistrationStatus($upcomingEventStatus),
                        'layout' => !empty($event->_meta['layout']) ? asset('storage/' . ($event->_meta['layout'])) : ''
                    ];
                })
            ];

            return response()->json([
                'status' => "success",
                'data' => $data
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ]);
        }

    }

    public function visitorEventRegistration(Request $request)
    {
        $visitorId = auth()->user()->id;
        $visitor = Visitor::find($visitorId);
        if (!$visitor) {
            return response()->json([
                'status' => 'error',
                'message' => 'Visitor not found...'
            ]);
        }
        $eventId = $request->event_id;
        if (!$eventId) {
            return response()->json([
                'status' => 'error',
                'message' => 'Event Id is missing..'
            ]);
        }
        $event = Event::find($eventId);
        if (!$event) {
            return response()->json([
                'status' => 'error',
                'message' => 'Event not found..'
            ]);
        }
        $source = $request->source;
        if (!$source) {
            return response()->json([
                'status' => 'error',
                'message' => 'Source is missing..'
            ]);
        }

        $isVisitorExists = $visitor->eventVisitors()?->where('event_id', $eventId)->first();
        if ($isVisitorExists) {
            return response()->json([
                'status' => 'error',
                'message' => 'Visitor already registered',
            ], 201);
        }
        try {
            $visitor->eventVisitors()->create([
                'event_id' => $eventId,
                'registration_type' => $source
            ]);
            return response()->json([
                'status' => 'success',
                'message' => 'Visitor registered successfully',
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ]);
        }
    }
    public function getEventDashboardData(Request $request)
    {
        $visitorId = auth()->user()->id;
        $visitor = Visitor::find($visitorId);
        if (!$visitor) {
            return response()->json(['status' => 'error', 'message' => 'Visitor not found'], 404);
        }

        $eventId = $request->event_id;
        if (!$eventId) {
            return response()->json(['status' => 'error', 'message' => 'Event Id is missing'], 404);
        }

        $event = Event::find($eventId);
        if (!$event) {
            return response()->json(['status' => 'error', 'message' => 'Event not found'], 404);
        }

        try {

            $eventVisitor = EventVisitor::where('event_id', $eventId)->where('visitor_id', $visitorId)->first();
            if (!$eventVisitor) {
                return response()->json(['status' => 'error', 'message' => 'Visitor not registered for this event'], 404);
            }

            $totalAppointmentCount = Appointment::where('event_id', $eventId)->where('visitor_id', $visitorId)->count();
            $scheduledCount = Appointment::where('event_id', $eventId)->where('visitor_id', $visitorId)->where('status', 'scheduled')->count();
            $rescheduledCount = Appointment::where('event_id', $eventId)->where('visitor_id', $visitorId)->where('status', 'rescheduled')->count();
            $lapsedCount = Appointment::where('event_id', $eventId)->where('visitor_id', $visitorId)->where('status', 'no-show')->count();
            $cancelledCount = Appointment::where('event_id', $eventId)->where('visitor_id', $visitorId)->where('status', 'cancelled')->count();
            $completedCount = Appointment::where('event_id', $eventId)->where('visitor_id', $visitorId)->where('status', 'completed')->count();
            $seminarCount = Seminar::where('event_id', $eventId)->where('is_active', 1)->count();

            $seminars = Seminar::where('event_id', $eventId)->where('is_active', 1)->get();
            $registeredSeminars = EventSeminarParticipant::where('event_id', $eventId)->where('visitor_id', $visitorId)->pluck('seminar_id')->toArray();

            $seminarsList = $seminars->map(function ($seminar) use ($registeredSeminars, $eventId, $visitorId) {
                $isRegisteredSeminar = in_array($seminar->id, $registeredSeminars);
                $seminarPayment = EventSeminarParticipant::where('event_id', $eventId)
                    ->where('visitor_id', $visitorId)
                    ->where('seminar_id', $seminar->id)
                    ->first();
                $paymentStatus = '';
                if (!empty($seminarPayment->payment_status)) {
                    $paymentStatus = $seminarPayment->payment_status == 'paid' ? 'Paid' : 'Unpaid';
                }
                return [
                    'id' => $seminar->id,
                    'title' => $seminar->title,
                    'description' => $seminar->description,
                    'date' => $seminar->date,
                    'start_time' => $seminar->start_time,
                    'end_time' => $seminar->end_time,
                    'amount' => $seminar->amount,
                    'location' => $seminar->_meta['location'] ?? '',
                    'is_registered' => $isRegisteredSeminar ? 'Registered' : 'Register',
                    'payment_status' => $paymentStatus,
                    'image' => !empty($seminar->_meta['thumbnail']) ? asset('storage/' . ($seminar->_meta['thumbnail'])) : ''
                ];
            });
            $hallLayout = !empty($event->_meta['layout']) ? asset('storage/' . $event->_meta['layout']) : '';
            $data = [
                'event_id' => $event->id,
                'event_title' => $event->title,
                'totalAppointment' => $totalAppointmentCount,
                'scheduled' => $scheduledCount,
                'rescheduled' => $rescheduledCount,
                'lapsed' => $lapsedCount,
                'cancelled' => $cancelledCount,
                'completed' => $completedCount,
                'seminar' => $seminarCount,
                'seminarsList' => $seminarsList,
                'hall_layout' => $hallLayout
            ];
            return response()->json(['status' => 'success', 'data' => $data], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Something went wrong', 'error' => $e->getMessage()], 500);
        }
    }
}


