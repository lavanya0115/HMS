<?php

namespace App\Http\Livewire;

use App\Models\Event;
use App\Models\Stall;
use Livewire\Component;
use Livewire\Attributes\Url;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Request;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Pagination\LengthAwarePaginator;

class StallSummary extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $stallId = null;

    #[Url(as: 'pp')]
    public $perPage = 10;

    public $event_id;
    // public function mount()
    // {
    //     $this->event_id = request()->event_id ?? null;
    //     // dump($this->event_id);
    // }
    public function changePageValue($perPageValue)
    {
        $this->perPage = $perPageValue;
        $this->resetPage();
    }

    public function deleteEventStall($stallId)
    {
        $stall = Stall::find($stallId);

        if (!$stall) {
            session()->flash('error', 'Stall not found.');
            return redirect()->back();
        }

        try {
            $stall->delete();
            session()->flash('success', 'Stall details deleted successfully.');
            return redirect()->route('stall-summary');
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to delete stall. Please try again later.');
            return redirect()->back();
        }
    }

    public function mount()
    {
        $this->event_id = getCurrentEvent()->id;
    }

    public function render()
    {
        $stalls  =  new LengthAwarePaginator([], 0, $this->perPage);
        if (!empty($this->event_id)) {
            $stalls = Stall::where('event_id', $this->event_id)->orderBy('id', 'desc')
                ->paginate($this->perPage);
        }

        $events = Event::select('title', 'event_description', 'id')
            // ->whereNotNull('event_description')
            ->orderBy('id', 'desc')->get();
        $stallActivities = Activity::where('log_name', 'stall_log')->orderBy('id', 'desc')->paginate(10, pageName: 'activity');

        return view('livewire.stall-summary', [
            'stalls' => $stalls,
            'events' => $events,
            'stallActivities' => $stallActivities,
        ])->layout("layouts.admin");
    }
}
