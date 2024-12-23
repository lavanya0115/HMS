<?php

namespace App\Http\Livewire;

use App\Models\Announcement;
use Livewire\Component;

class AnnouncementsList extends Component
{
    public $announcements = [];

    public function render()
    {
        $this->announcements = getCurrentAnnouncement();
        return view('livewire.announcements-list')->layout('layouts.admin');
    }
}
