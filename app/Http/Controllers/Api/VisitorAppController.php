<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Category;
use App\Models\Event;
use App\Models\EventExhibitor;
use App\Models\EventVisitor;
use App\Models\Exhibitor;
use App\Models\PaymentTransaction;
use App\Models\Product;
use App\Models\Visitor;
use App\Models\Wishlist;
use App\Models\MedShorts;
use App\Models\EventSeminarParticipant;
use Illuminate\Http\Request;
use Carbon\Carbon;
use DateTime;
use DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use App\Models\Seminar;
use App\Http\Controllers\OneSignalController;

class VisitorAppController extends Controller
{
    public function index(Request $request)
    {
        $searchValue = $request->search ?? '';
        $search = trim($searchValue);

        $eventId = $request->event_id;
        if (!$eventId) {
            return response()->json([
                'status' => 'error',
                'message' => 'Event id missing...',
            ], 404);
        }

        $event = Event::find($eventId);
        if (!$event) {
            return response()->json([
                'status' => 'error',
                'message' => 'Event not found...',
            ], 404);
        }

        try {
            $productIds = Product::where('name', 'like', '%' . $search . '%')->pluck('id')->toArray();

            $eventExhibitors = EventExhibitor::where('event_id', $eventId)->get();

            $productBasedExhibitors = [];
            $productDetails = [];

            foreach ($eventExhibitors as $eventExhibitor) {
                $productIdsInExhibitor = $eventExhibitor->products;
                if (!empty($productIdsInExhibitor)) {
                    $commonProductIds = array_intersect($productIds, $productIdsInExhibitor);

                    if (!empty($commonProductIds)) {
                        $productBasedExhibitors[] = $eventExhibitor->exhibitor_id;
                        foreach ($commonProductIds as $commonProductId) {
                            if (!isset($productDetails[$commonProductId])) {
                                $product = Product::find($commonProductId);
                                if ($product) {
                                    $productDetails[$commonProductId] = [
                                        'id' => $product->id,
                                        'name' => $product->name,
                                    ];
                                }
                            }
                        }
                    }
                }
            }

            $products = array_values($productDetails);

            $exhibitorsList = Exhibitor::where(function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%')
                    ->orWhereHas('eventExhibitors', function ($subquery) use ($search) {
                        $subquery->where('stall_no', 'like', '%' . $search . '%');
                    });
            })
                ->whereHas('eventExhibitors', function ($query) use ($eventId) {
                    $query->where('event_id', $eventId);
                })
                ->pluck('id')
                ->toArray();

            $exhibitorIds = array_merge($productBasedExhibitors, $exhibitorsList);
            $exhibitorIds = array_unique($exhibitorIds);

            $exhibitors = Exhibitor::whereIn('id', $exhibitorIds)
                ->with([
                    'eventExhibitors' => function ($query) use ($eventId) {
                        $query->where('event_id', $eventId);
                    },
                    'exhibitorContact',
                    'address',
                    'exhibitorProducts'
                ])
                ->orderBy('name', 'asc')
                ->get();

            $data = $exhibitors->map(function ($exhibitor) use ($eventId) {
                $eventExhibitor = $exhibitor->eventExhibitors()?->where('event_id', $eventId)->first();
                $products = [];
                if ($eventExhibitor->products !== null) {
                    foreach ($eventExhibitor->products as $productId) {
                        $product = Product::find($productId);

                        if ($product) {
                            $products[] = [
                                'id' => $productId ?? '',
                                'name' => $product->name ?? '',
                            ];
                        }
                    }
                }
                return [
                    'id' => $exhibitor->id,
                    'username' => $exhibitor->username,
                    'name' =>  strtoupper($exhibitor->name),
                    'logo' => !empty($exhibitor->logo) ? asset('storage/' . $exhibitor->logo) : '',
                    'mobile_number' => $exhibitor->mobile_number,
                    'email' => $exhibitor->email,
                    'website' => $exhibitor->_meta['website_url'] ?? '',
                    'description' => $exhibitor->description ?? '',
                    'category' => $exhibitor->category?->name ?? '',
                    'salutation' => $exhibitor->exhibitorContact?->salutation ?? '',
                    'contact_person' => $exhibitor->exhibitorContact?->name ?? '',
                    'contact_number' => $exhibitor->exhibitorContact?->contact_number ?? '',
                    'designation' => $exhibitor->exhibitorContact?->designation ?? '',
                    'stall_no' => $eventExhibitor->stall_no ?? '',
                    'is_sponsor' => $eventExhibitor->is_sponsorer == 1 ? true : false,
                    'pincode' => $exhibitor->address?->pincode ?? '',
                    'city' => $exhibitor->address?->city ?? '',
                    'state' => $exhibitor->address?->state ?? '',
                    'country' => $exhibitor->address?->country ?? '',
                    'address' => $exhibitor->address?->address ?? '',
                    'event_products' => $products,
                    'products' => $exhibitor->exhibitorProducts->map(function ($exhibitorProduct) {
                        $images = $exhibitorProduct->_meta['images'] ?? [];

                        return [
                            'id' => $exhibitorProduct->product_id ?? '',
                            'name' => $exhibitorProduct->product?->name ?? '',
                            'images' => collect($images)->map(function ($productImage) {

                                return [
                                    'id' => $productImage['id'] ?? '',
                                    'path' => !empty($productImage['filePath']) ? asset('storage/' . $productImage['filePath']) : '',
                                ];
                            }),
                        ];
                    })->toArray(),
                ];
            });
            return response()->json([
                'status' => 'success',
                'exhibitors' => $data,
                'products' => $products,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function wishList(Request $request)
    {
        $visitorId = auth()->user()->id;
        $visitor = Visitor::find($visitorId);
        if (!$visitor) {
            return response()->json([
                'status' => 'error',
                'message' => 'Visitor not found...'
            ]);
        }
        $targetId = $request->targetId;
        if (!$targetId) {
            return response()->json([
                'status' => 'error',
                'message' => 'Target Id missing...',
            ], 404);
        }
        $eventId = $request->event_id;
        if (!$eventId) {
            return response()->json([
                'status' => 'error',
                'message' => 'Event Id missing...',
            ], 404);
        }

        $event = Event::find($eventId);
        if (!$event) {
            return response()->json([
                'status' => 'error',
                'message' => 'Event not found...',
            ], 404);
        }
        $type = $request->type;
        if (!$type) {
            return response()->json([
                'status' => 'error',
                'message' => 'Type not found...',
            ], 404);
        }
        try {
            if ($type == 'product') {
                $wishlistItem = Wishlist::where('product_id', $targetId)
                    ->where('visitor_id', $visitorId)
                    ->where('event_id', $eventId)
                    ->first();

                if (!$wishlistItem) {
                    Wishlist::create([
                        'product_id' => $targetId,
                        'visitor_id' => $visitorId,
                        'event_id' => $eventId,
                    ]);
                    return response()->json([
                        'status' => 'success',
                        'message' => 'Product added to wishlist',
                        'wishListStatus' => true
                    ]);
                } else {
                    $wishlistItem->delete();
                    return response()->json([
                        'status' => 'success',
                        'message' => 'Product removed from wishlist',
                        'wishListStatus' => false
                    ]);
                }
            } else {
                $wishlistItem = Wishlist::where('exhibitor_id', $targetId)
                    ->where('visitor_id', $visitorId)
                    ->where('event_id', $eventId)
                    ->first();

                if (!$wishlistItem) {
                    Wishlist::create([
                        'exhibitor_id' => $targetId,
                        'visitor_id' => $visitorId,
                        'event_id' => $eventId,
                    ]);
                    return response()->json([
                        'status' => 'success',
                        'message' => 'Exhibitor added to wishlist',
                        'wishListStatus' => true
                    ]);
                } else {
                    $wishlistItem->delete();
                    return response()->json([
                        'status' => 'success',
                        'message' => 'Exhibitor removed from wishlist',
                        'wishListStatus' => false
                    ]);
                }
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function getAppointments(Request $request)
    {
        $eventId = $request->event_id;
        if (!$eventId) {
            return response()->json([
                'status' => 'error',
                'message' => 'Event Id missing',
            ]);
        }

        $event = Event::find($eventId);
        if (!$event) {
            return response()->json([
                'status' => 'error',
                'message' => 'Event not found',
            ]);
        }

        $visitorId = auth()->user()->id;
        if (!$visitorId) {
            return response()->json([
                'status' => 'error',
                'message' => 'Visitor not Found',
            ]);
        }

        $visitor = Visitor::find($visitorId);
        if (!$visitor) {
            return response()->json([
                'status' => 'error',
                'message' => 'Visitor not Found',
            ]);
        }

        try {
            $appointments = Appointment::where('event_id', $eventId)
                ->where('visitor_id', $visitorId)->orderBy('scheduled_at', 'desc')
                ->get();

            if (count($appointments) == 0) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No Appointments Found',
                ]);
            }

            $formattedAppointments = $appointments->map(function ($appointment) use ($event) {
                return [
                    'event_id' => $event->id,
                    'event_name' => $event->title ?? '',
                    'id' => $appointment->id,
                    'exhibitor_id' => $appointment->exhibitor_id,
                    'exhibitor_name' => $appointment->exhibitor?->name ?? '',
                    'exhibitor_logo' => !empty($appointment->exhibitor?->logo) ? asset('storage/' . ($appointment->exhibitor?->logo)) : '',
                    'stall_no' => $appointment->eventExhibitorInfo?->stall_no ?? '',
                    'scheduled_at' => $appointment->scheduled_at->isoFormat('llll') ?? '',
                    'feedback' => $appointment->_meta['visitor_feedback']['message'] ?? '',
                    'status' => ucfirst($appointment->status) ?? '',
                ];
            });

            return response()->json([
                'status' => 'success',
                'data' => $formattedAppointments
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function updateAppointmentStatus(Request $request, OneSignalController $oneSignal)
    {

        $appointmentId = $request->appointmentId;
        $status = $request->status;
        $eventId = $request->event_id;
        $visitorId = auth()->user()->id;
        $exhibitorId = $request->exhibitorId;
        $date = $request->date;
        $time = $request->time;
        $scheduledAt = $date . $time;
        $feedback = $request->feedback;
        $purposeOfMeeting = $request->purposeOfMeeting ?? null;
        $source = $request->source;


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
        if (!$visitorId) {
            return response()->json([
                'status' => 'error',
                'message' => 'Visitor Id is Missing',
            ]);
        }
        if ($status !== 'scheduled' && empty($appointmentId)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Appointment Id is Missing',
            ]);
        }

        try {

            $eventData = Event::find($eventId);
            if (!$eventData) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Event not found',
                ]);
            }
            $isRegisteredVisitor = EventVisitor::where('event_id', $eventId)->where('visitor_id', $visitorId)->exists();
            if (!$isRegisteredVisitor) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Visitor is not registered for this event',
                ]);
            }

            if (!empty($exhibitorId)) {
                $isRegisteredExhibitor = EventExhibitor::where('event_id', $eventId)->where('exhibitor_id', $exhibitorId)->exists();
                if (!$isRegisteredExhibitor) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Exhibitor is not registered for this event',
                    ]);
                }
            }

            $appointment = null;
            $receiverEmailData = null;
            $senderEmailData = null;
            $exhibitorMobileNo = null;
            $contactPersonNo = null;
            $message = null;

            if (!empty($appointmentId)) {
                $appointment = Appointment::find($appointmentId);
                if (!$appointment) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Appointment not found',
                    ]);
                }

                $receiverEmailData = [
                    'receiverEmail' => $appointment->exhibitor?->email,
                    'appointmentId' => $appointmentId,
                ];

                $senderEmailData = [
                    'receiverEmail' => $appointment->visitor?->email,
                    'appointmentId' => $appointmentId,
                ];

                $exhibitorMobileNo = $appointment->exhibitor?->mobile_number;
                $contactPersonNo = $appointment->exhibitor?->exhibitorContact?->contact_number;
                $message = 'Appointment' . ' ' . $status . ' with ' . $appointment->exhibitor?->name . ' at ' . $eventData->title;

            }
            if (in_array($status, ['scheduled', 'rescheduled'])) {

                $startDate = new DateTime($eventData->start_date);
                $endDate = new DateTime($eventData->end_date);

                $validation = Validator::make(
                    $request->all(),
                    [
                        'date' => "required|date_format:Y-m-d|after_or_equal:{$startDate->format('Y-m-d')}|before_or_equal:{$endDate->format('Y-m-d')}",
                        'time' => 'required|date_format:H:i|after_or_equal:10:00|before_or_equal:18:00',
                    ]
                );

                if ($validation->fails()) {
                    return response()->json([
                        'status' => 'Validation failed',
                        'message' => 'The given date and time is not valid',
                        'errors' => $validation->errors()->all(),
                    ]);
                }
                if ($status === 'rescheduled') {
                    $appointment->update([
                        'scheduled_at' => $scheduledAt,
                        'status' => $status,
                        'updated_by' => $visitorId,
                        'updated_type' => get_class(auth()->user()),
                    ]);

                    $notificationPayload = [
                        'senderName' => $appointment->visitor?->name ?? '',
                        'receiverName' => $appointment->exhibitor?->name ?? '',
                        'scheduledAt' => Carbon::parse($appointment->scheduled_at)->toFormattedDateString(),
                        'status' => ucfirst($appointment->status),
                    ];
                    sendAppointmentStatusChangeEmail($receiverEmailData, $notificationPayload);
                    sendAppointmentStatusChangeEmail($senderEmailData, $notificationPayload);

                    $oneSignal->sendNotification(
                        'Appointment Rescheduled',
                        $message,
                        $exhibitorMobileNo
                    );

                    $oneSignal->sendNotification(
                        'Appointment Rescheduled',
                        $message,
                        $contactPersonNo
                    );

                    return response()->json([
                        'status' => 'success',
                        'message' => 'Appointment Rescheduled Successfully',
                    ]);

                } else if ($status === 'scheduled') {
                    if (empty($source)) {
                        return response()->json([
                            'status' => 'error',
                            'message' => 'Source is missing',
                        ]);
                    }
                    if (empty($exhibitorId)) {
                        return response()->json([
                            'status' => 'error',
                            'message' => 'Exhibitor Id is missing',
                        ]);
                    }
                    $appointment = new Appointment();
                    $appointment->event_id = $eventId;
                    $appointment->visitor_id = $visitorId;
                    $appointment->exhibitor_id = $exhibitorId;
                    $appointment->scheduled_at = $scheduledAt;
                    $appointment->status = 'scheduled';
                    $appointment->notes = $purposeOfMeeting ?? null;
                    $appointment->source = $source;
                    $appointment->created_by = $visitorId;
                    $appointment->created_type = get_class(auth()->user());
                    $appointment->save();

                    $receiverEmailData = [
                        'receiverEmail' => $appointment->exhibitor?->email,
                        'appointmentId' => $appointment->id,
                    ];

                    $senderEmailData = [
                        'receiverEmail' => $appointment->visitor?->email,
                        'appointmentId' => $appointment->id,
                    ];

                    $exhibitor = Exhibitor::find($appointment->exhibitor_id);
                    $exhibitorContactPersonMobileNumber = $exhibitor->exhibitorContact?->contact_number ?? null;
                    $exhibitorMobileNumber = $exhibitor->mobile_number ?? null;
                    $message = 'Appointment scheduled with ' . $exhibitor->name . 'at' . $eventData->title;

                    $notificationPayload = [
                        'senderName' => $appointment->visitor?->name ?? '',
                        'receiverName' => $appointment->exhibitor?->name ?? '',
                        'scheduledAt' => Carbon::parse($appointment->scheduled_at)->toFormattedDateString(),
                        'status' => ucfirst($appointment->status),
                    ];

                    sendAppointmentInitNotification($exhibitorMobileNumber, $notificationPayload);
                    sendAppointmentInitNotification($exhibitorContactPersonMobileNumber, $notificationPayload);
                    sendAppointmentStatusChangeEmail($receiverEmailData, $notificationPayload);
                    sendAppointmentStatusChangeEmail($senderEmailData, $notificationPayload);

                    $oneSignal->sendNotification(
                        'New Appointment',
                        $message,
                        $exhibitorMobileNumber
                    );
                    $oneSignal->sendNotification(
                        'New Appointment',
                        $message,
                        $exhibitorContactPersonMobileNumber
                    );

                    return response()->json([
                        'status' => 'success',
                        'message' => 'Appointment Scheduled Successfully',
                    ]);
                }

            } else if ($status === 'completed') {

                if (!$feedback) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Feedback is required',
                    ]);
                }
                $visitorFeedback = [
                    "message" => $feedback,
                    "timestamp" => now()->format('Y-m-d H:i:s'),
                ];

                $meta = $appointment->_meta;
                $meta['visitor_feedback'] = $visitorFeedback;

                $appointment->update([
                    '_meta' => $meta,
                    'status' => $status,
                    'completed_at' => Carbon::now(),
                    'completable_id' => $visitorId,
                    'completable_type' => get_class(auth()->user()),

                ]);

                $exhibitor = Exhibitor::find($appointment->exhibitor_id);
                $exhibitorContactPersonMobileNumber = $exhibitor->exhibitorContact?->contact_number ?? null;
                $exhibitorMobileNumber = $exhibitor->mobile_number ?? null;

                $notificationPayload = [
                    'senderName' => $appointment->visitor?->name ?? '',
                    'receiverName' => $appointment->exhibitor?->name ?? '',
                    'scheduledAt' => Carbon::parse($appointment->scheduled_at)->toFormattedDateString(),
                    'status' => ucfirst($appointment->status),
                ];

                sendAppointmentStatusChangeNotification($exhibitorMobileNumber, 'exhibitor', $notificationPayload);
                sendAppointmentStatusChangeNotification($exhibitorContactPersonMobileNumber, 'exhibitor', $notificationPayload);
                sendAppointmentStatusChangeEmail($receiverEmailData, $notificationPayload);
                sendAppointmentStatusChangeEmail($senderEmailData, $notificationPayload);

                $oneSignal->sendNotification(
                    'Appointment Completed',
                    $message,
                    $exhibitorMobileNo
                );

                $oneSignal->sendNotification(
                    'Appointment Completed',
                    $message,
                    $contactPersonNo
                );

                return response()->json([
                    'status' => 'success',
                    'message' => 'Appointment Completed Successfully',
                ]);

            } else if ($status === 'cancelled') {
                $appointment->update([
                    'status' => $status,
                    'cancelled_at' => Carbon::now(),
                    'cancelled_by' => $visitorId,
                    'cancelled_type' => get_class(auth()->user()),
                ]);

                $exhibitor = Exhibitor::find($appointment->exhibitor_id);
                $exhibitorContactPersonMobileNumber = $exhibitor->exhibitorContact?->contact_number ?? null;
                $exhibitorMobileNumber = $exhibitor->mobile_number ?? null;

                $notificationPayload = [
                    'senderName' => $appointment->visitor?->name ?? '',
                    'receiverName' => $appointment->exhibitor?->name ?? '',
                    'scheduledAt' => Carbon::parse($appointment->scheduled_at)->toFormattedDateString(),
                    'status' => ucfirst($appointment->status),
                ];

                sendAppointmentStatusChangeNotification($exhibitorMobileNumber, 'exhibitor', $notificationPayload);
                sendAppointmentStatusChangeNotification($exhibitorContactPersonMobileNumber, 'exhibitor', $notificationPayload);
                sendAppointmentStatusChangeEmail($receiverEmailData, $notificationPayload);
                sendAppointmentStatusChangeEmail($senderEmailData, $notificationPayload);

                $oneSignal->sendNotification(
                    'Appointment Cancelled',
                    $message,
                    $exhibitorMobileNo
                );

                $oneSignal->sendNotification(
                    'Appointment Cancelled',
                    $message,
                    $contactPersonNo
                );

                return response()->json([
                    'status' => 'success',
                    'message' => 'Appointment Cancelled Successfully',
                ]);

            } else if ($status === 'feedback') {

                $visitorFeedback = [
                    "message" => $feedback,
                    "timestamp" => now()->format('Y-m-d H:i:s'),
                ];

                $meta = $appointment->_meta;
                $meta['visitor_feedback'] = $visitorFeedback;
                $appointment->_meta = $meta;
                $appointment->save();

                return response()->json([
                    'status' => 'success',
                    'message' => 'Feedback added successfully',
                ]);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid status',
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function showVisitorData(Request $request)
    {
        $visitorId = auth()->user()->id;
        $visitor = Visitor::find($visitorId);
        if (!$visitor) {
            return response()->json([
                'status' => 'error',
                'message' => 'Visitor not found...'
            ]);
        }
        try {
            $businessTypes = Category::where('type', 'visitor_business_type')->get();
            $logoExists = false;
            if (!empty($visitor->_meta['logo'])) {
                $logoExists = Storage::disk('public')->exists($visitor->_meta['logo']);
            }
            $currentEventId = getCurrentEvent()->id;
            $eventVisitorData = $visitor->eventVisitors()->where('event_id', $currentEventId)->first();
            $regId = !empty($eventVisitorData->_meta['reference_no']) ? $eventVisitorData->_meta['reference_no'] : '';

            $visitorData = [
                'regId' => $regId,
                'username' => $visitor->username ?? '',
                'salutation' => $visitor->salutation ?? '',
                'name' => $visitor->name ?? '',
                'email' => $visitor->email ?? '',
                'mobile_number' => $visitor->mobile_number ?? '',
                'logo' => $logoExists ? asset('storage/' . $visitor->_meta['logo'] ?? '') : '',
                'category_id' => $visitor->category_id ?? '',
                'category_name' => $visitor->category?->name ?? '',
                'organization' => $visitor->organization ?? '',
                'designation' => $visitor->designation ?? '',
                'pincode' => $visitor->address?->pincode ?? '',
                'city' => $visitor->address?->city ?? '',
                'state' => $visitor->address?->state ?? '',
                'country' => $visitor->address?->country ?? '',
                'address' => $visitor->address?->address ?? '',
                'countries' => getCountries(),
                'business_types' => $businessTypes->map(function ($businessType) {
                    return [
                        'id' => $businessType->id ?? '',
                        'name' => $businessType->name ?? '',
                    ];
                }),
                'events' => $visitor->eventVisitors()->orderBy('id', 'desc')->get()->map(function ($eventVisitor) {
                    $products = [];
                    if ($eventVisitor->product_looking !== null && is_array($eventVisitor->product_looking)) {
                        foreach ($eventVisitor->product_looking as $productId) {
                            $product = Product::find($productId);
                            if ($product) {
                                $products[] = [
                                    'id' => $productId ?? '',
                                    'name' => $product->name ?? '',
                                ];
                            }
                        }
                    }
                    return [
                        'id' => $eventVisitor->event_id ?? null,
                        'name' => $eventVisitor->event?->title ?? '',
                        'registration_date' => $eventVisitor->created_at->format('Y-m-d') ?? '',
                        'products' => $products

                    ];
                }),

            ];
            return response()->json(['status' => 'success', 'data' => $visitorData]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function updateVisitorData(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'salutation' => 'required',
            'name' => 'required|regex:/^[a-zA-Z ]+$/',
            'email' => 'required|email',
            'mobile_number' => 'required',
            'category_id' => 'required',
            'designation' => 'required',
            'organization' => 'required',
            'country' => 'required',
            'pincode' => 'required',
            'address' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'status' => 'error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $visitorId = auth()->user()->id;
        $visitor = Visitor::find($visitorId);
        if (!$visitor) {
            return response()->json([
                'status' => 'error',
                'message' => 'Visitor not found...'
            ]);
        }

        $isEmailExists = Visitor::where('email', $request->email)->where('id', '!=', $visitorId)->exists();
        if ($isEmailExists) {
            return response()->json([
                'status' => 'error',
                'message' => 'Email already exists...'
            ]);
        }

        $isMobileNumberExists = Visitor::where('mobile_number', $request->mobile_number)->where('id', '!=', $visitorId)->exists();
        if ($isMobileNumberExists) {
            return response()->json([
                'status' => 'error',
                'message' => 'Mobile number already exists...'
            ]);
        }

        try {
            DB::beginTransaction();

            $visitor->update([
                'salutation' => $request->salutation,
                'name' => $request->name,
                'email' => $request->email,
                'mobile_number' => $request->mobile_number,
                'category_id' => $request->category_id,
                'designation' => $request->designation,
                'organization' => $request->organization,
            ]);

            $visitor->address()->update([
                'country' => $request->country,
                'pincode' => $request->pincode,
                'city' => $request->city,
                'state' => $request->state,
                'address' => $request->address,
            ]);

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Visitor data updated successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    public function updateLogo(Request $request)
    {
        $visitorId = auth()->user()->id;
        $visitor = Visitor::find($visitorId);
        if (!$visitor) {
            return response()->json([
                'status' => 'error',
                'message' => 'Visitor not found...'
            ]);
        }
        $logo = $request->logo;
        if (!$logo) {
            return response()->json([
                'status' => 'error',
                'message' => 'Logo not found...'
            ]);
        }
        try {
            $imageData = base64_decode($logo);
            // Remove previous logo
            if (!empty($visitor->_meta['logo'])) {
                $filepath = public_path('storage/' . $visitor->_meta['logo']);
                if (file_exists($filepath)) {
                    unlink($filepath);
                }
            }

            // Convert base64 to file and store using Storage
            $imageFolderPath = 'visitor/' . date('Y/m');
            $imageName = Str::random(10) . '.png';
            $filePath = $imageFolderPath . '/' . $imageName;

            // Store the file using the Storage facade
            Storage::disk('public')->put($filePath, $imageData);

            $meta = $visitor->_meta ?? [];
            $meta['logo'] = $filePath;
            $visitor->_meta = $meta;
            $visitor->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Logo updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }

    }

    public function updateEventProducts(Request $request)
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
                'message' => 'Event id missing...'
            ]);
        }
        $event = Event::find($eventId);
        if (!$event) {
            return response()->json([
                'status' => 'error',
                'message' => 'Event not found...'
            ]);
        }
        $products = $request->products ?? [];
        $visitor->eventVisitors()->where('event_id', $eventId)->update([
            'product_looking' => $products
        ]);
        return response()->json([
            'status' => 'success',
            'message' => 'Products updated successfully'
        ]);

    }

    public function registerForSeminar(Request $request)
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
                'message' => 'Event id missing...'
            ]);
        }
        $event = Event::find($eventId);
        if (!$event) {
            return response()->json([
                'status' => 'error',
                'message' => 'Event not found...'
            ]);
        }
        $seminarId = $request->seminarId;
        $seminar = Seminar::where('id', $seminarId)->where('event_id', $eventId)->where('is_active', 1)->first();
        if (!$seminar) {
            return response()->json(['status' => 'error', 'message' => 'Seminar not found.'], 404);
        }

        $paymentStatus = $request->payment_status;
        if (!$paymentStatus) {
            return response()->json(['status' => 'error', 'message' => 'Payment status missing.'], 404);
        }

        $razorpay_payment_id = $request->razorpay_payment_id ?? '';
        $razorpay_order_id = $request->razorpay_order_id ?? '';
        $razorpay_signature = $request->razorpay_signature ?? '';
        $responseData = $request->response_data ?? [];
        $failureReason = $request->failure_reason ?? '';
        $transactionStatus = $request->transaction_status;

        if ($paymentStatus == "paid" && empty($transactionStatus)) {
            return response()->json(['status' => 'error', 'message' => 'Transaction status missing.'], 404);
        }
        try {
            $eventVisitor = EventVisitor::where('visitor_id', $visitorId)->where('event_id', $eventId)->first();
            if (!$eventVisitor) {
                return response()->json(['status' => 'error', 'message' => 'Visitor not registered for this event.'], 404);
            }

            $seminarsToAttend = EventSeminarParticipant::where('event_id', $eventId)->where('visitor_id', $visitorId)
                ->where('seminar_id', $seminarId)->first();

            if (empty($seminarsToAttend)) {
                $amount = Seminar::find($seminarId)->amount;
                $seminarRegister = new EventSeminarParticipant();
                $seminarRegister->event_id = $eventId;
                $seminarRegister->visitor_id = $visitorId;
                $seminarRegister->seminar_id = $seminarId;
                $seminarRegister->amount = $amount;
                $seminarRegister->payment_status = $transactionStatus == 'Success' ? 'paid' : 'pay_later';
                $seminarRegister->payment_type = $transactionStatus == 'Success' ? 'online' : '';
                $seminarRegister->save();
            } else {
                $seminarsToAttend->payment_status = $transactionStatus == 'Success' ? 'paid' : 'pay_later';
                $seminarsToAttend->payment_type = $transactionStatus == 'Success' ? 'online' : '';
                $seminarsToAttend->save();
            }

            $eventVisitor->is_delegates = 1;
            $visitorSeminar = $eventVisitor->_meta ?? [];
            $visitorSeminar['converted_into_delegates'] = "yes";
            $eventVisitor->_meta = $visitorSeminar;
            $eventVisitor->save();

            if (!empty($transactionStatus)) {
                $transaction = new PaymentTransaction();
                $transaction->razorpay_payment_id = $razorpay_payment_id;
                $transaction->razorpay_order_id = $razorpay_order_id;
                $transaction->razorpay_signature = $razorpay_signature;
                $transaction->status = $transactionStatus;
                $transaction->_meta = $responseData;
                $transaction->failure_reason = $failureReason;
                $transaction->payable()->associate($visitor);
                $transaction->save();
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Seminar registered successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ]);
        }

    }

    public function getWishList(Request $request)
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
                'message' => 'Event not found...'
            ]);
        }
        $event = Event::find($eventId);
        if (!$event) {
            return response()->json([
                'status' => 'error',
                'message' => 'Event not found...'
            ]);
        }
        try {
            $exhibitorWishlists = Wishlist::where('event_id', $eventId)
                ->where('visitor_id', $visitorId)
                ->where('product_id', null)
                ->get();
            $productWishlists = Wishlist::where('event_id', $eventId)
                ->where('visitor_id', $visitorId)
                ->whereNull('exhibitor_id')
                ->get();

            $whislistExhibitorIds = $exhibitorWishlists->pluck('exhibitor_id')->toArray();
            $wishlistProductIds = $productWishlists->pluck('product_id')->toArray();

            $productNames = Product::whereIn('id', $wishlistProductIds)->pluck('name');
            $similarProducts = [];
            if ($productWishlists->count() > 0) {
                $similarProducts = Product::where(function ($query) use ($productNames, $wishlistProductIds) {
                    foreach ($productNames as $productName) {
                        $splitProduct = explode(' ', $productName);

                        foreach ($splitProduct as $product) {
                            $query->orWhere('name', 'like', '%' . $product . '%');
                        }
                    }
                })
                    ->whereNotIn('id', $wishlistProductIds)
                    ->get();
            }

            $productsFromExhibitors = EventExhibitor::whereIn('exhibitor_id', $whislistExhibitorIds)
                ->where('event_id', $eventId)
                ->get();

            $exhibitorProductsCollection = $productsFromExhibitors->pluck('products')->toArray();

            $exhibitorProducts = collect($exhibitorProductsCollection)->flatten()->unique()->toArray();

            $similarExhibitors = [];

            if ($exhibitorWishlists->count() > 0) {
                $similarExhibitors = EventExhibitor::whereNotIn('exhibitor_id', $whislistExhibitorIds)
                    ->where('event_id', $eventId)
                    ->where(function ($subquery) use ($exhibitorProducts) {
                        foreach ($exhibitorProducts as $productId) {
                            $subquery->orWhereJsonContains('products', strval($productId));
                        }
                    })
                    ->get();
            }
            $exhibitorWishlist = collect($exhibitorWishlists)->map(function ($wishlist) {
                return [
                    'id' => $wishlist->id,
                    'exhibitor_id' => $wishlist->exhibitor_id,
                    'exhibitor_name' => $wishlist->exhibitor?->name,
                    'event_id' => $wishlist->event_id,
                ];
            });
            $productWishlist = collect($productWishlists)->map(function ($wishlist) {
                return [
                    'product_id' => $wishlist->product_id,
                    'product_name' => $wishlist->product?->name,
                ];
            });
            $similarExhibitor = collect($similarExhibitors)->map(function ($exhibitor) {
                return [
                    'exhibitor_id' => $exhibitor->exhibitor_id,
                    'exhibitor_name' => $exhibitor->exhibitor?->name,
                    'event_id' => $exhibitor->event_id,
                ];
            });
            $similarProduct = collect($similarProducts)->map(function ($product) {
                return [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                ];
            });
            $data = [
                'exhibitorWishlists' => $exhibitorWishlist,
                'productWishlists' => $productWishlist,
                'similarExhibitors' => $similarExhibitor,
                'similarProducts' => $similarProduct,
            ];

            return response()->json([
                'status' => 'success',
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ]);
        }
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
                'image' => !empty($seminar->_meta['thumbnail']) ? asset('storage/' . ($seminar->_meta['thumbnail'])) : ''
            ];
        });
        return response()->json([
            'status' => 'success',
            'currentEventId' => $currentEvent->id,
            'currentAndPreviousEvents' => $formattedEvents,
            'currentAndUpcomingEvents' => $formattedUpcomingEvents,
            'seminars' => $seminarsList
        ]);
    }

    public function getExhibitorDetails(Request $request)
    {

        $exhibitorId = $request->exhibitor_id;
        if (!$exhibitorId) {
            return response()->json([
                'status' => 'error',
                'message' => 'Exhibitor Id missing',
            ]);
        }
        $exhibitor = Exhibitor::find($exhibitorId);
        if (!$exhibitor) {
            return response()->json([
                'status' => 'error',
                'message' => 'Exhibitor not found',
            ]);
        }

        $eventId = $request->event_id;
        if (!$eventId) {
            return response()->json([
                'status' => 'error',
                'message' => 'Event Id missing',
            ]);
        }

        $event = Event::find($eventId);
        if (!$event) {
            return response()->json([
                'status' => 'error',
                'message' => 'Event not found',
            ]);
        }


        $eventExhibitor = $exhibitor->eventExhibitors()?->where('event_id', $eventId)->first();
        $products = [];
        if (!empty($eventExhibitor->products)) {
            foreach ($eventExhibitor->products as $productId) {
                $product = Product::find($productId);

                if ($product) {
                    $products[] = [
                        'id' => $productId ?? '',
                        'name' => $product->name ?? '',
                    ];
                }
            }
        }
        return [
            'id' => $exhibitor->id,
            'username' => $exhibitor->username,
            'name' => $exhibitor->name,
            'logo' => !empty($exhibitor->logo) ? asset('storage/' . $exhibitor->logo) : '',
            'mobile_number' => $exhibitor->mobile_number,
            'email' => $exhibitor->email,
            'website' => $exhibitor->_meta['website_url'] ?? '',
            'description' => $exhibitor->description ?? '',
            'category' => $exhibitor->category?->name ?? '',
            'salutation' => $exhibitor->exhibitorContact?->salutation ?? '',
            'contact_person' => $exhibitor->exhibitorContact?->name ?? '',
            'contact_number' => $exhibitor->exhibitorContact?->contact_number ?? '',
            'designation' => $exhibitor->exhibitorContact?->designation ?? '',
            'stall_no' => $eventExhibitor->stall_no ?? '',
            'pincode' => $exhibitor->address?->pincode ?? '',
            'city' => $exhibitor->address?->city ?? '',
            'state' => $exhibitor->address?->state ?? '',
            'country' => $exhibitor->address?->country ?? '',
            'address' => $exhibitor->address?->address ?? '',
            'event_products' => $products,
            'products' => $exhibitor->exhibitorProducts->map(function ($exhibitorProduct) {
                $images = $exhibitorProduct->_meta['images'] ?? [];

                return [
                    'id' => $exhibitorProduct->product_id ?? '',
                    'name' => $exhibitorProduct->product?->name ?? '',
                    'images' => collect($images)->map(function ($productImage) {

                        return [
                            'id' => $productImage['id'] ?? '',
                            'path' => !empty($productImage['filePath']) ? asset('storage/' . $productImage['filePath']) : '',
                        ];
                    }),
                ];
            })->toArray(),
        ];
    }

    public function getMedshorts(Request $request)
    {
        $medshorts = Medshorts::orderBy('id', 'desc')->get();
        return response()->json([
            'status' => 'success',
            'medshorts' => $medshorts
        ]);
    }
}

