<?php

namespace App\Http\Controllers\Api\V2;

use App\Models\Event;
use App\Models\Visitor;
use App\Models\Category;
use App\Models\EventVisitor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Jobs\SendVisitorAppPromotionNotificationJob;

class VisitorController extends Controller
{
    public function store(Request $request)
    {
        Log::info("[Visitor Registration V2]", $request->all());
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'mobile_number' => 'required|string',
            'email' => 'nullable|email',
            'organization' => 'nullable|string',
            'designation' => 'nullable|string',
            'address' => 'nullable|string',
            'source' => 'nullable|string',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'status' => 'error',
                'errors' => $validator->errors(),
                'type' => 'VALIDATION_FAILED',
            ], 422);
        }

        $salutation = preg_replace('/[^a-zA-Z0-9]/', '', $request->salutation ?? 'Mr');
        $name = $request->name ?? '';
        $email = $request->email ?? '';
        $mobileNumber = $request->mobile_number ?? '';
        $mobileNumber = str_replace(' ', '', $mobileNumber);
        $organization = $request->organization ?? '';
        $designation = $request->designation ?? '';
        $source = $request->source ?? 'web';
        $selected_event_ids = $request->selected_event_ids ?? '';
        $selectedEventIds = explode(',', $selected_event_ids);

        $address = $request->address ?? '';
        $city = $request->city ?? '';
        $state = $request->state ?? '';
        $country = $request->country ?? '';

        $natureOfBusiness = $request->nature_of_business ?? '';
        $reasonForVisit = $request->reason_for_visit ?? '';
        $selectedKnownSource = $request->known_source ?? '';
        $dateOfRegistration = $request->dateOfRegistration ?? '';
        $referenceNo = $request->reference_no ?? '';
        $seminars = $request->seminars ?? null;
        $is_delegate = $request->is_delegate ?? 0;

        $requiredQr = $request->required_qr ?? 'no';
        $requiredQr = strtolower($requiredQr);

        $defaultPassword = config('app.default_user_password');
        $username = getUniqueUsernameFromGivenName($name);
        $currentEvent = getCurrentEvent();

        $selectedEvents = Event::whereIn('id', $selectedEventIds)->get();

        $category = null;
        Log::info('Nature of Business: ' . $natureOfBusiness);
        if ($natureOfBusiness != '') {

            $category = Category::where('type', 'visitor_business_type')
                ->where('name', 'like', '%' . $natureOfBusiness . '%')
                ->first();

            if (!$category) {
                $category = Category::create([
                    'name' => $natureOfBusiness,
                    'type' => 'visitor_business_type',
                    'is_active' => 1,
                ]);
            }
        }

        $knownSourceKey = '';
        if (is_numeric($selectedKnownSource)) {
            $selectedKnownSource = getKnownSourceDataById($selectedKnownSource);
        }

        foreach (getKnownSourceData() as $key => $knownSourceValue) {
            if ($knownSourceValue == $selectedKnownSource) {
                $knownSourceKey = $key;
                break;
            }
        }
        $knownSourceKey = !empty($knownSourceKey) ? $knownSourceKey : $selectedKnownSource;

        $visitorData = [
            'salutation' => $salutation,
            'username' => $username,
            'password' => Hash::make($defaultPassword),
            'name' => $name,
            'mobile_number' => $mobileNumber,
            'email' => $email,
            'organization' => $organization,
            'designation' => $designation,
            'registration_type' => $source,
            'category_id' => $category->id ?? null,
            'reason_for_visit' => $reasonForVisit,
            'known_source' => $knownSourceKey,
        ];

        $eventMeta = [
            'event_id' => $currentEvent->id ?? 0,
            'registration_type' => $source,
            'is_delegates' => $is_delegate,
            'seminars_to_attend' => $seminars,
            'known_source' => $knownSourceKey,
            '_meta' => [
                'dateOfRegistration' => $dateOfRegistration ?? '',
                'reference_no' => $referenceNo,
                'is_welcome_notification_sent' => 'no',
            ],
        ];
        Log::info("Registering visitor data through API");
        Log::info('Visitor Data: ' . json_encode($visitorData));
        Log::info('Event Meta: ' . json_encode($eventMeta));

        $visitor = Visitor::where('mobile_number', $mobileNumber)
            ->first();

        if ($visitor) {

            Log::info('Visitor already registered');
            Log::info('Selected Event Ids: ' . json_encode($selectedEventIds));
            $registeredEvents = [];
            if (count($selectedEvents) > 0) {
                Log::info('Registering visitor for selected events');
                foreach ($selectedEvents as $event) {

                    $registeredEvents[] = $event;

                    $eventVisitor = EventVisitor::where('event_id', $event->id)
                        ->where('visitor_id', $visitor->id)
                        ->first();

                    if ($eventVisitor) {
                        $eventVisitor->registration_type = $source;
                        $eventVisitor->is_delegates = $is_delegate;
                        $eventVisitor->seminars_to_attend = $seminars;
                        $eventVisitor->known_source = $knownSourceKey;
                        $eventVisitor->save();
                    } else {
                        $eventMeta['event_id'] = $event->id;
                        $visitor->eventVisitors()->create($eventMeta);
                    }
                }
            } else {
                Log::info('Registering visitor for current event');
                $visitor->eventVisitors()->create($eventMeta);
                $registeredEvents[] = $currentEvent;
            }

            foreach ($registeredEvents as $event) {
                $isCurrentEvent = $event->id == $currentEvent->id;
                if ($isCurrentEvent) {
                    sendVisitorAppNotification($mobileNumber);
                    // sendWhatsappNotificationAfterRegister($visitor, $event);
                }
                sendEmailToMedicallTeamWhenRegisterVisitor($visitor, $event);
            }
            // SendVisitorAppPromotionNotificationJob::dispatch($mobileNumber);
            // sendWhatsappNotificationAfterRegister($visitor, $currentEvent);
            // sendEmailToMedicallTeamWhenRegisterVisitor($visitor, $currentEvent);
            // TODO: Use job & queue to send welcome message
            // sendWelcomeMessageThroughWhatsappBot($mobileNumber, 'visitor');

            return response()->json([
                'message' => 'Visitor already registered for the event',
                'status' => 'success',
                'type' => 'VISITOR_ALREADY_REGISTERED_FOR_EVENT',
                'data' => $visitor ?? [],
            ]);
        }

        try {
            $visitor = Visitor::create($visitorData);

            if (!$visitor) {
                Log::info('Something went wrong to register visitor');
                return response()->json([
                    'message' => 'Something went wrong to register visitor',
                    'status' => 'error',
                    'type' => 'VISITOR_REGISTRATION_FAILED',
                ], 500);
            }

            // TODO: Use job & queue to send welcome message
            // sendWelcomeMessageThroughWhatsappBot($mobileNumber, 'visitor');

            $visitor->address()->create([
                'address' => $address,
                'city' => $city,
                'state' => $state,
                'country' => $country,
            ]);

            $registeredEvents = [];

            if (count($selectedEvents) > 0) {
                foreach ($selectedEvents as $event) {
                    $eventMeta['event_id'] = $event->id;
                    $visitor->eventVisitors()->create($eventMeta);
                    $registeredEvents[] = $event;
                }
            } else {
                $visitor->eventVisitors()->create($eventMeta);
                $registeredEvents[] = $currentEvent;
            }

            foreach ($registeredEvents as $event) {
                $isCurrentEvent = $event->id == $currentEvent->id;
                sendEmailToMedicallTeamWhenRegisterVisitor($visitor, $event);
                if ($isCurrentEvent) {
                    // sendWhatsappNotificationAfterRegister($visitor, $event);
                }
            }

            if ("yes" == $requiredQr) {
                $url = 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=' . $visitor->id;

                $qrImageContent = file_get_contents($url);
                // store it public directory again the event
                $directory = '/qr_codes/' . $currentEvent->id;
                $path = public_path($directory);
                if (!file_exists($path)) {
                    mkdir($path, 0777, true);
                }
                $filename = $visitor->id . '.png';
                file_put_contents($path . '/' . $filename, $qrImageContent);
                $qrCodePublicPath = asset($directory . '/' . $filename);
            }

            Log::info('Visitor successfully registered');
            return response()->json([
                'message' => 'Visitor successfully registered',
                'status' => 'success',
                'type' => 'VISITOR_REGISTRATION_SUCCESS',
                'qr_url' => $qrCodePublicPath ?? '',
                'data' => $visitor ?? [],
            ], 201);
        } catch (\Exception $e) {
            Log::error('Exception: ' . $e->getMessage());
            return response()->json([
                'message' => 'Something went wrong',
                'status' => 'error',
                'type' => 'EXCEPTION_ERROR',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
