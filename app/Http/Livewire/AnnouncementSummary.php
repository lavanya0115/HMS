<?php

namespace App\Http\Livewire;

use App\Models\Announcement;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Http\Request;

class AnnouncementSummary extends Component
{
    use WithPagination;

    public $eventId;

    public function mount(Request $request)
    {
        $this->eventId = $request->eventId;
    }
    public function render()
    {
        $announcements = Announcement::where('event_id', $this->eventId)->orderBy('id', 'desc')->paginate(10);
        return view('livewire.announcement-summary', compact('announcements'))->layout('layouts.admin');
    }
    public function delete($id)
    {
        $announcement = Announcement::find($id);
        $announcement->delete();
        session()->flash('success', 'Announcement deleted successfully.');
        return redirect()->back();
    }
}
