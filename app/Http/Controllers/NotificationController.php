<?php

namespace App\Http\Controllers;

use App\Jobs\GreetingNotificationToParticipatedVisitorsJob;
use App\Jobs\RemainderNotificationJob;
use App\Jobs\SendApplicationNotificationToExhibitorsJob;
use App\Jobs\SendMinimalistNotificationToVisitor;
use App\Jobs\SendNotificationToVisitedVisitorsForHyd24Job;
use App\Jobs\SendNotificationToVisitorsForChennai24EventJob;
use App\Jobs\SendNotificationToVisitorsForHyd2024Job;
use App\Jobs\SendNotificationToVisitorsForMumbai24EventJob;
use App\Jobs\SendUserEngagementNotificationsForKol23Job;
use App\Jobs\SendVisitorAppPromotionNotificationJob;
use App\Jobs\SendVisitorRemainderNotificationVersion1Job;
use App\Jobs\SendVisitorRemainderNotificationVersion2Job;
use App\Models\Event;
use App\Models\Exhibitor;
use App\Models\Visitor;
use Illuminate\Http\Request;

class NotificationController extends Controller
{

    public function sendMinimalNotificationToAllVisitors(Request $request)
    {
        $eventId = $request->event_id ?? null;
        if (!$eventId) {
            $event = getCurrentEvent();
        } else {
            $event = Event::find($eventId);
        }

        $queue = $request->queue ?? '';

        if (empty($event)) {
            return ['status' => 'error', 'message' => 'Event not found'];
        }

        if (empty($queue)) {
            return ['status' => 'error', 'message' => 'Queue name is required'];
        }

        $visitors = Visitor::select('id', 'mobile_number')
            ->whereHas('eventVisitors', function ($q) use ($event) {
                $q->where('event_id', $event->id);
            })->get();

        $queuedCount = 0;

        if ("send_minimalist_notification_to_visitor" == $queue) {
            dispatch(new SendMinimalistNotificationToVisitor('9787480936', null, $event))
                ->onQueue('send_minimalist_notification_to_visitor');
            $queuedCount++;
            foreach ($visitors as $visitor) {
                dispatch(new SendMinimalistNotificationToVisitor($visitor->mobile_number, $visitor, $event))
                    ->onQueue('send_minimalist_notification_to_visitor');
                $queuedCount++;
            }
        } else if ("visitor_remainder_template_for_morning" == $queue) {
            dispatch(new SendVisitorRemainderNotificationVersion1Job('9787480936', null, $event))
                ->onQueue('visitor_remainder_template_for_morning');
            $queuedCount++;
            foreach ($visitors as $visitor) {
                dispatch(new SendVisitorRemainderNotificationVersion1Job($visitor->mobile_number, $visitor, $event))
                    ->onQueue('visitor_remainder_template_for_morning');
                $queuedCount++;
            }
        } else if ("visitor_remainder_template_for_evening" == $queue) {
            dispatch(new SendVisitorRemainderNotificationVersion2Job('9787480936', null, $event))
                ->onQueue('visitor_remainder_template_for_evening');
            $queuedCount++;
            foreach ($visitors as $visitor) {
                dispatch(new SendVisitorRemainderNotificationVersion2Job($visitor->mobile_number, $visitor, $event))
                    ->onQueue('visitor_remainder_template_for_evening');
                $queuedCount++;
            }
        } else if ("visitor_app_promotion_notification" == $queue) {

            dispatch(new SendVisitorAppPromotionNotificationJob('9787480936'))
                ->onQueue('visitor_app_promotion_notification');
            $queuedCount++;
            foreach ($visitors as $visitor) {
                if (empty($visitor->mobile_number)) {
                    continue;
                }
                dispatch(new SendVisitorAppPromotionNotificationJob($visitor->mobile_number))
                    ->onQueue('visitor_app_promotion_notification');
                $queuedCount++;
            }
        } else if ("send_user_engagement_notifications_to_kol_23" == $queue) {
            dispatch(new SendUserEngagementNotificationsForKol23Job('9787480936'))
                ->onQueue('send_user_engagement_notifications_to_kol_23');
            $queuedCount++;

            dispatch(new SendUserEngagementNotificationsForKol23Job('7010886622'))
                ->onQueue('send_user_engagement_notifications_to_kol_23');
            $queuedCount++;

            dispatch(new SendUserEngagementNotificationsForKol23Job('9940623932'))
                ->onQueue('send_user_engagement_notifications_to_kol_23');
            $queuedCount++;

            dispatch(new SendUserEngagementNotificationsForKol23Job('6381926363'))
                ->onQueue('send_user_engagement_notifications_to_kol_23');
            $queuedCount++;

            foreach ($visitors as $visitor) {
                dispatch(new SendUserEngagementNotificationsForKol23Job($visitor->mobile_number))
                    ->onQueue('send_user_engagement_notifications_to_kol_23');
                $queuedCount++;
            }
        }

        return ['status' => 'success', 'message' => 'Queued successfully', 'queued_count' => $queuedCount];
    }
    public function sendNotificationToAllVisitorsForHyderabad24()
    {
        $event = getCurrentEvent();
        $visitors = Visitor::whereHas('eventVisitors', function ($q) use ($event) {
            $q->where('event_id', $event->id);
        })->get();

        $dumpRecord = Visitor::where('mobile_number', '9787480936')->first();
        $dumpRecord2 = Visitor::where('mobile_number', '9788481779')->first();

        $queuedCount = 0;
        dispatch(new SendNotificationToVisitorsForHyd2024Job($dumpRecord, $event))
            ->onQueue('send_notification_to_visitors_for_hyderabad24_event');
        $queuedCount++;

        dispatch(new SendNotificationToVisitorsForHyd2024Job($dumpRecord2, $event))
            ->onQueue('send_notification_to_visitors_for_hyderabad24_event');
        $queuedCount++;

        foreach ($visitors as $visitor) {

            dispatch(new SendNotificationToVisitorsForHyd2024Job($visitor, $event))
                ->onQueue('send_notification_to_visitors_for_hyderabad24_event');

            $queuedCount++;
        }

        return ['status' => 'success', 'message' => 'Queued successfully', 'queued_count' => $queuedCount];

    }

