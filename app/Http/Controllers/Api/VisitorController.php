<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\EventVisitor;
use App\Models\Seminar;
use App\Models\Visitor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class VisitorController extends Controller
{
    public function store(Request $request)
    {
        Log::info("[Register Visitor]", $request->all());
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

        Log::info("Registor Visitor Request: " . json_encode($request->all()));

        $salutation = $request->salutation ?? 'Mr';
        $name = $request->name ?? '';
        $email = $request->email ?? '';
        $mobileNumber = $request->mobile_number ?? '';
        $mobileNumber = str_replace(' ', '', $mobileNumber);
        $organization = $request->organization ?? '';
        $designation = $request->designation ?? '';
        $source = $request->source ?? 'web';

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

        $seminarIds = [];
        if (!empty($seminars)) {
            $extractSeminars = explode(',', $seminars);
            foreach ($extractSeminars as $seminar) {
                $seminarIds = Seminar::where('title', 'like', '%' . $seminar . '%')
                    ->where('event_id', $currentEvent->id)
                    ->pluck('id')
                    ->toArray();
            }
        }

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
            'seminars_to_attend' => $seminarIds,
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
            $eventVisitor = $visitor->eventVisitors()->where('event_id', $currentEvent->id ?? 0)->first();

            if ($eventVisitor) {
                Log::info('Visitor already registered for the event');
                $participatedSeminars = $eventVisitor->seminars_to_attend ?? [];
                if (!empty($participatedSeminars)) {
                    $seminarIds = array_merge($participatedSeminars, $seminarIds);
                }
                $eventVisitor->registration_type = $source;
                $eventVisitor->is_delegates = $is_delegate;
                $eventVisitor->seminars_to_attend = $seminarIds;
                $eventVisitor->known_source = $knownSourceKey;
                $eventVisitor->save();

                foreach ($seminarIds as $seminarId) {
                    $visitor->eventDelegates()->firstOrCreate([
                        'event_id' => $currentEvent->id,
                        'visitor_id' => $visitor->id,
                        'seminar_id' => $seminarId,
                    ]);
                }

                // sendWhatsappNotificationAfterRegister($visitor, $currentEvent);
                sendVisitorAppNotification($mobileNumber);
                sendEmailToMedicallTeamWhenRegisterVisitor($visitor, $currentEvent);

                return response()->json([
                    'message' => 'Visitor already registered for the event',
                    'status' => 'success',
                    'type' => 'VISITOR_ALREADY_REGISTERED_FOR_EVENT',
                    'data' => $visitor ?? [],
                ]);
            } else {

                Log::info('Visitor already exists but not registered for the event');
                $visitor->eventVisitors()->create($eventMeta);

                foreach ($seminarIds as $seminarId) {
                    $visitor->eventDelegates()->firstOrCreate([
                        'event_id' => $currentEvent->id,
                        'visitor_id' => $visitor->id,
                        'seminar_id' => $seminarId,
                    ]);
                }

                // sendWhatsappNotificationAfterRegister($visitor, $currentEvent);
                sendVisitorAppNotification($mobileNumber);
                sendEmailToMedicallTeamWhenRegisterVisitor($visitor, $currentEvent);
                // TODO: Use job & queue to send welcome message
                // sendWelcomeMessageThroughWhatsappBot($mobileNumber, 'visitor');
                return response()->json([
                    'message' => 'Visitor successfully registered',
                    'status' => 'success',
                    'type' => 'VISITOR_EXISTS_BUT_REGISTERED_FOR_EVENT',
                    'data' => $visitor ?? [],
                ], 201);
            }
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

            $visitor->eventVisitors()->create($eventMeta);

            foreach ($seminarIds as $seminarId) {
                $visitor->eventDelegates()->firstOrCreate([
                    'event_id' => $currentEvent->id,
                    'visitor_id' => $visitor->id,
                    'seminar_id' => $seminarId,
                ]);
            }
            sendVisitorAppNotification($mobileNumber);

            // sendWhatsappNotificationAfterRegister($visitor, $currentEvent);
            sendEmailToMedicallTeamWhenRegisterVisitor($visitor, $currentEvent);

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

    public function updateVisitorCheckedInStatus(Request $request)
    {
        Log::info('Called updateVisitorCheckedInStatus Method. Request: ' . json_encode($request->all()));
        $mobileNumber = $request->mobile_number ?? '';
        Log::info('Mobile number: ' . $mobileNumber);

        if (empty($mobileNumber)) {
            return response()->json([
                'message' => 'Mobile number is required',
                'status' => 'error',
            ], 422);
        }

        $mobileNumber = preg_replace('/[^0-9]/', '', $mobileNumber);
        $mobileNumber = substr($mobileNumber, -10);
        Log::info('Formatted Mobile number: ' . $mobileNumber);
        $visitor = Visitor::where('mobile_number', 'LIKE', '%' . $mobileNumber . '%')->first();

        if (!$visitor) {
            Log::info('Visitor not found');
            return response()->json([
                'message' => 'Visitor not found',
                'status' => 'error',
            ], 404);
        }

        $currentEvent = getCurrentEvent();

        $eventVisitor = EventVisitor::where('event_id', $currentEvent->id ?? 0)
            ->where('visitor_id', $visitor->id)
            ->first();

        if (!$eventVisitor) {
            Log::info('Visitor not registered for this event');
            return response()->json([
                'message' => 'Visitor not registered for this event',
                'status' => 'error',
            ], 404);
        }
        $eventVisitor->is_visited = 1;
        $eventVisitor->visited_at = now();
        $eventVisitor->save();

        if ($eventVisitor) {
            Log::info('Visitor checked in successfully');
        }

        return response()->json([
            'message' => 'Visitor checked in successfully',
            'status' => 'success',
        ]);
    }
}
