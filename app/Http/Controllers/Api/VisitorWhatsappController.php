<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\Appointment;
use App\Models\Event;
use App\Models\EventExhibitor;
use App\Models\EventVisitor;
use App\Models\Exhibitor;
use App\Models\Product;
use App\Models\Visitor;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class VisitorWhatsappController extends Controller
{
    public function getAppointmentsByMobilenumber($mobileNumber)
    {

        $mobileNumber = preg_replace('/[^0-9]/', '', $mobileNumber);
        $mobileNumber = substr($mobileNumber, -10);
        $visitor = Visitor::where('mobile_number', 'LIKE', '%' . $mobileNumber . '%')->first();

        if (!$visitor) {
            return response()->json([
                'status' => 'error',
                'type' => 'VISITOR_NOT_FOUND',
                'message' => 'Visitor not found with the provided mobile number.',
            ], 404);
        }

        $currentEventId = getCurrentEvent()->id ?? 0;
        // Retrieve the most recent event associated with the visitor
        $mostRecentEvent = EventVisitor::where('visitor_id', $visitor->id)
            ->where('event_id', $currentEventId)
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$mostRecentEvent) {
            return response()->json([
                'status' => 'error',
                'type' => 'VISITOR_NOT_REGISTERED',
                'message' => 'Visitor is not for current event.',
            ], 404);
        }

        // Retrieve appointments for the visitor for the current event
        $appointments = Appointment::where('visitor_id', $visitor->id)
            ->where('event_id', $currentEventId)
            ->where('status', '!=', 'canceled')
            ->orderBy('scheduled_at', 'desc')
            ->cursorPaginate(10);

        $appointmentsData = $appointments->map(function ($appointment) {
            $status_label = $appointment->status ?? '';

            if ($appointment->status == 'scheduled') {
                $status_label = 'Confirmed';
            } elseif ($appointment->status == 'rescheduled') {
                $status_label = 'Rescheduled and confirmed';
            }

            return [
                'appointment_id' => $appointment->id,
                'exhibitor_name' => $appointment->exhibitor->name ?? '',
                'stall_no' => $appointment->eventExhibitorInfo->stall_no ?? '',
                'scheduled_on' => $appointment->scheduled_at->format('Y-m-d H:i:s'),
                'notes' => $appointment->notes,
                'status' => $appointment->status,
                'status_label' => $status_label,
            ];
        });

        return response()->json([
            'status' => 'success',
            'type' => 'VISITOR_APPOINTMENTS',
            'appointments' => $appointmentsData,
            'nextPageCursorValue' => $this->getCursorValueFromGivenUrl($appointments->nextPageUrl()),
            'nextPageUrl' => $appointments->nextPageUrl(),

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
    //get product Related exhibitor
    public function getExhibitorsByProduct(Request $request)
    {
        $productId = $request->product_id ?? 0;
        $currentEventId = getCurrentEvent()->id ?? 0;
        $eventExhibitorIds = [];

        $eventExhibitorIds = EventExhibitor::where('event_id', $currentEventId)
            ->whereJsonContains('products', strval($productId))
            ->pluck('exhibitor_id')
            ->toArray();

        $exhibitors = Exhibitor::whereIn('id', $eventExhibitorIds)->orderBy('id')
            ->cursorPaginate(10);

        $formattedExhibitors = $exhibitors->map(function ($exhibitor) {
            $eventExhibitor = $exhibitor->eventExhibitors->where('event_id', getCurrentEvent()->id)->first();
            return [
                'exhibitor_id' => $exhibitor->id,
                'exhibitor_name' => $exhibitor->name,
                'stall_no' => $eventExhibitor->stall_no,
                'exhibitor_product' => $eventExhibitor->getProductNames(),
            ];
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Related Exhibitor.',
            'exhibitors' => $formattedExhibitors,
            'nextPageCursorValue' => $this->getCursorValueFromGivenUrl($exhibitors->nextPageUrl()),
            'nextPageUrl' => $exhibitors->nextPageUrl(),
        ]);
    }
    //Appointment status rechedule and cance
    public function rescheduleAppointment($appointmentId, Request $request)
    {
        $newAppointmentDate = $request->new_appointment_date;
        $newAppointmentTime = $request->new_appointment_time;

        $appointment = Appointment::find($appointmentId);

        if (!$appointment) {
            return response()->json([
                'status' => 'error',
                'type' => 'APPOINTMENT_NOT_FOUND', // To identify the type of error
                'message' => 'Appointment not found.',
            ]);
        }

        // TODO: Confirm it is necessary to check the status of the appointment before rescheduling
        // if ($appointment->status !== 'scheduled') {
        //     return response()->json([
        //         'status' => 'error',
        //         'message' => 'Cannot reschedule appointment. It may already be canceled or rescheduled.',
        //     ]);
        // }

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

    public function cancelAppointment($appointmentId)
    {
        $appointment = Appointment::find($appointmentId);

        if (!$appointment) {
            return response()->json([
                'status' => 'error',
                'type' => 'APPOINTMENT_NOT_FOUND', // To identify the type of error
                'message' => 'Appointment not found.',
            ]);
        }

        $appointment->status = 'canceled';
        $appointment->save();

        return response()->json([
            'status' => 'success',
            'type' => 'APPOINTMENT_CANCELED',
            'message' => 'Appointment canceled successfully.',
            'appointment' => $appointment,
        ]);
    }

    //Find Product
    public function searchProducts($search)
    {
        $currentEventId = getCurrentEvent()->id ?? 0;
        $products = Product::where('name', 'like', '%' . $search . '%')
            ->orderBy('name', 'asc')->cursorPaginate(10);

        $productData = [];

        foreach ($products as $product) {
            $foundInEventExhibitor = EventExhibitor::whereJsonContains('products', strval($product->id))
                ->where('event_id', $currentEventId)
                ->first();

            if ($foundInEventExhibitor) {
                $productData[] = [
                    'id' => $product->id,
                    'name' => $product->name,
                ];
            }
        }

        return response()->json([
            'products' => $productData,
            'nextPageCursorValue' => $this->getCursorValueFromGivenUrl($products->nextPageUrl()),
            'nextPageUrl' => $products->nextPageUrl(),
        ]);
    }
//Search Exhibitor
    public function searchExhibitors($search)
    {
        $currentEventId = getCurrentEvent()->id ?? 0;
        if (!$currentEventId) {
            return response()->json([
                'status' => 'error',
                'message' => 'No current event found.',
            ], 404);
        }

        $exhibitors = Exhibitor::where('name', 'like', '%' . $search . '%')
            ->whereHas('eventExhibitors', function ($query) use ($currentEventId) {
                $query->where('event_id', $currentEventId);
            })->orderBy('id')
            ->cursorPaginate(10);

        $formattedExhibitors = $exhibitors->map(function ($exhibitor) {
            $eventExhibitor = $exhibitor->eventExhibitors->where('event_id', getCurrentEvent()->id)->first();
            return [
                'exhibitor_id' => $exhibitor->id,
                'exhibitor_name' => $exhibitor->name,
                'stall_no' => $eventExhibitor->stall_no,
                'exhibitor_product' => $eventExhibitor->getProductNames(),
            ];
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Get exhibitors successfully',
            'exhibitors' => $formattedExhibitors,
            'nextPageCursorValue' => $this->getCursorValueFromGivenUrl($exhibitors->nextPageUrl()),
            'nextPageUrl' => $exhibitors->nextPageUrl(),
        ]);
    }

    public function makeAppointment(Request $request)
    {
        $appointmentDate = $request->appointment_date;
        $appointmentTime = $request->appointment_time;
        $visitorMobileNumber = $request->mobile_number ?? 0;
        $visitorId = $request->visitor_id ?? 0;

        $visitorMobileNumber = preg_replace('/[^0-9]/', '', $visitorMobileNumber);
        $visitorMobileNumber = substr($visitorMobileNumber, -10);

        $currentEvent = getCurrentEvent() ?? null;
        if (!$currentEvent) {
            return response()->json(
                [
                    'status' => 'error',
                    'message' => 'Event Not Found',
                ]
            );
        }

        $visitor = Visitor::when(!empty($visitorMobileNumber), function ($q) use ($visitorMobileNumber) {
            return $q->where('mobile_number', $visitorMobileNumber);
        })->when(!empty($visitorId), function ($q) use ($visitorId) {
            return $q->where('id', $visitorId);
        })
            ->first();

        if (!$visitor) {
            return response()->json(
                [
                    'status' => 'error',
                    'message' => 'Visitor Not Register for Current Event',
                ]
            );
        }

        $registeredVisitor = EventVisitor::where('visitor_id', $visitor->id)
            ->where('event_id', $currentEvent->id)
            ->first();
        if (!$registeredVisitor) {
            return response()->json(
                [
                    'status' => 'error',
                    'message' => 'Visitor Not Registered for Current Event',
                ]
            );
        }

        $registeredExhibitor = EventExhibitor::where('exhibitor_id', $request->exhibitor_id)->where('event_id', $currentEvent->id)->first();
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

        $eventStartDate = strtotime(date('Y-m-d', strtotime($currentEvent->start_date)));
        $eventEndDate = strtotime(date('Y-m-d', strtotime($currentEvent->end_date)));
        $appointmentDateTimestamp = strtotime(date('Y-m-d', strtotime($appointmentDate)));

        if ($appointmentDateTimestamp < $eventStartDate || $appointmentDateTimestamp > $eventEndDate) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid appointment date. Please provide a date within the event dates.',
            ]);
        }

        $appointment = new Appointment();
        $appointment->event_id = $currentEvent->id;
        $appointment->visitor_id = $visitor->id;
        $appointment->exhibitor_id = $request->exhibitor_id;
        $appointment->scheduled_at = date('Y-m-d H:i:s', strtotime($appointmentDate . " " . $appointmentTime));
        $appointment->status = 'scheduled';
        $appointment->source = 'whatsapp';
        $appointment->save();

        if ($appointment) {
            $notificationPayload = [
                'senderName' => $visitor->name ?? '',
                'receiverName' => $registeredExhibitor->exhibitor->name ?? '',
                'scheduledAt' => Carbon::parse($appointment->scheduled_at)->toFormattedDateString(),
                'status' => ucfirst($appointment->status),
            ];

            $exhibitor = Exhibitor::with('exhibitorContact')->find($request->exhibitor_id);
            $exhibitorContactPersonMobileNumber = $exhibitor->exhibitorContact->contact_number ?? null;
            $exhibitorMobileNumber = $exhibitor->mobile_number ?? null;
            $receiverEmailData = [
                'receiverEmail' => $exhibitor->email,
                'appointmentId' => $appointment->id,
            ];
            $senderEmailData = [
                'receiverEmail' => $visitor->email,
                'appointmentId' => $appointment->id,
            ];

            sendAppointmentInitNotification($exhibitorContactPersonMobileNumber, $notificationPayload);
            sendAppointmentInitNotification($exhibitorMobileNumber, $notificationPayload);
            sendAppointmentStatusChangeEmail($receiverEmailData, $notificationPayload);
            sendAppointmentStatusChangeEmail($senderEmailData, $notificationPayload);
        }

        return response()->json(
            [
                'status' => 'success',
                'message' => 'Appointment Created Successfully',
                'params' => $request->all(),
                'appointment' => $appointment,
            ]
        );
    }

    public function checkUserExits(Request $request)
    {
        $currentEvent = getCurrentEvent() ?? null;
        $phone_no = $request->input('phone_no', '') ?? '';
        $formatedMobileNumber = substr($phone_no, -10);

        $visitor = Visitor::where('mobile_number', 'LIKE', '%' . $formatedMobileNumber)
            ->first();
        if (!$visitor) {
            return response()->json([
                'status' => 'error',
                'type' => 'VISITOR_NOT_FOUND',
                'message' => 'Visitor not found',
            ]);
        }

        $visitorInfo = [
            'visitor_id' => $visitor->id,
            'name' => $visitor->name,
            'mobile_no' => $visitor->mobile_number,
            'email' => $visitor->email,
            'Organization' => $visitor->organization,
            'designation' => $visitor->designation,
            'city' => $visitor->address->city ?? '',
        ];
        if ($visitor) {
            $isRegisteredForCurrentEvent = EventVisitor::where('visitor_id', $visitor->id)
                ->where('event_id', $currentEvent->id)
                ->exists();
            if ($isRegisteredForCurrentEvent) {

                return response()->json([
                    'status' => 'success',
                    'type' => 'VISITOR_REGISTERED',
                    'message' => 'Visitor exists and is registered for the current event',
                    'visitorInfo' => $visitorInfo,
                ]);
            } else {
                return response()->json([
                    'status' => 'success',
                    'type' => 'VISITOR_NOT_REGISTERED',
                    'message' => 'Visitor exists but is not registered for the current event',
                    'visitorInfo' => $visitorInfo,
                ]);
            }

        }
    }

    public function registerVisitor(Request $request)
    {
        Log::info("Registering visitor for selected events");

        $visitor_id = $request->input('visitor_id', 0);
        $known_source = $request->input('known_source');
        $phone_no = $request->input('phone_no', '') ?? '';
        $formatedMobileNumber = substr($phone_no, -10);
        $currentEventId = getCurrentEvent()->id;
        $usage_engagement_event = $request->input('usage_engagement_event');

        // Find visitor by ID or mobile number
        $visitor = Visitor::where('id', $visitor_id)->first();
        if (!$visitor) {
            $visitor = Visitor::where('mobile_number', 'LIKE', '%' . $formatedMobileNumber)->first();
        }

        Log::info("Visitor: " . json_encode($visitor));

        if (!$visitor) {
            return response()->json([
                'status' => 'error',
                'message' => 'Visitor not found',
            ]);
        }

        $selected_event_ids = $request->input('selected_event_ids');
        $selectedEventIds = $selected_event_ids ? explode(',', $selected_event_ids) : [$currentEventId];

        $selectedEvents = Event::whereIn('id', $selectedEventIds)->get();
        $registeredEvents = [];

        if ($selectedEvents->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'No valid events found for registration',
            ]);
        }

        Log::info('Registering visitor for selected events');

        foreach ($selectedEvents as $event) {
            $registeredEvents[] = $event;

            $eventVisitor = EventVisitor::where('event_id', $event->id)
                ->where('visitor_id', $visitor->id)
                ->first();

            if (!$eventVisitor) {
                $eventVisitor = new EventVisitor();
                $eventVisitor->visitor_id = $visitor->id;
                $eventVisitor->event_id = $event->id;
                $eventVisitor->known_source = $known_source ?? 'WhatsApp';
                $eventVisitor->registration_type = 'whatsapp';
                $eventVisitor->_meta = [
                    'usage_engagement_event' => $usage_engagement_event ?? '',
                ];
                $eventVisitor->save();
            } else {
                // Update existing registration
                $eventVisitor->known_source = $known_source ?? 'WhatsApp';
                $eventVisitor->registration_type = 'whatsapp';

                $currentMeta = $eventVisitor->_meta ?? [];
                $currentMeta['usage_engagement_event'] = $usage_engagement_event ?? '';
                $eventVisitor->_meta = $currentMeta;

                $eventVisitor->save();
            }
        }

        // Generate QR code for the visitor
        $qrcodePath = generateQrForVisitor($visitor->mobile_number);

        return response()->json([
            'status' => 'success',
            'message' => 'Visitor registered successfully',
            'registeredEvents' => $registeredEvents,
            'qrcode_path' => $qrcodePath,
        ]);
    }

    public function updateVisitor(Request $request)
    {
        $visitor_id = $request->input('visitor_id');
        $type = $request->input('type');
        $value = $request->input('value');

        $visitor = Visitor::find($visitor_id);

        if (!$visitor) {
            return response()->json([
                'status' => 'error',
                'message' => 'Visitor not found',
            ]);
        }

        if ($type == 'change_city') {
            $address = Address::where('addressable_id', $visitor_id)->first();
            if (!$address) {
                $address = new Address();
                $address->addressable_id = $visitor_id;
                $address->addressable_type = 'App\Models\Visitor';
                $address->city = $value;
                $address->save();
            } else {
                if ($address->city != $value) {
                    $address->city = $value;
                    $address->save();
                }
            }
        } elseif ($type == 'change_mobile_number') {
            if ($visitor->mobile_number != $value) {
                $visitor->mobile_number = $value;
            }
        } elseif ($type == 'change_email') {
            if ($visitor->email != $value) {
                $visitor->email = $value;
            }

        } elseif ($type == 'change_company') {
            if ($visitor->organization != $value) {
                $visitor->organization = $value;
            }
        } elseif ($type == 'change_designation') {
            if ($visitor->designation != $value) {
                $visitor->designation = $value;
            }
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid field type',
            ]);
        }

        $visitor->save();
        $visitorData = [
            'id' => $visitor->id,
            'name' => $visitor->name,
            'email' => $visitor->email,
            'mobile_number' => $visitor->mobile_number,
            'organization' => $visitor->organization,
            'city' => $visitor->city,
        ];
        return response()->json([
            'status' => 'success',
            'message' => 'Visitor updated successfully',
            'visitor' => $visitorData,
        ]);
    }

    public function searchExhibitorsByProduct(Request $request)
    {
        $searchTerm = $request->search_term ?? '';
        $eventId = $request->event_id ?? 0;

        if (empty($searchTerm)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Product name is required',
            ]);
        }

        if (empty($eventId)) {
            $eventId = getCurrentEvent()->id ?? 0;
        }

        $isValidEvent = Event::where('id', $eventId)->exists();
        if (!$isValidEvent) {
            return response()->json([
                'status' => 'error',
                'message' => 'Event not exists or given the invalid event id',
            ]);
        }

        $exhibitors = Exhibitor::with('products.product')
            ->whereHas('eventExhibitors', function ($query) use ($eventId) {
                $query->where('event_id', $eventId);
            })
            ->where(function ($q) use ($searchTerm) {
                $q->whereHas('products', function ($query) use ($searchTerm) {
                    $query->whereHas('product', function ($query) use ($searchTerm) {
                        $query->where('name', 'like', '%' . $searchTerm . '%');
                    });
                })
                    ->orWhere('name', 'like', '%' . $searchTerm . '%');
            })
            ->orderBy('name', 'asc')
            ->cursorPaginate(10);

        $totalNoOfExhibitors = Exhibitor::with('products.product')
            ->whereHas('eventExhibitors', function ($query) use ($eventId) {
                $query->where('event_id', $eventId);
            })
            ->where(function ($q) use ($searchTerm) {
                $q->whereHas('products', function ($query) use ($searchTerm) {
                    $query->whereHas('product', function ($query) use ($searchTerm) {
                        $query->where('name', 'like', '%' . $searchTerm . '%');
                    });
                })
                    ->orWhere('name', 'like', '%' . $searchTerm . '%');
            })->count();

        $formattedExhibitors = $exhibitors->map(function ($exhibitor) use ($eventId) {
            $eventExhibitor = $exhibitor->eventExhibitors->where('event_id', $eventId)->first();
            $products = isset($exhibitor->products) ? $exhibitor->products->map(function ($product) {
                return [
                    'product_name' => $product->product->name ?? '',
                ];
            }) : [];

            $products = collect($products)->pluck('product_name')->toArray();

            $products = count($products) > 0 ? implode(', ', $products) : '';

            return [
                'exhibitor_id' => $exhibitor->id,
                'exhibitor_name' => $exhibitor->name,
                'exhibitor_desc' => $exhibitor->description ?? '',
                'stall_no' => $eventExhibitor->stall_no ?? '',
                'products' => $products,
            ];
        });
        $nextPageUrl = $exhibitors->nextPageUrl();
        $nextPageCursorValue = $this->getCursorValueFromGivenUrl($nextPageUrl);

        return response()->json([
            'status' => 'success',
            'message' => 'Get exhibitors successfully',
            'exhibitors' => $formattedExhibitors,
            'nextPageCursorValue' => $nextPageCursorValue,
            'nextPageUrl' => $nextPageUrl,
            'totalNoOfExhibitors' => $totalNoOfExhibitors,
        ]);
    }
}