    public function sendFeedbackNotificationToAllVisitedVisitors(Request $request)
    {
        $eventname = $request->eventname ?? '';
        $queuename = $request->queuename ?? '';

        if (empty($eventname) || empty($queuename)) {
            return ['status' => 'error', 'message' => 'Event name and queue name are required'];
        }

        $event = getEventByName($eventname);

        $visitedVisitors = Visitor::whereHas('eventVisitors', function ($q) use ($event) {
            $q->where('event_id', $event->id)
                ->where('is_visited', 1);
        })->get();

        $queuedCount = 0;

        if ("send_notification_to_visitors_for_mumbai24_event" == $queuename) {
            dispatch(new SendNotificationToVisitorsForMumbai24EventJob([
                'send_to' => '9787480936',
            ]))
                ->onQueue('send_notification_to_visitors_for_mumbai24_event');

            $queuedCount++;

            foreach ($visitedVisitors as $visitor) {
                $payload = [
                    'send_to' => $visitor->mobile_number,
                ];

                dispatch(new SendNotificationToVisitorsForMumbai24EventJob($payload))
                    ->onQueue('send_notification_to_visitors_for_mumbai24_event');

                $queuedCount++;
            }
        } else if ("send_notification_to_visitors_for_hyd_24_event" == $queuename) {

            dispatch(new SendNotificationToVisitedVisitorsForHyd24Job([
                'send_to' => '9787480936',
            ]))
                ->onQueue($queuename);

            $queuedCount++;

            foreach ($visitedVisitors as $visitor) {
                $payload = [
                    'send_to' => $visitor->mobile_number,
                ];

                dispatch(new SendNotificationToVisitedVisitorsForHyd24Job($payload))
                    ->onQueue($queuename);

                $queuedCount++;
            }
        } else if ("send_notification_to_visitors_for_chennai_24_event" == $queuename) {

            dispatch(new SendNotificationToVisitorsForChennai24EventJob([
                'send_to' => '9787480936',
            ]))
                ->onQueue($queuename);

            $queuedCount++;

            dispatch(new SendNotificationToVisitorsForChennai24EventJob([
                'send_to' => '6381926363',
            ]))
                ->onQueue($queuename);

            $queuedCount++;

            foreach ($visitedVisitors as $visitor) {
                $payload = [
                    'send_to' => $visitor->mobile_number,
                ];

                dispatch(new SendNotificationToVisitorsForChennai24EventJob($payload))
                    ->onQueue($queuename);

                $queuedCount++;
            }
        }

        return ['status' => 'success', 'message' => 'Queued successfully', 'queued_count' => $queuedCount];
    }
    public function sendRemainderNotificationsToAllUsers(Request $request)
    {
        $type = $request->type ?? 'all';
        $types = explode(',', $type);
        $canSendToVisitors = in_array('visitors', $types) || in_array('all', $types);
        $canSendToExhibitors = in_array('exhibitors', $types) || in_array('all', $types);
        $canSendToExhibitorContactPersons = in_array('exhibitor_contact_persons', $types) || in_array('all', $types);

        if ($canSendToVisitors) {

            $visitors = Visitor::get();
            foreach ($visitors as $visitorIndex => $visitor) {

                $payload = [
                    'name' => $visitor->name,
                    'mobile_number' => $visitor->mobile_number,
                    'user_type' => 'visitor',
                    'send_to' => $visitor->mobile_number,
                ];

                dispatch(new RemainderNotificationJob($payload))
                    ->onQueue('remainder_notification_for_visitors')
                    ->delay(now()->addSeconds(1));
            }
        }

        if ($canSendToExhibitorContactPersons || $canSendToExhibitors) {
            $exhibitors = Exhibitor::with('exhibitorContact')->get();

            foreach ($exhibitors as $exhibitorIndex => $exhibitor) {

                $contactPersonMobileNumber = $exhibitor->exhibitorContact->contact_number ?? '';

                if ($canSendToExhibitorContactPersons) {

                    dispatch(new RemainderNotificationJob([
                        'name' => $exhibitor->name,
                        'mobile_number' => $exhibitor->mobile_number ?? '',
                        'user_type' => 'exhibitor',
                        'send_to' => $contactPersonMobileNumber,
                    ]))
                        ->onQueue('remainder_notification_for_exhibitor_contact_persons')
                        ->delay(now()->addSeconds(1));
                }

                if ($canSendToExhibitors) {

                    dispatch(new RemainderNotificationJob([
                        'name' => $exhibitor->name,
                        'mobile_number' => $exhibitor->mobile_number ?? '',
                        'user_type' => 'exhibitor',
                        'send_to' => $exhibitor->mobile_number,
                    ]))
                        ->onQueue('remainder_notification_for_exhibitors')
                        ->delay(now()->addSeconds(1));
                }
            }
        }

        return ['status' => 'success', 'message' => 'Queued successfully'];
    }

