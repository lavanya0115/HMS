<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Event; // Import Event model
use App\Models\Exhibitor;
use App\Models\EventExhibitor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use DB;

class ExhibitorEventDashboardController extends Controller
{

    public function getEventDashboardData(Request $request, $eventId)
    {
        $exhibitorId = auth()->user()->id;

        try {
            $currentEvent = Event::find($eventId);
            if (!$currentEvent) {
                return response()->json(['status' => 'error', 'message' => 'Event not found'], 404);
            }

            $exhibitor = EventExhibitor::where('event_id', $eventId)->where('exhibitor_id', $exhibitorId)->first();
            if (!$exhibitor) {
                return response()->json(['status' => 'error', 'message' => 'Exhibitor not registered for this event'], 404);
            }

            $totalAppointmentCount = Appointment::where('event_id', $eventId)->where('exhibitor_id', $exhibitorId)->count();
            $scheduledCount = Appointment::where('event_id', $eventId)->where('exhibitor_id', $exhibitorId)->where('status', 'scheduled')->count();
            $rescheduledCount = Appointment::where('event_id', $eventId)->where('exhibitor_id', $exhibitorId)->where('status', 'rescheduled')->count();
            $confirmedCount = Appointment::where('event_id', $eventId)->where('exhibitor_id', $exhibitorId)->where('status', 'confirmed')->count();
            $lapsedCount = Appointment::where('event_id', $eventId)->where('exhibitor_id', $exhibitorId)->where('status', 'no-show')->count();
            $cancelledCount = Appointment::where('event_id', $eventId)->where('exhibitor_id', $exhibitorId)->where('status', 'cancelled')->count();
            $completedCount = Appointment::where('event_id', $eventId)->where('exhibitor_id', $exhibitorId)->where('status', 'completed')->count();
            $pendingAppointments = Appointment::where('exhibitor_id', $exhibitorId)
                ->where('event_id', $eventId)
                ->whereIn('status', ['scheduled', 'rescheduled'])
                ->with('visitor')
                ->select('id', 'visitor_id', 'scheduled_at', 'notes')
                ->get();
            $formattedPendingAppointments = [];
            foreach ($pendingAppointments as $appointment) {
                $formattedPendingAppointments[] = [
                    'id' => $appointment->id,
                    'visitor_name' => $appointment->visitor->name ?? '',
                    'organization' => $appointment->visitor->organization,
                    'place' => $appointment->visitor->address->place,
                    'purpose' => $appointment->notes,
                    'scheduled_date_time' => $appointment->scheduled_at->isoFormat('llll'),
                    'confirm_appointment' => $appointment->status == 'confirmed',
                ];
            }
            $hallLayout = !empty($currentEvent->_meta['layout']) ? asset('storage/' . $currentEvent->_meta['layout']) : '';

            $stallNo = $exhibitor->stall_no ?? '';

            return response()->json([
                'status' => 'success',
                'message' => 'Exhibitor Dashboard',
                'data' => [
                    'current_event_id' => $currentEvent->id,
                    'current_event_title' => $currentEvent->title,
                    'total_appointments_count' => $totalAppointmentCount,
                    'scheduled_count' => $scheduledCount,
                    'rescheduled_count' => $rescheduledCount,
                    'confirmed_count' => $confirmedCount,
                    'lapsed_count' => $lapsedCount,
                    'cancelled_count' => $cancelledCount,
                    'completed_count' => $completedCount,
                    'pending_appoinments' => $formattedPendingAppointments,
                    'hall_layout' => $hallLayout,
                    'stall_no' => $stallNo,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Something went wrong', 'error' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'event_id' => 'required',
            'company_name' => 'required',
            'mobile_number' => 'required|unique:exhibitors,mobile_number|regex:/^[0-9]*$/',
            'email' => 'required|email|unique:exhibitors,email',
            'salutation' => 'required',
            'contact_person' => 'required|regex:/^[a-zA-Z ]+$/',
            'contact_number' => 'required|unique:exhibitor_contacts,contact_number|regex:/^[0-9]*$/',
            'known_source' => 'required',
            'country' => 'required',
            'pincode' => 'required',
        ]);


        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'status' => 'error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $eventIds = $request->event_id;
        $companyName = $request->company_name;
        $mobileNumber = $request->mobile_number;
        $email = $request->email;
        $salutation = $request->salutation;
        $contactPerosn = $request->contact_person;
        $contactNumber = $request->contact_number;
        $knownSource = $request->known_source;
        $country = $request->country;
        $pincode = $request->pincode;
        $city = $request->city ?? '';
        $state = $request->state ?? '';

        $userName = Str::replace(' ', '', $companyName);
        $userName = $userName . rand(9999, 9999999);

        try {

            DB::beginTransaction();

            $exhibitor = Exhibitor::create([
                'username' => $userName,
                'name' => $companyName,
                'mobile_number' => $mobileNumber,
                'email' => $email,
                'known_source' => $knownSource,
                'password' => Hash::make(config('app.default_user_password')),
                'registration_type' => 'mobile',
            ]);

            $exhibitor->exhibitorContact()->create([
                'salutation' => $salutation,
                'name' => $contactPerosn,
                'contact_number' => $contactNumber,
            ]);

            foreach ($eventIds as $eventId) {
                $exhibitor->eventExhibitors()->create([
                    'event_id' => $eventId,
                ]);
            }

            $exhibitor->address()->create([
                'country' => $country,
                'pincode' => $pincode,
                'city' => $city,
                'state' => $state,
            ]);

            DB::commit();
            sendWelcomeMessageThroughWhatsappBot($exhibitor->mobile_number, 'exhibitor');

            return response()->json([
                'status' => 'success',
                'message' => 'Exhibitor registered successfully',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ]);
        }

    }
}

