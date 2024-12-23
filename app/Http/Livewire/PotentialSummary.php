<?php

namespace App\Http\Livewire;

use Exception;
use App\Models\User;
use App\Models\Event;
use Livewire\Component;
use App\Models\Potential;
use Livewire\Attributes\Url;
use Livewire\WithPagination;
use Spatie\Activitylog\Models\Activity;

class PotentialSummary extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    #[Url(as: 'pp')]
    public $perPage = 10;
    public $events, $salesPersons;
    public $potentialId, $potential;
    public $event_id, $lead_type, $lead_category, $assign_id, $re_assign_id;
    public $potential_Ids;
    public $potentialList;

    protected $rules = [
        'event_id' => 'required',
        'lead_type' => 'required',
        'lead_category' => 'required',
        're_assign_id' => 'required',
    ];
    protected $messages = [
        'event_id.required' => 'This Field is required',
        'lead_type.required' => 'This Field is required',
        'lead_category.required' => 'This Field is required',
        're_assign_id.required' => 'This Field is required',
    ];

    public function mount()
    {
        $getPreviousEventId = getPreviousEvents()->pluck('id')->toArray();
        $this->events = Event::whereNotIn('id', $getPreviousEventId)
            ->select('id', 'title', 'event_description')
            ->orderBy('start_date', 'desc')
            ->get();
        $this->salesPersons = User::select('id', 'name')
            ->whereIN('type', ['sales_person', 'user'])->get();
        $this->potentialList = Potential::get();
    }

    public function getPotentialId($id)
    {
        $this->potentialId = $id;
        $this->potential = Potential::find($id);
        if ($this->potential) {
            $this->assign_id = $this->potential->assignedPerson->name;
            $this->lead_category =  $this->potential->_meta['lead_category'];
            $this->lead_type =  lcfirst($this->potential?->lead?->type);
            $this->potentialList =   $this->potentialList->where('sales_person_id', $this->potential->sales_person_id);
        }
    }

    public function render()
    {
        if (empty($this->event_id)) {
            $this->event_id = $this->events->first()->id;
        }
        $potentials  = Potential::whereDoesntHave('followups')
            ->orWhereHas('followups', function ($query) {
                $query->where('status', '!=', 'closed-won');
            })
            ->orderBy('id', 'desc')
            ->paginate(10, pageName: 'potential-list');
            $potentialActivities = Activity::where('log_name', 'potential_log')->orderBy('id', 'desc')->paginate(10, pageName: 'activity');
        return view('livewire.potential-summary', [
            'potentials' => $potentials,
            'potentialActivities' => $potentialActivities,
        ])->layout("layouts.admin");
    }

    public function reAssignSalesPerson()
    {
        $this->validate();
        $isSamePerson = ($this->potential->sales_person_id == $this->re_assign_id);
        if ($isSamePerson) {
            return $this->addError('re_assign_id', 'Change Salesperson to update.');
        }

        try {
            $potentials = Potential::whereIn('id', $this->potential_Ids)->get();
            if ($potentials->isNotEmpty()) {
                $potentials->each(function ($potential) {
                    $potential->update(['sales_person_id' => $this->re_assign_id]);
                });

                session()->flash('success', 'Potential(s) updated successfully.');
                return redirect(route('potential-summary'));
            } else {
                session()->flash('error', 'No potentials found to update.');
                return;
            }
        } catch (\Exception $e) {
            session()->flash('error', 'An error occurred: ' . $e->getMessage());
            return redirect(route('potential-summary'));
        }
    }


    public function changePageValue($perPageValue)
    {
        $this->perPage = $perPageValue;
        $this->resetPage();
    }
}
