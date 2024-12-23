<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EventVisitor;
use App\Models\Seminar;
use Illuminate\Http\Request;
use App\Models\Visitor;
use Carbon\Carbon;

class VisitorGomenController extends Controller
{
    public function show(Request $request)
    {
        $searchValue = $request->search ?? '';
        $search = trim($searchValue);
        $type = $request->type;
        $paginateCount = $request->paginateCount;
        if (!$type) {
            return response()->json([
                'status' => 'error',
                'message' => 'Type is required...'
            ]);
        }

        $currentEvent = getCurrentEvent();
        if (!$currentEvent) {
            return response()->json([
                'status' => 'error',
                'message' => 'Current Event not found...',
            ], 404);
        }

        $visitors = Visitor::whereHas('eventVisitors', function ($query) use ($currentEvent, $type) {
            $query->where('event_id', $currentEvent->id);

            if ($type == 'whatsapp') {
                $query->where('registration_type', 'whatsapp');
            } else if ($type == 'online-spot') {
                $query->where('registration_type', 'online-spot');
            } else if ($type == 'online') {
                $query->whereNotIn('registration_type', ['whatsapp', 'online-spot']);
            } else if ($type == 'delegate') {
                $query->where('is_delegates', 1);
            }
        });

        if ($search) {
            $visitors->where(function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%')
                    ->orWhere('mobile_number', 'like', '%' . $search . '%')
                    ->orWhereHas('eventVisitors', function ($query) use ($search) {
                        $query->where('_meta->reference_no', 'like', '%' . $search . '%');
                    });
            });
        }
        $visitors = $visitors->orderBy('id', 'desc')->paginate($paginateCount);
        $formattedData = $visitors->map(function ($visitor) {
            $currentEvent = getCurrentEvent();
            $eventVisitor = $visitor->eventVisitors()?->where('event_id', $currentEvent->id)->first();
            return [
                'reg_id' => isset ($eventVisitor->_meta['reference_no']) ? $eventVisitor->_meta['reference_no'] : '',
                'visitor_id' => $visitor->id,
                'salutation' => $visitor->salutation ?? '',
                'name' => $visitor->name ?? '',
                'mobile_number' => $visitor->mobile_number ?? '',
                'email' => $visitor->email ?? '',
                'known_source' => $visitor->nature_of_business ?? '',
                'organization' => $visitor->organization ?? '',
                'designation' => $visitor->designation ?? '',
                'city' => $visitor->address?->city ?? '',
            ];
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Successfull get the List of Visitors.',
            'visitors' => $formattedData,
        ]);

    }

    public function updateVisitor(Request $request)
    {
        $visitorId = $request->visitor_id;
        $visitor = Visitor::find($visitorId);
        if (!$visitor) {
            return response()->json([
                'status' => 'error',
                'message' => 'Visitor not found...',
            ], 404);
        }
        $currentEvent = getCurrentEvent();
        if (!$currentEvent) {
            return response()->json([
                'status' => 'error',
                'message' => 'Current Event not found...',
            ], 404);
        }

        $visitorData = $request->data;
        $isMobileNoExists = Visitor::where('mobile_number', $visitorData['mobile_number'])->where('id', '!=', $visitorId)->first();
        if ($isMobileNoExists) {
            return response()->json([
                'status' => 'error',
                'message' => 'Mobile number already exists...',
            ], 404);
        }
        $isEmailExists = Visitor::where('email', $visitorData['email'])->where('id', '!=', $visitorId)->first();
        if ($isEmailExists) {
            return response()->json([
                'status' => 'error',
                'message' => 'Email already exists...',
            ], 404);
        }

        $visitor->update([
            'salutation' => $visitorData['salutation'] ?? '',
            'name' => $visitorData['name'] ?? '',
            'mobile_number' => $visitorData['mobile_number'] ?? '',
            'email' => $visitorData['email'] ?? '',
            'organization' => $visitorData['organization'] ?? '',
            'designation' => $visitorData['designation'] ?? '',
        ]);

        $visitor->address()->update([
            'city' => $visitorData['city'] ?? '',
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Visitor updated successfully.',
            'visitor' => $visitor
        ]);
    }

    public function getMasterData()
    {
        $currentEvent = getCurrentEvent();
        if (!$currentEvent) {
            return response()->json([
                'status' => 'error',
                'message' => 'Current Event not found...',
            ], 404);
        }
        $currentEventSeminars = Seminar::where('event_id', $currentEvent->id)->where('is_active', 1)->get();

        return response()->json([
            'status' => 'success',
            'message' => 'Successfull get the List of Seminars.',
            'event_id' => $currentEvent->id,
            'seminars' => $currentEventSeminars
        ]);
    }

    public function getDashboardData()
    {

        try {
            $currentEvent = getCurrentEvent();
            if (!$currentEvent) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Current Event not found...',
                ], 404);
            }

            $visitorTotalCount = EventVisitor::when($currentEvent->id != null, function ($query) use ($currentEvent) {
                $query->where('event_id', $currentEvent->id);
            })->count();

            $visitorOnlineCount = EventVisitor::when($currentEvent->id != null, function ($query) use ($currentEvent) {
                $query->where('event_id', $currentEvent->id)
                    ->whereIn('registration_type', ['medicall', 'online']);
            })->count();

            $visitorCRMCount = EventVisitor::when($currentEvent->id != null, function ($query) use ($currentEvent) {
                $query->where('event_id', $currentEvent->id)
                    ->whereIn('registration_type', ['web']);
            })->count();

            $visitorWhatsappCount = EventVisitor::when($currentEvent->id != null, function ($query) use ($currentEvent) {
                $query->where('event_id', $currentEvent->id)
                    ->where('registration_type', 'whatsapp');
            })->count();

            $visitor10tCount = EventVisitor::when($currentEvent->id != null, function ($query) use ($currentEvent) {
                $query->where('event_id', $currentEvent->id)
                    ->where('registration_type', '10t');
            })->count();

            $totalDelegatesCount = EventVisitor::when($currentEvent->id != null, function ($query) use ($currentEvent) {
                $query->where('event_id', $currentEvent->id)
                    ->where('is_delegates', '1');
            })->count();

            $visitorOnSpotCount = EventVisitor::when($currentEvent->id != null, function ($query) use ($currentEvent) {
                $query->where('event_id', $currentEvent->id)
                    ->where('is_delegates', '0')
                    ->where('registration_type', 'online-spot');
            })->count();

            $delegateOnSpotCount = EventVisitor::when($currentEvent->id != null, function ($query) use ($currentEvent) {
                $query->where('event_id', $currentEvent->id)
                    ->where('is_delegates', '1')
                    ->where('registration_type', 'online-spot');
            })->count();

            return response()->json([
                'status' => 'success',
                'message' => 'Successfull get the Dashboard Data.',
                'total_visitors' => $visitorTotalCount,
                'online_visitors' => $visitorOnlineCount,
                'crm_visitors' => $visitorCRMCount,
                'whatsapp_visitors' => $visitorWhatsappCount,
                '10t_visitors' => $visitor10tCount,
                'total_delegates' => $totalDelegatesCount,
                'on_spot_visitors' => $visitorOnSpotCount,
                'on_spot_delegates' => $delegateOnSpotCount
            ]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Something went wrong', 'error' => $e->getMessage()], 500);
        }
    }

    public function getVisitorData(Request $request)
    {
        try {

            $currentEvent = getCurrentEvent();
            if (!$currentEvent) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Current Event not found...',
                ], 404);
            }

            $visitorId = $request->visitor_id;
            $visitor = Visitor::find($visitorId);
            if (!$visitor) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Visitor not found...',
                ], 404);
            }
            $currentEventData = $visitor->eventVisitors()?->where('event_id', $currentEvent->id)->first();
            $data = [
                'id' => $visitor->id ?? null,
                'salutation' => $visitor->salutation ?? '',
                'name' => $visitor->name ?? '',
                'mobile_number' => $visitor->mobile_number ?? '',
                'email' => $visitor->email ?? '',
                'known_source' => $visitor->nature_of_business ?? '',
                'organization' => $visitor->organization ?? '',
                'designation' => $visitor->designation ?? '',
                'city' => $visitor->address->city ?? '',
                'address' => $visitor->address->address ?? '',
                'seminarIds' => $currentEventData->seminars_to_attend ?? [],
                'seminars' => $currentEventData->getSeminarNames(),
                'registration_type' => $currentEventData->registration_type ?? '',
                'is_delegates' => $currentEventData->is_delegates ?? 0,
                'reg_id' => isset($currentEventData->_meta['reference_no']) ? $currentEventData->_meta['reference_no'] : '',
            ];
            return response()->json([
                'status' => 'success',
                'message' => 'Successfull get the List of Visitor Data.',
                'visitor' => $data
            ]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Something went wrong', 'error' => $e->getMessage()], 500);
        }
    }
}
