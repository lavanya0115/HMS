<?php

namespace App\Http\Livewire;

use App\Models\EventVisitor;
use App\Models\Seminar;
use Illuminate\Http\Request;
use Livewire\Component;
use Livewire\WithPagination;

class SeminarList extends Component
{
    use WithPagination;
    public $eventId;
    public function mount(Request $request)
    {
        $this->eventId = $request->eventId;

    }
    public function render()
    {
        $seminars = Seminar::where('is_active', 1)
            ->orderBy('id', 'desc')
            ->get();

        $seminars = $seminars->map(function ($seminar) {
            $delegatesCount = EventVisitor::where('is_delegates', 1)
                ->whereJsonContains('seminars_to_attend', strval($seminar['id']))
                ->count();
            $seminar = $seminar->toArray();
            $seminar['delegates_count'] = $delegatesCount;
            return $seminar;
        });

        return view('livewire.seminar-list', [
            'seminars' => $seminars,
        ])->layout("layouts.admin");
    }
}
