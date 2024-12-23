<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Wishlist;
use App\Models\Exhibitor;
use Illuminate\Http\Request;
use App\Models\ExhibitorProduct;
use Livewire\WithPagination;

class EventExhibitorProfile extends Component
{
    use WithPagination;
    public $exhibitor;
    public $exhibitorData;
    public $perPage = 10;
    protected $paginationTheme = "bootstrap";
    protected $listeners = ['message' => 'alertStatus'];
    public function mount($eventId, $exhibitorId)
    {
        // dd($eventId, $exhibitorId);
        $this->exhibitor = Exhibitor::find($exhibitorId);
        $this->exhibitorData = $this->exhibitor->eventExhibitors()->where('event_id', $eventId)->first();
    }

    public function addAppointment()
    {
        $this->dispatch('selectedExhibitor', [$this->exhibitor->id, $this->exhibitorData->event_id]);
    }
    public function toggleWishlist()
    {

        $wishlistItem = Wishlist::where('exhibitor_id', $this->exhibitor->id)
            ->where('visitor_id', getAuthData()->id)
            ->where('event_id', $this->exhibitorData->event_id)
            ->first();

        if (!$wishlistItem) {
            Wishlist::create([
                'exhibitor_id' => $this->exhibitor->id,
                'visitor_id' => getAuthData()->id,
                'event_id' => $this->exhibitorData->event_id,
            ]);
            session()->flash('success', 'Exhibitor added to wishlist');
        } else {
            $wishlistItem->delete();
            session()->flash('success', 'Exhibitor removed from wishlist');
        }
    }
    public function targetIdExistsInWishlist()
    {

        $wishlistItem = Wishlist::where('exhibitor_id', $this->exhibitor->id)
            ->where('visitor_id', getAuthData()->id)
            ->where('event_id', $this->exhibitorData->event_id)
            ->first();

        return $wishlistItem ? true : false;
    }
    public function alertStatus($status = null, $message = null)
    {
        if ($status && $message) {
            session()->flash($status, $message);
        }
    }

    public function render()
    {
        $exhibitorProducts = ExhibitorProduct::where('exhibitor_id', $this->exhibitor->id)->paginate($this->perPage);

        return view('livewire.event-exhibitor-profile', [
            'exhibitorProducts' => $exhibitorProducts,
        ])->layout('layouts.admin');
    }

    public function changePageValue($perPageValue)
    {
        $this->perPage = $perPageValue;
        $this->resetPage();
    }
}
