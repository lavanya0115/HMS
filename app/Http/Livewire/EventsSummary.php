<?php

namespace App\Http\Livewire;

use App\Models\Event;
use Livewire\Component;
use App\Models\EventVisitor;
use Illuminate\Http\Request;
use Livewire\Attributes\Url;
use Livewire\WithPagination;
use App\Models\EventExhibitor;
use Spatie\Activitylog\Models\Activity;

class EventsSummary extends Component
{
    use WithPagination;

    protected $listeners = [
        'deleteEvent' => 'deleteEventById',
    ];

    protected $paginationTheme = 'bootstrap';

    public $eventId = null;

    #[Url(as: 'pp')]
    public $perPage = 10;

    public $event;

    public function deleteEventById($eventId)
    {
        $event = Event::find($eventId);
        $event->update([
            "deleted_by" => getAuthData()->id,
        ]);
        $isDeleted = $event->delete();
        if ($isDeleted) {
            session()->flash('success', 'Event deleted successfully!');
            return redirect(route('events', ['pp' => $this->perPage, 'page' => $this->paginators['page']]));
        } else {
            session()->flash('error', 'Event deletion failed!');
            return;
        }
    }
    public function mount(Request $request)
    {
        $this->eventId = $request->eventId ?? null;
    }
    public function render()
    {
        $events = Event::orderBy('id', 'desc')
            ->paginate($this->perPage);

        $exhibitorEventsExists = [];
        $visitorEventsExists = [];
        foreach ($events as $event) {
            $isExhibitorEvents = EventExhibitor::where('event_id', $event->id)
                ->exists();

            $isVisitorEvents = EventVisitor::where('event_id', $event->id)
                ->exists();
            $exhibitorEventsExists[$event->id] = $isExhibitorEvents;
            $visitorEventsExists[$event->id] = $isVisitorEvents;
        }

        $events->appends(['perPage' => $this->perPage]);

        $eventActivities = Activity::where('log_name', 'event_log')->orderBy('id', 'desc')->paginate(10, pageName: 'activity');

        return view(
            'livewire.events-summary',
            [
                'eventId' => $this->eventId,
                'events' => $events,
                'exhibitorEventsExists' => $exhibitorEventsExists,
                'visitorEventsExists' => $visitorEventsExists,
                'eventActivities' => $eventActivities,
            ]
        )->layout("layouts.admin");
    }
    public function changePageValue($perPageValue)
    {
        $this->perPage = $perPageValue;
        $this->resetPage();
    }
}
