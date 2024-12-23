<?php

namespace App\Http\Livewire;

use App\Models\Event;
use App\Models\Stall;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\WithFileUploads;

class StallHandler extends Component
{
    use WithFileUploads;

    public $stall_number, $hall_number, $special_feature, $size, $stall_type;
    public $event_id;
    public $stallId;
    public $status = 'available';

    protected $rules = [
        'stall_number' => 'required',
        'hall_number' => 'required',
        'status' => 'nullable',
        'special_feature' => 'nullable|string',
        'size' => 'required|numeric|min:1',
        'stall_type' => 'required',
        'event_id' => 'required',

    ];

    protected $messages  = [
        'stall_number.required' => 'Stall number field is required',
        'hall_number.required' => 'Hall number field is required',
        'size.required' => 'Size field is required',
        'size.numeric' => 'Size field must be numeric value',
        'stall_type.required' => 'Stall type field is required',
        'event_id.required' => 'Event field is required',
    ];


    public function resetFields()
    {
        $this->reset();
    }

    public function create()
    {
        $this->validate();

        $stallExists = Stall::where('event_id', $this->event_id)->where('stall_number', $this->stall_number)->first();
        if ($stallExists) {
            $this->addError('stall_number', 'Stall Number Already Exists For This Event');
            return;
        }
        try {
            $authId = getAuthData()->id;

            $stall = Stall::create(
                [
                    "created_by" => $authId,
                    "updated_by" => $authId,
                    "stall_number" => $this->stall_number,
                    'hall_number' => $this->hall_number,
                    'status' => $this->status,
                    'special_feature' => $this->special_feature,
                    'size' => $this->size,
                    'stall_type' => $this->stall_type,
                    'event_id' => $this->event_id,
                ]
            );

            if ($stall) {
                session()->flash('success', 'Stall created successfully.');
                return redirect(route('stall-summary'));
            }
            session()->flash('error', 'Stall was not created ');
            return;
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
            return;
        }
    }

    public function update()
    {
        $this->validate();
        $stallExists = Stall::where('event_id', $this->event_id)->where('stall_number', $this->stall_number)->where('id', '!=', $this->stallId)->first();
        if ($stallExists) {
            $this->addError('stall_number', 'Event Title Already Exists.');
            return;
        }

        try {
            $stall = Stall::find($this->stallId);
            if ($stall) {
                $stall->update([
                    "updated_by" => getAuthData()->id,
                    "stall_number" => $this->stall_number,
                    'hall_number' => $this->hall_number,
                    'status' => $this->status,
                    'special_feature' => $this->special_feature,
                    'size' => $this->size,
                    'stall_type' => $this->stall_type,
                    'event_id' => $this->event_id,
                ]);

                session()->flash("success", "Stall Details Successfully Updated");
                return redirect(route('stall-summary'));
            }
            session()->flash("error", "Unable to Update the Stall Details");
            return;
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
            return;
        }
    }

    public function mount()
    {
        $this->event_id = getCurrentEvent()->id;
        $this->stallId =  request()->stallId ?? null;
        if ($this->stallId) {
            $stallData = Stall::find($this->stallId);
            if (!empty($stallData)) {
                $this->stall_number = $stallData['stall_number'];
                $this->hall_number = $stallData['hall_number'];
                $this->status = $stallData['status'];
                $this->special_feature = $stallData['special_feature'];
                $this->size = $stallData['size'];
                $this->stall_type = $stallData['stall_type'];
                $this->event_id = $stallData['event_id'];
            }
            return;
        }
    }


    public function render()
    {
        $events = Event::select('title', 'event_description', 'id')
            // ->
            // whereNotNull('event_description')
            ->orderBy('id', 'desc')->get();
        return view('livewire.stall-handler', [
            'events' => $events,
        ])->layout('layouts.admin');
    }
}
