<?php

namespace App\Http\Livewire;

use App\Models\User;
use Livewire\Component;
use App\Models\Exhibitor;
use Illuminate\Http\Request;
use Livewire\Attributes\Url;
use App\Models\EventExhibitor;
use Spatie\Activitylog\Models\Activity;

class MappingToExhibitor extends Component
{
    public $perPage = 10;

    public $eventId;

    public $userName, $userId;

    public $toggleContent = false;

    public $exhibitors = [];

    public $exhibitorId = [];

    #[Url(as: 'search')]
    public $search;

    public $oldExhibitor = [];

    public $orderBy = 'asc', $orderByName = 'name';

    public $authUser;

    public function resetFilter()
    {
        $this->search = null;
    }

    public function orderByAsc($columnName)
    {
        $this->orderBy = 'asc';
        $this->orderByName = $columnName;
    }

    public function orderByDesc($columnName)
    {
        $this->orderBy = 'desc';
        $this->orderByName = $columnName;
    }

    public function mount(Request $request)
    {
        $this->eventId = $request->eventId;
        $this->exhibitors = Exhibitor::where('deleted_by', null)->select('id', 'name')->get();
        $this->authUser = getAuthData();
    }

    public function render()
    {
        $users = User::when(isset($this->search), function ($query) {
            $query->where(function ($subQuery) {
                $subQuery->whereHas('exhibitors', function ($getName) {
                    $getName->where('name', 'like', '%' . trim($this->search) . '%');
                });
            })->orWhere('name', 'like', '%' . trim($this->search) . '%');
        })
            ->where('is_active', 1)
            ->whereHas('roles', function ($roleQuery) {
                $roleQuery->where('name', 'Sales Person');
            })
            ->orderBy($this->orderByName, $this->orderBy)

            ->paginate($this->perPage, pageName: 'p');
        return view('livewire.mapping-to-exhibitor', compact('users'))->layout('layouts.admin');
    }

    public function getUserId($id)
    {
        $this->userId = $id;
        $user = User::find($this->userId);
        $this->userName = $user->name;
        $this->exhibitorId = Exhibitor::where('sales_person_id', $this->userId)->pluck('id')->flatten()->toArray();
        $this->oldExhibitor = $this->exhibitorId;
        $this->dispatch('setValueInTomSelect', id: $this->exhibitorId);
    }

    public function mapExhibitor()
    {

        $unlinkExhibitorIds = array_diff($this->oldExhibitor, $this->exhibitorId);
        $newExhibitorIds = array_diff($this->exhibitorId, $this->oldExhibitor);


        if (!empty($unlinkExhibitorIds) && count($unlinkExhibitorIds) > 0) {
            $unlinkExhibitors = Exhibitor::whereIn('id', $unlinkExhibitorIds)
                // ->where('event_id', $this->eventId)
                ->update([
                    'sales_person_id' => null,
                ]);

            $properties = [
                'old' => [
                    'unMappedExhibitorsIds' => $unlinkExhibitorIds,
                ],
            ];
            $causerId = $this->authUser->id;
            $causerName = $this->authUser->name;

            foreach ($unlinkExhibitorIds as $id) {
                $description = "Exhibitor ($id) unmapped from the $this->userName (sales person) --by $causerName";
                $activity_created = Activity::create([
                    'log_name' => 'sales_person_unlink_log',
                    'description' => $description,
                    'subject_type' => 'App\Models\Exhibitor',
                    'event' => 'updated',
                    'subject_id' => $id,
                    'causer_type' => 'App\Models\User',
                    'causer_id' => $causerId,
                    'properties' => $properties,
                ]);
            }
            if ($unlinkExhibitors > 0 && !empty($activity_created)) {
                $this->closeModal();
                session()->flash('success', 'Exhibitors successfully unmapped for ' . $this->userName);
                // return;
            }
        }

        if (!empty($newExhibitorIds) && count($newExhibitorIds) > 0) {
            $addExhibitors = Exhibitor::where('sales_person_id', null)
                ->whereIn('id', $newExhibitorIds)
                // ->where('event_id', $this->eventId)
                ->get();

            if (!empty($addExhibitors) && count($addExhibitors) > 0) {
                foreach ($addExhibitors as $addExhibitor) {
                    $addExhibitor->sales_person_id = $this->userId;
                    $addExhibitor->save();
                }
                $isUpdated = $addExhibitor->wasChanged('sales_person_id');
                if ($isUpdated) {
                    $properties = [
                        'attributes' => [
                            'mappedExhibitorsIds' => $newExhibitorIds,
                        ],
                    ];
                    $causerId = auth()->user()->id;
                    $causerName = auth()->user()->name;

                    foreach ($newExhibitorIds as $id) {
                        $description = "Exhibitor ($id) mapped to the $this->userName (sales person) --by $causerName";
                        $activity_created = Activity::create([
                            'log_name' => 'sales_person_link_log',
                            'description' => $description,
                            'subject_type' => 'App\Models\Exhibitor',
                            'event' => 'created',
                            'subject_id' => $id,
                            'causer_type' => 'App\Models\User',
                            'causer_id' => $causerId,
                            'properties' => $properties,
                        ]);
                    }
                    $this->closeModal();
                    session()->flash('success', 'Exhibitors successfully mapped for ' . $this->userName);
                    return;
                }
                $this->closeModal();
                session()->flash('info', 'Do some Modification ');
                return;
            }
            $this->closeModal();
            session()->flash('info', 'This Exhibitor already mapped to another sales person ');
            return;
        }
    }

    public function closeModal()
    {
        $this->reset([
            'exhibitorId',
        ]);
        $this->dispatch('closeModal');
    }

    public function changePageValue($perPageValue)
    {
        $this->perPage = $perPageValue;
        // $this->resetPage(pageName: 'p');
    }

    public function toggleBtn()
    {
        $this->toggleContent = !$this->toggleContent;
    }
}
