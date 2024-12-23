<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\OneSignalController;
use App\Models\Address;
use App\Models\Announcement;
use App\Models\Appointment;
use App\Models\Event;
use App\Models\EventExhibitor;
use App\Models\EventVisitor;
use App\Models\Exhibitor;
use App\Models\ExhibitorContact;
use App\Models\Seminar;
use App\Models\Visitor;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class AdminController extends Controller
{
    public function getVisitors(Request $request)
    {

        $search = $request->search ?? '';
        $type = $request->type ?? '';
        $eventId = $request->event_id ?? null;
        $startDate = $request->start_date ?? null;
        $endDate = $request->end_date ?? null;
        $participateStatus = $request->participate_status ?? null;
        $visitorRegId = $request->visitor_reg_id ?? null;
        $sortBy = $request->sort_by ?? null;
        $sortDirection = $request->sort_direction ?? 'desc';

        $event = $eventId ? Event::find($eventId) : null;

        $visitorsQuery = $this->getFilteredVisitors(
            $event->id ?? null,
            $startDate,
            $endDate,
            $participateStatus,
            $visitorRegId,
            $search,
            $type,
            $sortBy,
            $sortDirection
        );

        $visitors = $visitorsQuery->cursorPaginate(10);

        $formattedVisitors = $visitors->map(function ($visitor) use ($event) {

            $appointmentsCount = $visitor->appointments()
                ->when(isset($event->id), function ($query) use ($event) {
                    return $query->where('event_id', $event->id);
                })
                ->count();


            $productLookingFor = [];
            $knownSource = null;
            foreach ($visitor->eventVisitors as $eventVisitor) {
                $productLookingFor[] = $eventVisitor->getProductNames();
                if (!$knownSource && isset($eventVisitor->known_source)) {
                    $knownSource = $eventVisitor->known_source;
                }
            }

            return [
                'visitor_id' => $visitor->id,
                'salutation' => $visitor->salutation,
                'name' => $visitor->name,
                'mobile_no' => $visitor->mobile_number,
                'email' => $visitor->email,
                'nature_of_business' => $visitor->nature_of_business,
                'organization' => $visitor->organization,
                'designation' => $visitor->designation,
                'known_sources' => $knownSource,
                'address' => $visitor->address->address ?? '',
                'city' => $visitor->address->city ?? '',
                'state' => $visitor->address->state ?? '',
                'pincode' => $visitor->address->pincode ?? '',
                'reason_for_visit' => $visitor->reason_for_visit,
                'product_looking_for' => implode(', ', $productLookingFor),
                'no_of_appointments' => $appointmentsCount,
            ];
        });

        // Only for getting paginate data
        $visitors = $visitors->toArray();

        return response()->json([
            'status' => 'success',
            'message' => 'Successfully retrieved the list of visitors.',
            'visitors' => $formattedVisitors,
            'paginate_data' => [
                'next_cursor' => $visitors["next_cursor"] ?? null,
                'prev_cursor' => $visitors["prev_cursor"] ?? null,
                'next_page_url' => $visitors["next_page_url"] ?? null,
                'prev_page_url' => $visitors["prev_page_url"] ?? null,
            ],
        ]);
    }

    private function getFilteredVisitors($eventId, $startDate, $endDate, $participateStatus, $visitorRegId, $search, $type, $sortBy, $sortDirection)
    {
        $query = Visitor::query();
        $query->whereHas('eventVisitors', function ($subQuery) use ($eventId, $startDate, $endDate, $participateStatus, $visitorRegId, $type) {
            $subQuery->where('is_delegates', '<>', 1)
                ->when($eventId, function ($query) use ($eventId) {
                    return $query->where('event_id', $eventId);
                })
                ->when($startDate && $endDate, function ($subQuery) use ($startDate, $endDate) {
                    return $subQuery->whereBetween('created_at', [Carbon::parse($startDate)->startOfDay(), Carbon::parse($endDate)->endOfDay()]);
                })
                ->when($participateStatus == 'visited', function ($query) {
                    return $query->where('is_visited', 1);
                })
                ->when($participateStatus == 'not_visited', function ($query) {
                    return $query->where('is_visited', 0);
                })
                ->when($visitorRegId, function ($query) use ($visitorRegId) {
                    return $query->where('_meta->reference_no', 'like', '%' . trim($visitorRegId) . '%');
                })
                ->when($type, function ($query) use ($type) {
                    return $query->where('registration_type', $type);
                });
        });

        // Apply search filter
        $query->when(trim($search), function ($query) use ($search) {
            $trimmedSearch = trim($search);
            $query->where(function ($query) use ($trimmedSearch) {
                $query->where('name', 'like', '%' . $trimmedSearch . '%')
                    ->orWhere('mobile_number', 'like', '%' . $trimmedSearch . '%')
                    ->orWhere('email', 'like', '%' . $trimmedSearch . '%')
                    ->orWhere('organization', 'like', '%' . $trimmedSearch . '%')
                    ->orWhere('designation', 'like', '%' . $trimmedSearch . '%');
            });
        });

        return $query->orderBy($sortBy ?? 'id', $sortDirection ?? 'desc');

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

    public function getExhibitors(Request $request)
    {
        $search = trim($request->search ?? '');
        $eventId = $request->event_id ?? null;
        $startDate = $request->start_date ?? null;
        $endDate = $request->end_date ?? null;
        $sortBy = $request->sort_by ?? null;
        $sortDirection = $request->sort_direction ?? 'desc';

        $event = $eventId ? Event::find($eventId) : null;

        $exhibitorQuery = $this->getFilteredExhibitors(
            $event->id ?? null,
            $search,
            $startDate,
            $endDate,
            $sortBy,
            $sortDirection
        );

        $exhibitors = $exhibitorQuery->cursorPaginate(10);

        $formattedExhibitors = $exhibitors->map(function ($exhibitor) use ($event) {

            $eventExhibitor = $exhibitor->eventExhibitors
                ->when(isset($event->id), function ($query) use ($event) {
                    return $query->where('event_id', $event->id);
                })
                ->first();

            $products = $exhibitor->eventExhibitors
                ->when(isset($event->id), function ($query) use ($event) {
                    return $query->where('event_id', $event->id);
                })
                ->map(function ($eventExhibitor) {
                    return $eventExhibitor->getProductNames();
                })->implode(', ');

            return [
                'exhibitor_id' => $exhibitor->id,
                'stall_no' => $eventExhibitor->stall_no ?? '',
                'square_space' => $eventExhibitor->_meta['square_space'] ?? '',
                'stall_space' => $eventExhibitor->_meta['stall_space'] ?? '',
                'company' => $exhibitor->name,
                'email' => $exhibitor->email,
                'phone_no' => $exhibitor->mobile_number,
                'address' => $exhibitor->address->city ?? '',
                'contact_person' => $exhibitor->exhibitorContact->name ?? '_',
                'contact_no' => $exhibitor->exhibitorContact->contact_number ?? '_',
                'products' => $products,
                'no_of_appointments' => $exhibitor->appointments->count(),
            ];
        });

        $exhibitorsArray = $exhibitors->toArray();

        return response()->json([
            'status' => 'success',
            'message' => 'Successfully retrieved the list of exhibitors.',
            'exhibitors' => $formattedExhibitors,
            'paginate_data' => [
                'next_cursor' => $exhibitorsArray['next_cursor'] ?? null,
                'prev_cursor' => $exhibitorsArray['prev_cursor'] ?? null,
                'next_page_url' => $exhibitorsArray['next_page_url'] ?? null,
                'prev_page_url' => $exhibitorsArray['prev_page_url'] ?? null,
            ],
        ]);
    }

    public function getFilteredExhibitors($eventId, $search, $startDate, $endDate, $sortBy, $sortDirection)
    {

        $query = Exhibitor::whereHas('eventExhibitors', function ($subQuery) use ($eventId, $startDate, $endDate) {
            $subQuery->when($eventId, function ($query) use ($eventId) {
                $query->where('event_id', $eventId);
            });

            if ($startDate && $endDate) {
                $subQuery->whereBetween('created_at', [
                    Carbon::parse($startDate)->startOfDay(),
                    Carbon::parse($endDate)->endOfDay(),
                ]);
            }
        });

        if ($search) {
            $query->where(function ($query) use ($search) {
                $query->where('name', 'LIKE', '%' . $search . '%')
                    ->orWhere('mobile_number', 'LIKE', '%' . $search . '%')
                    ->orWhere('email', 'LIKE', '%' . $search . '%')
                    ->orWhereHas('exhibitorContact', function ($query) use ($search) {
                        $query->where('contact_number', 'LIKE', '%' . $search . '%')
                            ->orWhere('name', 'LIKE', '%' . $search . '%');
                    })
                    ->orWhereHas('address', function ($query) use ($search) {
                        $query->where('city', 'LIKE', '%' . $search . '%');
                    });
            });
        }

        if ($sortBy) {
            $query->orderBy($sortBy, $sortDirection);
        }

        return $query;
    }

    //Update Stall
    public function updateStallDetail(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'exhibitor_id' => 'required',
            'stall_space' => 'required',
            'square_space' => 'required',
            'stall_no' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed please send the required values',
                'errors' => $validator->errors(),
            ]);
        }
        $currentEvent = getCurrentEvent();
        $isStallNoExists = EventExhibitor::where('event_id', $currentEvent->id)
            ->where('stall_no', $request->stall_no)
            ->where('exhibitor_id', '!=', $request->exhibitor_id)
            ->first();

        if ($isStallNoExists) {
            return response()->json([
                'status' => 'error',
                'message' => 'Stall No already exists',
            ], 422);
        }

        $exhibitor = Exhibitor::find($request->exhibitor_id);

        $exhibitor->eventExhibitors()->where('event_id', $currentEvent->id)->update([
            'stall_no' => $request->stall_no,
            '_meta' => [
                'stall_space' => $request->stall_space ?? '',
                'square_space' => $request->square_space ?? '',
            ],
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Stall detail updated successfully.',
            'stall_no' => $request->stall_no,
            'stall_space' => $request->stall_space,
            'square_space' => $request->square_space,

        ]);
    }

    //List of Delegates
    public function getDelegates(Request $request)
    {
        $eventId = $request->input('event_id');
        $search = trim($request->input('search', ''));
        $seminar = $request->input('seminar', null);
        $sortBy = $request->input('sort_by', 'id');
        $sortDirection = $request->input('sort_direction', 'desc');

        $event = $eventId ? Event::find($eventId) : getCurrentEvent();

        if (!$event) {
            return response()->json([
                'status' => 'error',
                'message' => 'Event not found.',
            ], 404);
        }

        $query = Visitor::whereHas('eventDelegates', function ($query) use ($eventId, $seminar) {
            $query->where('event_id', $eventId)
                ->when($seminar, function ($query) use ($seminar) {
                    $query->where('seminar_id', $seminar);
                });
        })->when(!empty($search), function ($query) use ($search) {
            $query->where(function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%')
                    ->orWhere('mobile_number', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%')
                    ->orWhere('organization', 'like', '%' . $search . '%');
            });
        });

        if ($sortBy && $sortDirection) {
            $query->orderBy($sortBy, $sortDirection);
        }

        $delegatesQuery = $query->cursorPaginate(10);

        $delegates = $delegatesQuery->map(function ($delegate) use ($eventId) {
            $delegateData = [
                'name' => $delegate->name,
                'mobile_number' => $delegate->mobile_number,
                'email' => $delegate->email,
                'organization' => $delegate->organization,
                'designation' => $delegate->designation,
                'seminars' => [],
            ];

            foreach ($delegate->eventDelegates as $eventDelegate) {
                if ($eventDelegate->event_id == $eventId) {
                    $seminar = $eventDelegate->seminar;
                    $delegateData['seminars'][] = [
                        'seminar_name' => $seminar ? $seminar->title : null,
                        'payment_status' => $eventDelegate->payment_status ?? 'Not Registered',
                    ];
                }
            }

            return $delegateData;
        });
        $delegatesQuery = $delegatesQuery->toArray();
        return response()->json([
            'status' => 'success',
            'delegates' => $delegates,
            'paginate_data' => [
                'next_cursor' => $delegatesQuery["next_cursor"] ?? null,
                'prev_cursor' => $delegatesQuery["prev_cursor"] ?? null,
                'next_page_url' => $delegatesQuery["next_page_url"] ?? null,
                'prev_page_url' => $delegatesQuery["prev_page_url"] ?? null,
            ],
        ]);
    }

    //List of Appointments
    public function getAppointments(Request $request)
    {
        $eventId = $request->input('event_id') ?? null;
        $appointmentStatus = $request->input('appointment_status') ?? null;
        $sortBy = $request->input('sort_by') ?? 'id';
        $sortDirection = $request->input('sort_direction') ?? 'desc';
        $search = $request->input('search') ?? null;
        $scheduled_at = $request->input('scheduled_at') ?? null;

        $event = $eventId ? Event::find($eventId) : getCurrentEvent();

        if (!$event) {
            return response()->json([
                'status' => 'error',
                'message' => 'Event not found.',
            ], 404);
        }

        $appointments = Appointment::with([
            'visitor',
            'exhibitor',
            'eventVisitorInfo' => function ($query) use ($eventId) {
                $query->where('event_id', $eventId);
            },
        ])
            ->when($eventId, function ($query) use ($eventId) {
                return $query->where('event_id', $eventId);
            })
            ->when($search !== null, function ($query) use ($search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->whereHas('visitor', function ($query) use ($search) {
                        $query->where('name', 'like', '%' . $search . '%');
                    })
                        ->orWhereHas('exhibitor', function ($query) use ($search) {
                            $query->where('name', 'like', '%' . $search . '%');
                        });
                });
            })

            ->when($scheduled_at !== null, function ($query) use ($scheduled_at) {
                return $query->whereDate('scheduled_at', $scheduled_at);
            })

            ->when($appointmentStatus !== null, function ($query) use ($appointmentStatus) {
                return $query->where('status', $appointmentStatus);
            })
            ->orderBy($sortBy, $sortDirection)
            ->cursorPaginate(10);

        $data = $appointments->getCollection()->map(function ($appointment) use ($eventId) {
            $visitorData = $appointment->eventVisitorInfo()->where('event_id', $eventId)->first();
            $productNames = $visitorData ? $visitorData->getProductNames() : null;
            $products = $productNames ? collect(explode(',', $productNames))->take(2)->implode(', ') : 'No Products';
            $moreProductsCount = $productNames ? max(0, count(explode(',', $productNames)) - 2) : 0;

            return [
                'appointment_id' => $appointment->id,
                'exhibitor_id' => $appointment->exhibitor->id ?? null,
                'visitor_id' => $appointment->visitor->id ?? null,
                'products' => $products . ($moreProductsCount > 0 ? " (+{$moreProductsCount} more)" : ''),
                'visitor_name' => $appointment->visitor->name ?? 'Visitor Details',
                'exhibitor_name' => $appointment->exhibitor->name ?? 'Exhibitor Details',
                'designation' => $appointment->visitor->designation ?? 'Visitor Designation',
                'organization' => $appointment->visitor->organization ?? 'Visitor Organization',
                'scheduled_at' => $appointment->scheduled_at ? $appointment->scheduled_at->isoFormat('llll') : 'Date and Time',
                'status' => $appointment->status ?? '',
                'updated_at' => $appointment->updated_at ? $appointment->updated_at->isoFormat('llll') : '',
            ];
        });

        // Convert appointments to array to get pagination data
        $appointments = $appointments->toArray();

        return response()->json([
            'status' => 'success',
            'appointments' => $data,
            'paginate_data' => [
                'next_cursor' => $appointments["next_cursor"] ?? null,
                'prev_cursor' => $appointments["prev_cursor"] ?? null,
                'next_page_url' => $appointments["next_page_url"] ?? null,
                'prev_page_url' => $appointments["prev_page_url"] ?? null,
            ],
        ]);
    }

    //dashboard count

    public function getAdminDashboardData(Request $request)
    {
        $eventId = $request->input('event_id') ?? null;

        $event = $eventId ? Event::find($eventId) : getCurrentEvent();

        if (!$event) {
            return response()->json([
                'status' => 'error',
                'message' => 'Event not found.',
            ], 404);
        }

        $currentEventStartDate = Carbon::parse($event->start_date)->startOfDay();
        $currentEventEndDate = Carbon::parse($event->end_date)->startOfDay();

        $eventDates = CarbonPeriod::create($currentEventStartDate, $currentEventEndDate);

        $eventVisitorData = [];

        foreach ($eventDates as $date) {
            $eventDate = $date->format('Y-m-d');

            $eventVisitorData[$eventDate] = [];

            $onlineTotalRegisterCount = EventVisitor::where('event_id', $event->id)
                ->whereDate('created_at', $eventDate)
                ->whereIn('registration_type', ['medicall', 'online'])
                ->where('is_delegates', '0')
                ->count();
            $onlineVisitedCount = EventVisitor::where('event_id', $event->id)
                ->whereDate('updated_at', $eventDate)
                ->whereIn('registration_type', ['medicall', 'online'])
                ->where('is_visited', 1)
                ->where('is_delegates', '0')
                ->count();

            $spotTotalRegisterCount = EventVisitor::where('event_id', $event->id)
                ->whereDate('created_at', $eventDate)
                ->where('registration_type', 'spot')
                ->where('is_delegates', '0')
                ->count();
            $spotVisitedCount = EventVisitor::where('event_id', $event->id)
                ->whereDate('updated_at', $eventDate)
                ->where('registration_type', 'spot')
                ->where('is_visited', 1)
                ->where('is_delegates', '0')
                ->count();

            $whatsappTotalRegisterCount = EventVisitor::where('event_id', $event->id)
                ->whereDate('created_at', $eventDate)
                ->where('registration_type', 'whatsapp')
                ->count();

            $whatsappVisitedCount = EventVisitor::where('event_id', $event->id)
                ->whereDate('updated_at', $eventDate)
                ->where('registration_type', 'whatsapp')
                ->where('is_visited', 1)
                ->where('is_delegates', '0')
                ->count();

            $total10tRegisterCount = EventVisitor::where('event_id', $event->id)
                ->whereDate('created_at', $eventDate)
                ->where('registration_type', '10t')
                ->where('is_delegates', '0')
                ->count();
            $total10tVisitedCount = EventVisitor::where('event_id', $event->id)
                ->whereDate('updated_at', $eventDate)
                ->where('registration_type', '10t')
                ->where('is_visited', 1)
                ->where('is_delegates', '0')
                ->count();

            $delegatesTotalRegisterCount = EventVisitor::where('event_id', $event->id)
                ->whereDate('created_at', $eventDate)
                ->where('is_delegates', '1')
                ->count();
            $delegatesVisitedCount = EventVisitor::where('event_id', $event->id)
                ->whereDate('updated_at', $eventDate)
                ->where('is_delegates', 1)
                ->where('is_visited', 1)
                ->count();

            $eventVisitorData[$eventDate] = [
                'online' => [
                    'total_register_count' => $onlineTotalRegisterCount,
                    'visited_count' => $onlineVisitedCount,
                ],
                'spot' => [
                    'total_register_count' => $spotTotalRegisterCount,
                    'visited_count' => $spotVisitedCount,
                ],
                'whatsapp' => [
                    'total_register_count' => $whatsappTotalRegisterCount,
                    'visited_count' => $whatsappVisitedCount,
                ],
                '10t' => [
                    'total_register_count' => $total10tRegisterCount,
                    'visited_count' => $total10tVisitedCount,
                ],
                'delegates' => [
                    'total_register_count' => $delegatesTotalRegisterCount,
                    'visited_count' => $delegatesVisitedCount,
                ],
            ];
        }

        $visitorsCount = Visitor::whereHas('eventVisitors', function ($query) use ($event) {
            $query->where('event_id', $event->id);
        })->count();

        $exhibitorsCount = Exhibitor::whereHas('eventExhibitors', function ($query) use ($event) {
            $query->where('event_id', $event->id);
        })->count();

        $delegateCount = EventVisitor::where('event_id', $event->id)->where('is_delegates', 1)->count();

        $totalAppointmentCount = Appointment::where('event_id', $event->id)->where('cancelled_by', null)->count();
        $scheduledCount = Appointment::where('event_id', $event->id)->where('status', 'scheduled')->count();
        $rescheduledCount = Appointment::where('event_id', $event->id)->where('status', 'rescheduled')->count();
        $lapsedCount = Appointment::where('event_id', $event->id)->where('status', 'no-show')->count();
        $cancelledCount = Appointment::where('event_id', $event->id)->where('status', 'cancelled')->count();
        $completedCount = Appointment::where('event_id', $event->id)->where('status', 'completed')->count();

        $visitorAppointmentCount = EventVisitor::where('event_id', $event->id)
            ->whereHas('visitor.appointments', function ($query) use ($event) {
                $query->where('event_id', $event->id);
            })->count();

        return response()->json([
            'status' => 'success',
            'message' => 'Successfully retrieved admin data.',
            'title' => $event->title,
            'thumbnail' => asset('storage/' . ($event->_meta['thumbnail'] ?? '')),
            'visitors_count' => $visitorsCount,
            'exhibitors_count' => $exhibitorsCount,
            'delegateCount' => $delegateCount,
            'total_appointments_count' => $totalAppointmentCount,
            'scheduled_count' => $scheduledCount,
            'rescheduled_count' => $rescheduledCount,
            'lapsed_count' => $lapsedCount,
            'cancelled_count' => $cancelledCount,
            'completed_count' => $completedCount,
            'visitorAppointmentCount' => $visitorAppointmentCount,
            'event_visitor_data' => $eventVisitorData,
        ]);
    }

    public function getEventDetails(Request $request)
    {
        $currentEvent = getCurrentEvent();
        $currentAndPreviousEvents = Event::where('start_date', '<=', $currentEvent->start_date)->orderBy('start_date', 'desc')->get();
        $formattedEvents = $currentAndPreviousEvents->map(function ($event) {
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
                'layout' => !empty($event->_meta['layout']) ? asset('storage/' . ($event->_meta['layout'])) : '',
                'exhibitorList' => !empty($event->_meta['exhibitorList']) ? asset('storage/' . ($event->_meta['exhibitorList'])) : '',
                'latitude' => $event->_meta['latitude'] ?? '',
                'longitude' => $event->_meta['longitude'] ?? '',
            ];
        });

        $currentAndUpcomingEvents = Event::where('start_date', '>=', $currentEvent->start_date)->orderBy('start_date', 'asc')->get();
        $formattedUpcomingEvents = $currentAndUpcomingEvents->map(function ($event) {
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
                'layout' => !empty($event->_meta['layout']) ? asset('storage/' . ($event->_meta['layout'])) : '',
                'exhibitorList' => !empty($event->_meta['exhibitorList']) ? asset('storage/' . ($event->_meta['exhibitorList'])) : '',
                'latitude' => $event->_meta['latitude'] ?? '',
                'longitude' => $event->_meta['longitude'] ?? '',
            ];
        });

        $seminars = Seminar::where('event_id', $currentEvent->id)->where('is_active', 1)->get();
        $seminarsList = $seminars->map(function ($seminar) {
            return [
                'id' => $seminar->id,
                'title' => $seminar->title,
                'description' => $seminar->description,
                'date' => $seminar->date,
                'start_time' => $seminar->start_time,
                'end_time' => $seminar->end_time,
                'amount' => $seminar->amount,
                'location' => $seminar->_meta['location'] ?? '',
                'image' => !empty($seminar->_meta['thumbnail']) ? asset('storage/' . ($seminar->_meta['thumbnail'])) : '',
            ];
        });
        return response()->json([
            'status' => 'success',
            'currentEventId' => $currentEvent->id,
            'currentAndPreviousEvents' => $formattedEvents,
            'currentAndUpcomingEvents' => $formattedUpcomingEvents,
            'seminars' => $seminarsList,
        ]);
    }

    public function getLastSevenDaysVisitorsCount(Request $request)
    {
        $eventId = $request->event_id;
        $event = Event::find($eventId);
        if (empty($event)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Event not found...',
            ]);
        }

        $lastSevenDaysCount = [];
        $totalCount = 0;

        for ($i = 0; $i < 7; $i++) {
            $date = Carbon::today()->subDays($i);
            $visitorCount = EventVisitor::where('event_id', $eventId)->whereDate('created_at', $date)->count();

            $lastSevenDaysCount[] = [
                'date' => $date->format('d-m-Y'),
                'count' => $visitorCount,
            ];

            $totalCount += $visitorCount;
        }

        return response()->json([
            'status' => 'success',
            'lastSevenDaysCount' => $lastSevenDaysCount,
            'total' => $totalCount,
        ]);
    }

    public function getTopLocationDetails(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required',
            'startDate' => 'date_format:Y-m-d|before_or_equal:endDate|nullable',
            'endDate' => 'date_format:Y-m-d|after_or_equal:startDate|nullable',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'status' => 'fail',
                'errors' => $validator->errors(),
            ], 422);
        }

        $eventId = $request->event_id;
        $event = Event::find($eventId);
        if (empty($event)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Event not found...',
            ]);
        }
        $startDate = $request->startDate ?? null;
        $endDate = $request->endDate ?? null;
        $currentDate = Carbon::today();

        $type = $request->type;
        $locationTypes = ['city', 'state', 'country'];

        if (!in_array($type, $locationTypes)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid type',
            ], 422);
        }

        $topLocations = Address::select($type, DB::raw('count(*) as total'))
            ->where('addresses.addressable_type', 'App\Models\Visitor')
            ->whereNotNull($type)
            ->where($type, '<>', '')
            ->join('event_visitors', 'addresses.addressable_id', '=', 'event_visitors.visitor_id')
            ->where('event_visitors.event_id', '=', $eventId)
            ->when($startDate, function ($query, $startDate) {
                $query->whereDate('event_visitors.created_at', '>=', $startDate);
            })
            ->when($endDate, function ($query, $endDate) {
                $query->whereDate('event_visitors.created_at', '<=', $endDate);
            })
            ->groupBy($type)
            ->orderBy('total', 'desc')
            ->take(5)
            ->get();

        foreach ($topLocations as $location) {
            $today_count = Address::where('addressable_type', 'App\Models\Visitor')
                ->where($type, $location->$type)
                ->join('event_visitors', 'addresses.addressable_id', '=', 'event_visitors.visitor_id')
                ->where('event_visitors.event_id', '=', $eventId)
                ->whereDate('event_visitors.created_at', $currentDate)
                ->count();
            $location->today_count = $today_count;
        }

        return response()->json([
            'status' => 'success',
            'top5Locations' => $topLocations,
            'overAllCount' => $topLocations->sum('total'),
            'overAllTodayCount' => $topLocations->sum('today_count'),
        ]);
    }

    public function getRegistrationTypewiseCounts(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'startDate' => 'date_format:Y-m-d|before_or_equal:endDate|nullable',
            'endDate' => 'date_format:Y-m-d|after_or_equal:startDate|nullable',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'status' => 'fail',
                'errors' => $validator->errors(),
            ], 422);
        }

        $eventId = $request->event_id;
        $event = Event::find($eventId);
        if (empty($event)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Event not found...',
            ]);
        }
        $startDate = $request->startDate ?? null;
        $endDate = $request->endDate ?? null;
        $currentDate = Carbon::today();

        $types = EventVisitor::select('registration_type')->where('event_id', $eventId)
            ->whereNotNull('registration_type')->distinct()->get();

        $registrationTypes = [];
        $webTotalCount = 0;
        $webTodayCount = 0;

        foreach ($types as $type) {

            $totalCount = EventVisitor::where('event_id', $eventId)
                ->where('registration_type', $type->registration_type)
                ->when($startDate, function ($query, $startDate) {
                    $query->whereDate('created_at', '>=', $startDate);
                })
                ->when($endDate, function ($query, $endDate) {
                    $query->whereDate('created_at', '<=', $endDate);
                })
                ->count();

            $todayCount = EventVisitor::where('event_id', $eventId)
                ->where('registration_type', $type->registration_type)
                ->whereDate('created_at', $currentDate)
                ->count();

            $label = '';
            if (in_array($type->registration_type, ['medicall', 'online'])) {
                $label = 'Web';
                $webTotalCount += $totalCount;
                $webTodayCount += $todayCount;
            } elseif ($type->registration_type == 'web') {
                $label = 'CRM';
            } else {
                $label = ucfirst($type->registration_type);
            }

            if ($label !== 'Web') {
                $registrationTypes[] = [
                    'name' => $label,
                    'total' => $totalCount,
                    'today_count' => $todayCount,
                ];
            }
        }

        $registrationTypes[] = [
            'name' => 'Web',
            'total' => $webTotalCount,
            'today_count' => $webTodayCount,
        ];

        $overAllCount = array_sum(array_column($registrationTypes, 'total'));
        $overAllTodayCount = array_sum(array_column($registrationTypes, 'today_count'));

        return response()->json([
            'status' => 'success',
            'registrationTypes' => $registrationTypes,
            'overAllCount' => $overAllCount,
            'overAllTodayCount' => $overAllTodayCount,
        ]);

    }

    public function getBusinessTypeWiseCounts(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'startDate' => 'date_format:Y-m-d|before_or_equal:endDate|nullable',
            'endDate' => 'date_format:Y-m-d|after_or_equal:startDate|nullable',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'status' => 'fail',
                'errors' => $validator->errors(),
            ], 422);
        }

        $eventId = $request->event_id;
        $event = Event::find($eventId);
        if (empty($event)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Event not found...',
            ]);
        }
        $startDate = $request->startDate ?? null;
        $endDate = $request->endDate ?? null;
        $currentDate = Carbon::today();

        $businessTypes = Visitor::with(['eventVisitors', 'category'])->select('category_id', DB::raw('count(*) as total'))
            ->whereNotNull('category_id')
            ->where('category_id', '<>', '')
            ->whereHas('eventVisitors', function ($query) use ($eventId, $startDate, $endDate) {
                $query->where('event_id', $eventId)
                    ->when($startDate, function ($query, $startDate) {
                        $query->whereDate('created_at', '>=', $startDate);
                    })
                    ->when($endDate, function ($query, $endDate) {
                        $query->whereDate('created_at', '<=', $endDate);
                    });
            })
            ->whereHas('category', function ($query) {
                $query->where('name', '<>', '')
                    ->whereNotNull('name');
            })
            ->groupBy('category_id')
            ->orderBy('total', 'desc')
            ->take(5)
            ->get();

        foreach ($businessTypes as $businessType) {
            $today_count = Visitor::where('category_id', $businessType->category_id)
                ->whereHas('eventVisitors', function ($query) use ($eventId, $currentDate) {
                    $query->where('event_id', $eventId)
                        ->whereDate('created_at', $currentDate);
                })
                ->count();
            $businessType->today_count = $today_count;
            $businessType->name = $businessType->category?->name;
        }

        $top5BusinessTypes = $businessTypes->map(function ($businessType) {
            return [
                'name' => $businessType->name,
                'total' => $businessType->total,
                'today_count' => $businessType->today_count,
            ];
        });

        return response()->json([
            'status' => 'success',
            'businessTypes' => $top5BusinessTypes,
            'overAllCount' => $top5BusinessTypes->sum('total'),
            'overAllTodayCount' => $top5BusinessTypes->sum('today_count'),
        ]);
    }

    public function getKnownSourceWiseCounts(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'startDate' => 'date_format:Y-m-d|before_or_equal:endDate|nullable',
            'endDate' => 'date_format:Y-m-d|after_or_equal:startDate|nullable',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'status' => 'fail',
                'errors' => $validator->errors(),
            ], 422);
        }

        $eventId = $request->event_id;
        $event = Event::find($eventId);
        if (empty($event)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Event not found...',
            ]);
        }
        $startDate = $request->startDate ?? null;
        $endDate = $request->endDate ?? null;
        $currentDate = Carbon::today();

        $knownSources = EventVisitor::select('known_source', DB::raw('count(*) as total'))
            ->whereNotNull('known_source')
            ->where('known_source', '<>', '')
            ->where('event_id', $eventId)
            ->when($startDate, function ($query, $startDate) {
                $query->whereDate('created_at', '>=', $startDate);
            })
            ->when($endDate, function ($query, $endDate) {
                $query->whereDate('created_at', '<=', $endDate);
            })
            ->groupBy('known_source')
            ->orderBy('total', 'desc')
            ->take(5)
            ->get();

        foreach ($knownSources as $knownSource) {
            $today_count = EventVisitor::where('known_source', $knownSource->known_source)
                ->where('event_id', $eventId)
                ->whereDate('created_at', $currentDate)
                ->count();
            $knownSource->today_count = $today_count;
        }

        return response()->json([
            'status' => 'success',
            'knownSources' => $knownSources,
            'overAllCount' => $knownSources->sum('total'),
            'overAllTodayCount' => $knownSources->sum('today_count'),
        ]);
    }

    public function storeAnnouncement(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'event_id' => 'required',
            'title' => 'required',
            'description' => 'required',
            'visible_type' => 'required|in:visitors_only,exhibitors_only,both',
            'is_active' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'status' => 'error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $eventId = $request->event_id;
        $event = Event::find($eventId);
        if (empty($event)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Event not found...',
            ]);
        }

        $announcement = Announcement::create($request->only(['event_id', 'title', 'description', 'image', 'visible_type', 'is_active']));

        if (!$announcement) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create announcement',
            ], 500);
        }

        if ($announcement->is_active == 1) {

            $users = collect();

            $visitors = Visitor::whereHas('eventVisitors', function ($query) use ($request) {
                $query->where('event_id', $request->event_id);
            })->get();

            $exhibitors = Exhibitor::whereHas('eventExhibitors', function ($query) use ($request) {
                $query->where('event_id', $request->event_id);
            })->get();

            $exhibitorContacts = ExhibitorContact::whereIn('exhibitor_id', $exhibitors->pluck('id'))->get()
                ->map(function ($contact) {
                    $contact->mobile_number = $contact->contact_number;
                    return $contact;
                });

            if ($request->visible_type === 'visitors_only') {
                $users = $visitors;
            } elseif ($request->visible_type === 'exhibitors_only') {
                $users = $exhibitors->merge($exhibitorContacts);
            } elseif ($request->visible_type === 'both') {
                $users = $visitors->merge($exhibitors)->merge($exhibitorContacts);
            }

            $uniqueUsers = $users->pluck('mobile_number')->toArray();
            $oneSignal = new OneSignalController();

            $oneSignal->sendNotification(
                $announcement->title,
                $announcement->description,
                $uniqueUsers,
                $announcement->image
            );

        }

        return response()->json([
            'status' => 'success',
            'announcement' => $announcement,
        ]);
    }

}
