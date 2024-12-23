<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventVisitor;
use Carbon\Carbon;
use Carbon\CarbonPeriod; // Assuming your Event model is located in this namespace
use Illuminate\Http\Request;

// Assuming your EventVisitor model is located in this namespace

class GeneralController extends Controller
{
    public function sendEventData(Request $request)
    {
        $currentEventId = getCurrentEvent()->id;
        $currentEvent = Event::find($currentEventId);

        if ($currentEvent) {
            $startDate = Carbon::parse($currentEvent->start_date)->startOfDay();
            $endDate = Carbon::parse($currentEvent->end_date)->startOfDay();

            $eventDates = CarbonPeriod::create($startDate, $endDate);

            $eventVisitorData = [];

            $todayDate = now()->format('Y-m-d');


            $onlineVisitedCount = EventVisitor::where('event_id', $currentEvent->id)
                ->whereDate('updated_at', $todayDate)
                ->where('registration_type', 'medicall')
                ->where('is_visited', 1)
                ->where('is_delegates', '0')
                ->count();

            $spotVisitedCount = EventVisitor::where('event_id', $currentEvent->id)
                ->whereDate('updated_at', $todayDate)
                ->where('registration_type', 'spot')
                ->where('is_visited', 1)
                ->where('is_delegates', '0')
                ->count();

            $whatsappVisitedCount = EventVisitor::where('event_id', $currentEvent->id)
                ->whereDate('updated_at', $todayDate)
                ->where('registration_type', 'whatsapp')
                ->where('is_visited', 1)
                ->where('is_delegates', '0')
                ->count();

            $total10tVisitedCount = EventVisitor::where('event_id', $currentEvent->id)
                ->whereDate('updated_at', $todayDate)
                ->where('registration_type', '10t')
                ->where('is_visited', 1)
                ->where('is_delegates', '0')
                ->count();

            $delegatesVisitedCount = EventVisitor::where('event_id', $currentEvent->id)
                ->whereDate('updated_at', $todayDate)
                ->where('is_delegates', 1)
                ->where('is_visited', 1)
                ->count();

            $totalVisitedCount = $onlineVisitedCount + $spotVisitedCount + $whatsappVisitedCount + $total10tVisitedCount;

            $reportData = [
                'online_visited_count' => $onlineVisitedCount,
                'spot_visited_count' => $spotVisitedCount,
                'whatsapp_visited_count' => $whatsappVisitedCount,
                '10t_visited_count' => $total10tVisitedCount,
                'delegates_visited_count' => $delegatesVisitedCount,
                'total_visited_count' => $totalVisitedCount,
            ];

            $response = sendEventInfoEmailToMedicallTeam($reportData);
            return response()->json($response);
        } else {
            return response()->json(['error' => 'Event not found'], 404);
        }
    }

}