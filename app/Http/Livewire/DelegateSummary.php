<?php

namespace App\Http\Livewire;

use App\Exports\DelegatesExport;
use App\Models\Event;
use App\Models\EventSeminarParticipant;
use App\Models\EventVisitor;
use App\Models\Seminar;
use App\Models\Visitor;
use Illuminate\Http\Request;
use Livewire\Component;
use Livewire\WithPagination;

class DelegateSummary extends Component
{

    use WithPagination;
    protected $paginationTheme = 'bootstrap';
    public $eventId;
    #[Url]
    public $search = '';
    public $seminar = '';
    public $sortBy = 'id';
    public $sortDirection = 'desc';
    public $events = [];
    public $event_id;
    public $selectAll = false;
    public $showToggle = false;
    public $selectedDelegates = [];
    protected $queryString = ['search' => ['except' => '']];
    public $delegateId;
    public $notPaidSeminars = [];
    public $seminars = [];
    public $totalAmount = 0;
    public $paymentStatus = [];
    public $delegateSeminars = [];

    protected $listeners = [
        'message' => 'alertStatus',
    ];

    public function alertStatus($status = null, $message = null)
    {
        if ($status && $message) {
            session()->flash($status, $message);
        }
    }
    public $exporting = false;

    public function mount(Request $request)
    {
        $this->eventId = $request->eventId;
        $this->seminars = Seminar::where('event_id', $this->eventId)->pluck('title', 'id')->toArray();

    }
    public function sortColumn($field, $order = 'asc')
    {
        $this->sortDirection = $order;
        $this->sortBy = $field;
    }

    public function applySorting($query)
    {
        if ($this->sortBy && in_array($this->sortBy, ['id', 'name', 'mobile_number', 'email', 'organization'])) {
            $query->orderBy($this->sortBy, $this->sortDirection);
        }
    }
    public function updatedSelectAll($value)
    {
        if ($value) {
            $delegates = $this->getFiltereddelegates()->get();
            $this->selectedDelegates = $delegates->pluck('id');
        } else {
            $this->selectedDelegates = [];
        }
    }

    public function toggleEvents()
    {
        $this->showToggle = !$this->showToggle;
    }

    // public function getDelegate($id)
    // {
    //     $this->delegateId = $id;
    //     $this->dispatch('selectedDelegate', [$this->delegateId]);
    // }

    private function getFiltereddelegates()
    {
        $trimmedSearch = trim($this->search);

        $query = Visitor::whereHas('eventDelegates', function ($query) {
            $query->where('event_id', $this->eventId)
                ->when(!empty($this->seminar), function ($query) {
                    $query->where('seminar_id', $this->seminar);
                });
        })->when(!empty($trimmedSearch), function ($query) use ($trimmedSearch) {
            $query->where(function ($query) use ($trimmedSearch) {
                $query->where('name', 'like', '%' . $trimmedSearch . '%')
                    ->orWhere('mobile_number', 'like', '%' . $trimmedSearch . '%')
                    ->orWhere('email', 'like', '%' . $trimmedSearch . '%')
                    ->orWhere('organization', 'like', '%' . $trimmedSearch . '%');
            });
        });

        if ($this->sortBy) {
            $this->applySorting($query);
        }

        return $query;
    }

    public function getDelegateId($id)
    {
        $this->resetErrorBag();
        $this->delegateId = $id;
        $this->notPaidSeminars = EventSeminarParticipant::where('event_id', $this->eventId)
            ->where('visitor_id', $this->delegateId)->where('payment_status', 'pay_later')->get();
        $this->totalAmount = Seminar::whereIn('id', $this->notPaidSeminars->pluck('seminar_id'))->sum('amount');
        $this->delegateSeminars = EventSeminarParticipant::where('event_id', $this->eventId)->where('visitor_id', $this->delegateId)->get();
        foreach ($this->delegateSeminars as $delegate) {
            $this->paymentStatus[$delegate->id] = $delegate->payment_status;
        }
    }
    public function payForSeminar()
    {
        foreach ($this->notPaidSeminars as $seminar) {
            $seminar->update([
                'payment_status' => 'paid',
                'payment_type' => 'manual'
            ]);
        }
        redirect()->route('delegates.summary', ['eventId' => $this->eventId])->with('success', 'Seminars Successfully Paid');
    }
    public function exportToExcel()
    {
        $delegatesData = $this->getFiltereddelegates()->get();

        if (count($delegatesData) > 0) {
            $export = new DelegatesExport($this->eventId, $delegatesData);
            return $export->download('delegates.xlsx');
        }
    }

    public function render()
    {
        $delegatesQuery = $this->getFiltereddelegates();
        $delegates = $delegatesQuery->paginate(10);

        // dd($delegates);

        $this->events = Event::where('start_date', '>=', now()->format('Y-m-d'))
            ->orWhere('end_date', '>', now()->format('Y-m-d'))
            ->pluck('title', 'id');

        return view('livewire.delegate-summary', [
            'delegates' => $delegates,
        ])->layout('layouts.admin');
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function moveDelegatesToAnotherEvent()
    {
        $isCreated = null;
        if (count($this->selectedDelegates) > 0) {
            $visitorRecords = Visitor::whereIn('id', $this->selectedDelegates)->get();
            foreach ($visitorRecords as $visitorRecord) {
                $isExists = $visitorRecord->eventVisitors()->where('event_id', $this->event_id)->exists();
                if (!$isExists) {
                    $isCreated = $visitorRecord->eventVisitors()->create([
                        'event_id' => $this->event_id,
                        // 'is_delegates' => 1,
                    ]);
                } else {
                    session()->flash('info', "$visitorRecord->name Exists in this Event.");
                }
            }
            if ($isCreated) {
                redirect()->route('delegates.summary');
                session()->flash('success', 'Delegats Successfully Moved to Another Event.');
            }
        } else {
            session()->flash('info', 'Please select atleast one delegates.');
        }
    }

    public function updatePaymentStatus()
    {
        $this->validate([
            'paymentStatus.*' => 'required',
        ], [
            'paymentStatus.*.required' => 'Please select payment status',
        ]);

        foreach ($this->delegateSeminars as $eventDelegate) {

            $selectedStatus = $this->paymentStatus[$eventDelegate->id] ?? null;

            if ($selectedStatus) {
                $eventDelegate->update([
                    'payment_status' => $selectedStatus,
                ]);
            }
        }

        redirect()->route('delegates.summary', ['eventId' => $this->eventId]);
    }

}
