<?php

namespace App\Http\Livewire;

use App\Models\EventSeminarParticipant;
use App\Models\Seminar;
use Illuminate\Http\Request;
use Livewire\Component;
use Livewire\WithPagination;

class SeminarSummary extends Component
{
    use WithPagination;
    protected $listeners = [
        'deleteSeminar' => 'deleteSeminarById',
    ];

    protected $paginationTheme = 'bootstrap';
    public $eventId;
    public $seminarId = null;

    public function mount(Request $request)
    {
        $this->eventId = $request->eventId;
    }
    public function deleteSeminarById($seminarId)
    {
        $seminar = Seminar::find($seminarId);

        if ($seminar) {
            if ($seminar->event_id == $this->eventId) {
                $seminar->delete();
                session()->flash('success', 'Seminar deleted successfully.');
            } else {
                session()->flash('error', 'Seminar does not belong to this event.');
            }
        } else {
            session()->flash('error', 'Seminar not found.');
        }

        return redirect()->route('seminars', ['eventId' => $this->eventId]);
    }

    public function render()
    {
        $seminars = Seminar::where('event_id', $this->eventId)
            ->orderBy('date')
            ->orderBy('start_time')
            ->paginate(20);

        $visitorSeminarsExists = [];
        foreach ($seminars as $seminar) {
            $isVisitorSeminarsExists = EventSeminarParticipant::where('seminar_id', $seminar->id)
                ->exists();
            $visitorSeminarsExists[$seminar->id] = $isVisitorSeminarsExists;
        }

        return view('livewire.seminar-summary', [
            'seminars' => $seminars,
            'visitorSeminarsExists' => $visitorSeminarsExists

        ])->layout("layouts.admin");
    }

}