    public function sendGreetingsNotificationsToParticipatedVisitors(Request $request)
    {
        $fileName = $request->file_name ?? '';

        $filePublicPath = public_path('assets/' . $fileName);

        if (!file_exists($filePublicPath)) {
            return response()->json(['status' => 'error', 'message' => 'File not found or incorrect name given'], 404, [], JSON_PRETTY_PRINT);
        }

        $visitors = readCSV($filePublicPath);
        $currentEvent = getCurrentEvent();
        $currentEventTitle = $currentEvent->title ?? '';
        $queuedCount = 0;

        foreach ($visitors as $visitorIndex => $visitor) {

            $name = $visitor['name'] ?? '';
            $mobileNumber = $visitor['number'] ?? '';

            if (empty($mobileNumber)) {
                continue;
            }

            $payload = [
                'name' => $name,
                'eventTitle' => $currentEventTitle,
                'send_to' => $mobileNumber,
                'year' => date('Y'),
            ];

            dispatch(new GreetingNotificationToParticipatedVisitorsJob($payload))
                ->onQueue('send_greetings_to_participated_visitors')
                ->delay(now()->addSeconds(1));

            $queuedCount++;
        }

        $payload = [
            'name' => "Sekar",
            'eventTitle' => $currentEventTitle,
            'send_to' => "9787480936",
            'year' => date('Y'),
        ];

        dispatch(new GreetingNotificationToParticipatedVisitorsJob($payload))
            ->onQueue('send_greetings_to_participated_visitors')
            ->delay(now()->addSeconds(1));
        $queuedCount++;
        return response()->json(['status' => 'success', 'message' => 'Queued successfully', 'queued_count' => $queuedCount], 200, [], JSON_PRETTY_PRINT);
    }

    public function sendApplicationPromotionNotificationToAllExhibitors(Request $request)
    {
        $currentEvent = getCurrentEvent();
        $hallno = $request->hall_no ?? '';

        $exhibitors = Exhibitor::whereHas('eventExhibitors', function ($query) use ($currentEvent, $hallno) {
            $query->where('event_id', $currentEvent->id)
                ->when(!empty($hallno), function ($q) use ($hallno) {
                    $q->where('stall_no', 'like', $hallno . '%');
                });
        })->get();

        $jobs = [];

        $jobs[] = [
            'companyName' => 'Eight One Labs',
            'mobileNumber' => '9787480936',
            'senderMobileNumber' => '9787480936',
        ];

        foreach ($exhibitors as $exhibitor) {
            $jobs[] = [
                'companyName' => $exhibitor->name ?? '',
                'mobileNumber' => $exhibitor->mobile_number ?? '',
                'senderMobileNumber' => $exhibitor->mobile_number ?? '',
            ];

            if (isset($exhibitor->contact_persons) && count($exhibitor->contact_persons)) {
                foreach ($exhibitor->contact_persons as $contactPerson) {

                    if (empty($contactPerson->contact_number)) {
                        continue;
                    }

                    $jobs[] = [
                        'companyName' => $exhibitor->name ?? '',
                        'mobileNumber' => $contactPerson->contact_number ?? '',
                        'senderMobileNumber' => $contactPerson->contact_number ?? '',
                    ];
                }
            }
        }

        $queuedCount = 0;

        foreach ($jobs as $job) {
            dispatch(new SendApplicationNotificationToExhibitorsJob($job))
                ->onQueue('send_application_promotion_notification_to_exhibitors');
            $queuedCount = $queuedCount + 1;
        }

        return response()->json([
            'status' => 'success',
            'queueCount' => $queuedCount,
            'message' => 'Send notifications',
        ]);
    }
}