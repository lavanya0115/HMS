<?php

namespace App\Http\Livewire;

use App\Models\Lead;
use App\Models\User;
use App\Models\Event;
use App\Models\Stall;
use Livewire\Component;
use App\Models\Potential;
use App\Models\EventVisitor;
use Livewire\Attributes\Url;

class PotentialHandler extends Component
{
    public $event_id;
    // #[Url(as: 'lId')]
    public $lead_id;
    // #[Url(as: 'aId')]
    public $sales_person_id;
    public $stall, $stall_id, $stall_type, $stall_amount, $stall_status, $stall_agent_id, $company_name;
    public $potentialId;
    public $lead_category, $lead_unique_id, $lead_contact_number, $lead_address, $contact_mode, $address;
    public $events, $salesPersons, $leads;
    public $stallStatus, $stalls;
    public $sq_mtr, $rate;


    protected $rules = [
        'event_id' => 'required',
        'lead_id' => 'required',
        'sales_person_id' => 'required',
        'stall_agent_id' => 'required',
        'stall_id' => 'required',
        'stall_status' => 'required',
        'stall_type' => 'required',
        'stall_amount' => 'required',
        'rate' => 'required',
    ];

    protected $messages = [
        'event_id.required' => 'Select The Event.',
        'lead_id.required' => 'Select The Lead.',
        'sales_person_id.required' => 'Select the Sales Person.',
        'stall_id.required' => 'Stall is Required.',
        'stall_agent_id.required' => 'Enter Company Name.',
        'stall_status.required' => 'Stall Status is Required.',
        'stall_type.required' => 'Stall type is Required.',
        'stall_amount.required' => 'Amount Field is Required.',
        'rate.required' => 'Enter the Rate.',
    ];

    public function resetFields()
    {
        $this->reset();
    }

    public function mount()
    {
        $this->potentialId = request()->potentialId ?? null;
        if (!empty($this->potentialId)) {
            try {
                $potential = Potential::find($this->potentialId);
                if (empty($potential)) {
                    session()->flash('error', 'Potential Not Found ');
                }
                $this->event_id = $potential->event_id;
                $this->lead_id = $potential->lead_id;
                $this->sales_person_id = $potential->sales_person_id;
                $this->lead_unique_id = $potential->_meta['lead_id'] ?? null;
                $this->lead_category = $potential->_meta['lead_category'] ?? null;
                $this->contact_mode = $potential->_meta['contact_mode'] ?? null;
                $this->address = $potential->_meta['address'] ?? null;
                $this->sq_mtr = $potential->_meta['stall_sqr_mtr'] ?? null;
                $this->rate = $potential->_meta['stall_rate'] ?? null;
                $this->stall_id = $potential->stall_id;
                $this->stall_type = $potential->stall_type;
                $this->stall_amount = $potential->amount;
                $this->stall_status = $potential->stall_status;
                $this->stall_agent_id = $potential->agent_id;
                $this->company_name = $potential->lead->name ?? null;
                $this->stall = $potential->stall;
            } catch (\Exception $e) {
                session()->flash('error', 'Failed to load the potential data: ' . $e->getMessage());
                return redirect()->back();
            }
        }
        $getPreviousEventId = getPreviousEvents()->pluck('id')->toArray();
        $this->events = Event::whereNotIn('id', $getPreviousEventId)
            ->select('id', 'title', 'event_description')
            ->orderBy('start_date', 'desc')
            ->get();

        $this->salesPersons = User::select('id', 'name')
            ->whereHas('roles', function ($roleQuery) {
                $roleQuery->where('name', 'Sales Person');
            })->where('is_active', 1)
            ->get();

        $this->leads = Lead::orderBy('id', 'desc')
            ->get();

        $this->stallStatus  = getStallStatus();
    }

    public function updatedRate($value)
    {
        if (!empty($value)) {
            $this->stall_amount = $value * $this->sq_mtr;
        }
    }
    public function updatedStallId($value)
    {
        if (!empty($value)) {
            $this->stall = Stall::find($value);
            $this->stall_type = $this->stall?->stall_type ?? '';
            $this->sq_mtr = $this->stall?->size ?? 0;
        }
    }

