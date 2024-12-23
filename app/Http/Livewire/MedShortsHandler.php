<?php

namespace App\Http\Livewire;

use App\Models\MedShorts;
use Livewire\Component;

class MedShortsHandler extends Component
{

    public $linkId = null;
    public $link;

    public function create()
    {
        $this->authorize('Create MedShorts');
        $rssFeeder = new MedShorts();
        $rssFeeder->link = $this->link;
        $rssFeeder->save();
        session()->flash('success', 'Created successfully.');
        return redirect(route('med-shorts'));
    }

    public function delete($id)
    {
        $medShort = MedShorts::find($id);
        $medShort->delete();
        session()->flash('success', 'Deleted successfully.');
        return redirect(route('med-shorts'));
    }
    public function render()
    {
        $medShorts = MedShorts::orderBy('id', 'desc')->paginate(10);
        return view('livewire.med-shorts-handler', compact('medShorts'))->layout('layouts.admin');
    }
}
