<?php

namespace App\Http\Livewire;

use App\Models\Announcement;
use Livewire\Component;
use Livewire\Attributes\Validate;
use Illuminate\Http\Request;

class AnnouncementHandler extends Component
{
    #[Validate('required')]
    public $title = '';
    #[Validate('required')]
    public $description = '';
    #[Validate('required')]
    public $visible_type = '';
    public $is_active = false;
    public $announcementId;
    public $eventId;

    public function mount(Request $request)
    {
        $this->eventId = $request->eventId;

        if ($this->announcementId) {
            $announcement = Announcement::find($this->announcementId);
            $this->title = $announcement->title;
            $this->description = $announcement->description;
            $this->visible_type = $announcement->visible_type;
            $this->is_active = $announcement->is_active ? true : false;
        }
    }
    public function create()
    {
        $this->validate();
        Announcement::create([
            'event_id' => $this->eventId,
            'title' => $this->title,
            'description' => $this->description,
            'visible_type' => $this->visible_type,
            'is_active' => $this->is_active
        ]);

        session()->flash('success', 'Announcement created successfully.');
        return redirect()->route('announcements.index', ['eventId' => $this->eventId]);
    }

    public function update()
    {
        $this->validate();
        $announcement = Announcement::find($this->announcementId);
        $announcement->update([
            'title' => $this->title,
            'description' => $this->description,
            'visible_type' => $this->visible_type,
            'is_active' => $this->is_active
        ]);
        session()->flash('success', 'Announcement updated successfully.');
        return redirect()->route('announcements.index', ['eventId' => $this->eventId]);
    }

    public function render()
    {
        return view('livewire.announcement-handler')->layout('layouts.admin');
    }
}