    public function filLeadData()
    {
        if (!empty($this->lead_id)) {
            $lead = Lead::find($this->lead_id);
            $this->lead_unique_id = $lead?->lead_no ?? '';
            $this->lead_category = $lead?->category ?? '';
            $this->contact_mode = $lead?->contactPerson?->contact_number ?? '';
            $this->address = $lead?->branchPrimary?->address?->address ?? '';
            $this->stall_agent_id = $lead->id ?? '';
            $this->company_name =  $lead?->name ?? '';
        }
    }

    public function render()
    {
        if (empty($this->event_id)) {
            $this->event_id = $this->events?->first()?->id ?? null;
        }
        $this->stalls = Stall::where('event_id', $this->event_id)
            ->where(function ($query) {
                $query
                    ->where('status', '!=', 'booked')
                    ->orWhereNull('status');
            })
            ->whereNull('potential_id')
            ->orderBy('id', 'desc')
            ->get();

        $stallDetails = Potential::where('lead_id', $this->lead_id)->paginate(25);

        return view('livewire.potential-handler', [
            'stallDetails' => $stallDetails,
        ])->layout('layouts.admin');
    }

    public function create()
    {
        $this->validate();

        try {
            $authId = getAuthData()->id;

            if ($this->stall->staus !== $this->stall_status) {
                $this->stall->update([
                    'status' => $this->stall_status,
                ]);
            }

            if ($this->stall->stall_type !== $this->stall_type) {
                $this->stall->update([
                    'stall_type' => $this->stall_type,
                ]);
            }

            $potential = Potential::create(
                [
                    "created_by" => $authId,
                    "updated_by" => $authId,
                    'event_id' => $this->event_id,
                    'lead_id' => $this->lead_id,
                    'sales_person_id' => $this->sales_person_id,
                    "agent_id" => $this->stall_agent_id,
                    "status" => "warm",
                    'amount' => $this->stall_amount,
                    'stall_id' => $this->stall_id,
                    'stall_type' => $this->stall_type,
                    'stall_status' => $this->stall_status,
                    '_meta' => [
                        'lead_id' => $this->lead_unique_id,
                        'lead_category' => $this->lead_category,
                        'contact_mode' => $this->contact_mode,
                        'address' => $this->address,
                        'stall_sqr_mtr' => $this->sq_mtr,
                        'stall_rate' => $this->rate,
                    ],
                ]
            );

            if ($potential) {

                $this->stall->update([
                    'potential_id' => $potential->id,
                ]);

                session()->flash('success', 'Potential created successfully.');
                return redirect(route('potential-create', $this->lead_category === 'agent' ? ['lId' => $this->lead_id, 'aId' => $this->sales_person_id] : []));
            }
            session()->flash('error', 'Potential was not created ');
            return;
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
            return;
        }
    }
    public function update()
    {
        $this->validate();

        try {
            $authId = getAuthData()->id;

            $potential = Potential::findOrFail($this->potentialId);

            if ($this->stall->status !== $this->stall_status) {
                $this->stall->update([
                    'status' => $this->stall_status,
                ]);
            }

            if ($this->stall->stall_type !== $this->stall_type) {
                $this->stall->update([
                    'stall_type' => $this->stall_type,
                ]);
            }

            $potential->update([
                "updated_by" => $authId,
                'event_id' => $this->event_id,
                'lead_id' => $this->lead_id,
                'sales_person_id' => $this->sales_person_id,
                "agent_id" => $this->stall_agent_id,
                'amount' => $this->stall_amount,
                'stall_id' => $this->stall_id,
                'stall_type' => $this->stall_type,
                'stall_status' => $this->stall_status,
                '_meta' => [
                    'lead_id' => $this->lead_unique_id,
                    'lead_category' => $this->lead_category,
                    'contact_mode' => $this->contact_mode,
                    'address' => $this->address,
                    'stall_sqr_mtr' => $this->sq_mtr,
                    'stall_rate' => $this->rate,
                ],
            ]);
            $is_updated = $potential->wasChanged('event_id', 'updated_by', 'lead_id', 'sales_person_id', "agent_id", 'amount', 'stall_id', 'stall_type', 'stall_status', '_meta');
            if ($is_updated) {
                session()->flash('success', 'Potential updated successfully.');
                return redirect(route('potential-create', $this->lead_category === 'agent' ? ['lId' => $this->lead_id, 'aId' => $this->sales_person_id] : []));
            }
            session()->flash('info', 'Made some changes to updated details.');
            return redirect(route('potential-create', $this->lead_category === 'agent' ? ['lId' => $this->lead_id, 'aId' => $this->sales_person_id] : []));
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
            return;
        }
    }
}
