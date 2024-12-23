<?php

namespace App\Http\Livewire;

use App\Models\Event;
use App\Models\Seminar;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Http\Request;

class SeminarHandler extends Component
{
    use WithFileUploads;
    public $seminar = [
        'title' => '',
        'description' => '',
        'date' => '',
        'amount',
        'start_time' => '',
        'end_time' => '',
        'location' => '',
        'image' => '',
        'is_active' => false,
    ];
    public $eventId;
    public $photo;
    public $seminarId;
    public $start;
    public $end;

    protected $rules = [
        'seminar.title' => 'required',
        'seminar.date' => 'required|date|after_or_equal:today',
        'seminar.description' => 'required|string',
        'seminar.start_time' => 'required',
        'seminar.end_time' => 'required|after:seminar.start_time',
        'seminar.amount' => 'required',
        'seminar.location' => 'required',
    ];

    protected $messages = [
        'seminar.title.required' => 'Title is required',
        'seminar.date.required' => 'Date is required',
        'seminar.date.after_or_equal' => 'Date should be greater than today',
        'seminar.description.required' => 'Description is required',
        'seminar.description.string' => 'Description should be string',
        'seminar.start_time.required' => 'Start time is required',
        'seminar.end_time.required' => 'End time is required',
        'seminar.end_time.after' => 'End time should be greater than start time',
        'seminar.amount' => 'Amount is required',
        'seminar.location' => 'Location is required',
    ];

    public function mount(Request $request)
    {
        $this->eventId = $request->eventId;
        $this->seminarId = $request->seminarId;
        $selectedEvent = Event::where('id', $this->eventId)->select('start_date', 'end_date')->first();

        if ($selectedEvent) {
            $start = Carbon::parse($selectedEvent->start_date)->format('d-m-Y');
            $end = Carbon::parse($selectedEvent->end_date)->format('d-m-Y');

            $this->start = $start;
            $this->end = $end;
        }

        if ($this->seminarId) {
            $seminar = Seminar::find($this->seminarId);
            if ($seminar) {
                $this->seminar['title'] = $seminar->title;
                $this->seminar['description'] = $seminar->description;
                $this->seminar['date'] = $seminar->date;
                $this->seminar['start_time'] = $seminar->start_time;
                $this->seminar['end_time'] = $seminar->end_time;
                $this->seminar['location'] = $seminar->_meta['location'] ?? '';
                $this->seminar['amount'] = $seminar->amount;
                $this->seminar['is_active'] = $seminar->is_active;
                $this->seminar['image'] = $seminar->_meta['thumbnail'] ?? '';
            } else {
                session()->flash('warning', 'Seminar not found');
                return redirect()->back();
            }
        }
    }
    public function create()
    {
        $this->authorize('Create Seminar');
        $this->validate();

        DB::beginTransaction();

        try {
            $authId = auth()->id();
            $imagePath = '';

            if ($this->photo) {
                $imageFolderPath = 'thumbnail/' . date('Y/m');
                $imageName = $this->photo->getClientOriginalName();
                $imagePath = $this->photo->storeAs($imageFolderPath, $imageName, 'public');
            }
            $titleExits = Seminar::where('title', $this->seminar['title'])
                ->where('event_id', $this->eventId)
                ->first();

            if ($titleExits) {
                $this->addError('seminar.title', 'Seminar title already exists.');
                return;
            }

            $seminar = Seminar::create([
                "created_by" => $authId,
                "updated_by" => $authId,
                "title" => $this->seminar['title'],
                "event_id" => $this->eventId,
                "date" => Carbon::createFromFormat('Y-m-d', $this->seminar['date'])->toDateString(),
                "start_time" => $this->seminar['start_time'],
                "end_time" => $this->seminar['end_time'],
                "amount" => $this->seminar['amount'],
                "description" => $this->seminar['description'],
                "is_active" => $this->seminar['is_active'] == true,
                "_meta" => [
                    'thumbnail' => $imagePath,
                    'location' => $this->seminar['location'],
                ],
            ]);

            DB::commit();

            if ($seminar) {
                session()->flash('success', 'Seminar created successfully.');
                return redirect(route('seminars', ['eventId' => $this->eventId]));
            }

            session()->flash('error', 'Seminar was not created.');
            return;
        } catch (\Exception $e) {
            // dd($e->getMessage());
            DB::rollback();
            session()->flash('error', $e->getMessage());
            return;
        }
    }

    public function resetFields()
    {
        $this->reset();
    }

    public function update()
    {
        // dd($this->seminar);
        $this->validate();
        $titleExits = Seminar::where('title', $this->seminar['title'])->where('event_id', $this->eventId)
            ->where('id', '<>', $this->seminarId)
            ->first();

        if ($titleExits) {
            $this->addError('seminar.title', 'Event title already exists.');
            return;
        }

        DB::beginTransaction();
        try {
            $authId = auth()->id();
            $imagePath = '';

            $seminar = Seminar::find($this->seminarId);
            $imagePath = $seminar['_meta']['thumbnail'] ?? '';

            if ($this->photo) {
                $imageFolderPath = 'seminar/' . date('Y/m');
                $imageName = $this->photo->getClientOriginalName();
                $imagePath = $this->photo->storeAs($imageFolderPath, $imageName, 'public');
            }
            // dd($this->seminar['is_active']);
            $seminar->update([
                "updated_by" => $authId,
                "title" => $this->seminar['title'],
                "event_id" => $this->eventId,
                "date" => Carbon::createFromFormat('Y-m-d', $this->seminar['date'])->toDateString(),
                "start_time" => $this->seminar['start_time'],
                "end_time" => $this->seminar['end_time'],
                "amount" => $this->seminar['amount'],
                "description" => $this->seminar['description'],
                "is_active" => $this->seminar['is_active'] == true,
                "_meta" => [
                    'thumbnail' => $imagePath,
                    'location' => $this->seminar['location'],
                ],
            ]);

            DB::commit();

            session()->flash('success', 'Seminar updated successfully.');
            return redirect(route('seminars', ['eventId' => $this->eventId]));
        } catch (\Exception $e) {
            //  dd($e->getMessage());
            DB::rollback();
            session()->flash('error', $e->getMessage());
            return;
        }
    }

    public function render()
    {
        return view('livewire.seminar-handler')->layout('layouts.admin');
    }
}
