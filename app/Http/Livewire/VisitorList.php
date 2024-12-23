<?php

namespace App\Http\Livewire;

use App\Models\Visitor;
use Livewire\Component;
use App\Models\EventVisitor;
use Livewire\WithPagination;

class VisitorList extends Component
{
    use WithPagination;
    public $agentNumber;
    public $customerNumber;

    protected $paginationTheme = 'bootstrap';

    public function registerVisitorForEvent($visitorId)
    {
        $event_id = getCurrentEvent()->id;
        $visitor = Visitor::find($visitorId);

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
        $newEventVisitor->save();

        session()->flash('success', 'Visitor registered successfully.');
        return true;
    }


    public function render()
    {
        $currentUser = getAuthData();
        // dd($currentUser);
        $visitors = Visitor::where('telecalling_user_id', $currentUser->id)
        ->paginate(10);

        return view('livewire.visitor-list', [
            'visitors' => $visitors,
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
