<?php

namespace App\Http\Livewire;

use App\Models\EventVisitor;
use App\Models\Visitor;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class LeadsList extends Component
{
    use WithPagination;

    public $agentNumber;
    public $customerNumber;
    public $previousEvents = [];
    public $currentEvent = null;

    protected $paginationTheme = 'bootstrap';
    #[Url]
    public $search = null;
    public $event_id = null;
    protected $queryString = ['search', 'event_id'];

    public function mount()
    {
        $this->currentEvent = getCurrentEvent();
        $this->previousEvents = getPreviousEvents();
        $this->resetFilters();
    }
    public function resetFilters()
    {
        $this->search = null;
        $this->event_id = null;
        $this->resetPage();
    }
    public function registerVisitorForEvent($visitorId)
    {
        $event_id = getCurrentEvent()->id;
        $visitor = Visitor::find($visitorId);
        $currentUser = getAuthData();
        if (!$visitor) {
            session()->flash('error', 'Visitor not found.');
            return false;
        }

        $eventVisitor = EventVisitor::where('visitor_id', $visitorId)
            ->where('event_id', $event_id)
            ->first();

        if ($eventVisitor) {
            session()->flash('error', 'Visitor is already registered for this event.');
            return false;
        }

        $newEventVisitor = new EventVisitor();
        $newEventVisitor->visitor_id = $visitorId;
        $newEventVisitor->event_id = $event_id;
        $newEventVisitor->registration_type = 'tele-calling';
        $newEventVisitor->is_lead_converted = 1;
        $newEventVisitor->who_converted = $currentUser->id;
        $newEventVisitor->save();

        session()->flash('success', 'Visitor registered successfully.');
        $this->resetPage();
        $this->render();
        return true;
    }

    public function render()
    {
        $currentEvent = getCurrentEvent();
        $currentEventId = $currentEvent->id;
        // dd($currentEventId);
        $query = Visitor::query();

        $query->whereDoesntHave('eventVisitors', function ($query) use ($currentEventId) {
            $query->where('event_id', $currentEventId);
        });

        if (!empty($this->event_id)) {
            $query->whereHas('eventVisitors', function ($query) {
                $query->where('event_id', $this->event_id);
            });
        }

        if (!empty($this->search)) {
            $query->whereHas('address', function ($q) {
                $q->where('city', 'like', '%' . $this->search . '%');
            });
        }

        $leads = $query->paginate(10);

        return view('livewire.leads-list', [
            'leads' => $leads,
        ])->layout('layouts.admin');
    }

    public function makeOutboundCall($agentNumber, $customerNumber)
    {
        $response = $this->makeOutboundCallToKnowlarity($agentNumber, $customerNumber);

        if ($response['status'] === 'error') {
            session()->flash('error', $response['message']);
        } else {
            session()->flash('success', $response['message']);
        }
    }

    private function makeOutboundCallToKnowlarity($agentNumber, $customerNumber)
    {
        $url = 'https://kpi.knowlarity.com/Basic/v1/account/call/makecall';
        $payload = [
            'k_number' => $agentNumber,
            'agent_number' => $agentNumber,
            'customer_number' => $customerNumber,
        ];

        try {
            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_CONNECTTIMEOUT => 0,
                CURLOPT_POST => 1,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_POSTFIELDS => json_encode($payload),
                CURLOPT_HTTPHEADER => [
                    'Authorization: 2c68f66e-1830-11e6-953b-067cf20e9301',
                    'x-api-key: APPLICATION_ACCESS_KEY',
                    'Content-Type: application/json',
                ],
            ]);

            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);

            if ($err) {
                return ['status' => 'error', 'message' => $err];
            } else {
                return ['status' => 'success', 'message' => 'Call initiated successfully', 'response' => $response];
            }
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }
}
