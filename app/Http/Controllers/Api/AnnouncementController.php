<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\Event;
use Illuminate\Http\Request;

class AnnouncementController extends Controller
{
    public function getAnnouncements()
    {
        $currentEvent = getCurrentEvent();
        $currentEventId = $currentEvent->id;

        $announcements = Announcement::where('event_id', $currentEventId)->where('visible_type', '!=', 'visitors_only')
            ->where('is_active', 1)
            ->orderBy('created_at', 'desc')
            ->select('id', 'title', 'description')
            ->get();

        return response()->json([
            'status' => "success",
            'data' => $announcements,
        ], 200);
    }

    public function getVisitorsAnnouncements()
    {
        $currentEvent = getCurrentEvent();
        $currentEventId = $currentEvent->id;

        $announcements = Announcement::where('event_id', $currentEventId)->where('visible_type', '!=', 'exhibitors_only')
            ->where('is_active', 1)
            ->orderBy('created_at', 'desc')
            ->select('id', 'title', 'description')
            ->get();

        return response()->json([
            'status' => "success",
            'data' => $announcements,
        ], 200);
    }

    public function showAnnouncement(Request $request)
    {
        $eventId = $request->event_id;
        $event = Event::find($eventId);
        if (empty($event)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Event not found...',
            ]);
        }

        $announcements = Announcement::where('event_id', $eventId)->where('is_active', 1)
            ->orderBy('created_at', 'desc')
            ->get();

        $data = $announcements->map(function ($announcement) {
            return [
                'id' => $announcement->id,
                'title' => $announcement->title,
                'description' => $announcement->description,
                'image' => $announcement->image ?? '',
                'visible_type' => $announcement->visible_type,
            ];
        });

        return response()->json([
            'status' => "success",
            'data' => $data,
        ]);
    }
}
