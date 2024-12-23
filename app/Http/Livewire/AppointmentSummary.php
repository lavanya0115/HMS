<?php

namespace App\Http\Livewire;

use App\Exports\AppointmentExport;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Activitylog\Models\Activity;

class AppointmentSummary extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    #[Url( as :'pp')]
    public $perPage = 10;

    public $eventId, $appointmentStatus, $appointmentId;

    #[Url( as :'s')]
    public $search;
    #[Url( as :'df')]
    public $dateFilter;

    public $orderBy = 'desc';

    public $orderByDate = 'asc';

    public $orderByName = 'id';

    public $toggleContent = false;

    public $sortedFeedbacks = [];

    public $isSelectAll = false, $fetchAllRecords = false;

    public $selectedRecordIds = [];

    public $selectRecordsCount;

    public $totalCount;
    #[Url]
    public $source = 'all';
    public $sourceOptions;

    protected $listeners = [
        'deleteAppointment' => 'deleteAppointmentById',
    ];

    public function deleteAppointmentById($appointmentId)
    {
        $appointment = Appointment::find($appointmentId);
        $authUser = getAuthData();
        if ($appointment) {
            $appointment->update([
                'deleted_by' => $authUser->id,
                'deleted_type' => get_class($authUser),
            ]);
            $isUpdated = $appointment->wasChanged('deleted_by');
            $isDeleted = $appointment->delete();

            if ($isUpdated && $isDeleted) {
                session()->flash("success", "Appointment deleted successfully");
                return redirect()->back();
            } else {
                session()->flash("error", "Unable to delete Appointment");
                return;
            }
        }
    }

    public function mount(Request $request)
    {
        $this->sourceOptions = Appointment::distinct()->pluck('source');
        $this->source = 'all';

        $this->eventId = $request->eventId;
        $this->appointmentStatus = $request->appointmentStatus;
    }
    public function resetField()
    {
        $this->reset([
            'search',
        ]);
    }
    public function resetDate()
    {
        $this->reset([
            'dateFilter',
        ]);
    }

    public function toggleBtn()
    {
        $this->toggleContent = !$this->toggleContent;
    }
    public function changePageValue($perPageValue)
    {
        $this->perPage = $perPageValue;
        $this->resetPage();
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
    public function exhibitorAppointmentId($appointmentId)
    {
        $this->appointmentId = $appointmentId;
        $this->getFeedback();
    }

    public function getFeedback()
    {
        $viewFeedback = Appointment::find($this->appointmentId);

        $visitorFeedback = $viewFeedback->_meta['visitor_feedback'] ?? null;
        $exhibitorFeedback = $viewFeedback->_meta['exhibitor_feedback'] ?? null;

        if (isset($viewFeedback->visitor->_meta['logo']) && !empty($viewFeedback->visitor->_meta['logo'])) {
            $visitorLogo = $viewFeedback->visitor->_meta['logo'];
        }

        $feedbacks = collect([
            [
                'name' => $viewFeedback->visitor->name ?? '',
                'logo' => $visitorLogo ?? '',
                'message' => $visitorFeedback['message'] ?? '',
                'timestamp' => $visitorFeedback['timestamp'] ?? null,
                'type' => 'visitor',
            ],
            [
                'name' => $viewFeedback->exhibitor->name ?? '',
                'logo' => $viewFeedback->exhibitor->logo ?? '',
                'message' => $exhibitorFeedback['message'] ?? '',
                'timestamp' => $exhibitorFeedback['timestamp'] ?? null,
                'type' => 'exhibitor',
            ],
        ]);
        // dd($feedbacks);

        $this->sortedFeedbacks = $feedbacks->sort(function ($a, $b) {
            $timestampA = $a['timestamp'] ?? null;
            $timestampB = $b['timestamp'] ?? null;

            if ($timestampA === null) {
                return 1;
            }

            if ($timestampB === null) {
                return -1;
            }

            return $timestampA <=> $timestampB;
        });

        $this->sortedFeedbacks->values()->all();
    }
    public function exportData()
    {

        // if ($this->fetchAllRecords) {
        $allAppointments = $this->getAppointmentRecords()->get();
        $this->selectedRecordIds = $allAppointments->pluck('id');
        $this->selectRecordsCount = count($this->selectedRecordIds);
        // }
        if ($this->selectRecordsCount > 0) {
            return (new AppointmentExport($this->selectedRecordIds, $this->orderByName, $this->orderBy))->download('appointments.xlsx');
        } else {
            return session()->flash('info', 'No Records To Export');
        }
    }
    // public function selectedRows($id)
    // {
    //     $this->selectedRecordIds[] = $id;
    //     $this->selectRecordsCount = count(array_unique($this->selectedRecordIds));
    // }
    public function getAllRecords()
    {
        $this->fetchAllRecords = true;
    }
    public function clearSelection()
    {
        return redirect()->route('appointment.summary', ['p' => $this->paginators['p']]);
        // $this->isSelectAll = false;
        // $this->fetchAllRecords = false;
        // $this->selectedRecordIds = [];
        // $this->selectRecordsCount = count($this->selectedRecordIds);
        // $this->dispatch('clearCheckboxSelection');
    }
    private function getAppointmentRecords()
    {

        $appointments = Appointment::when(isset($this->eventId), function ($query) {
            $query->where('event_id', $this->eventId)
                ->when(isSalesPerson(), function ($query) {
                    $query->whereIn('exhibitor_id', mappedExhibitors($this->eventId));
                });
        })
            ->when($this->source != 'all', function ($q) {
                return $q->where('source', $this->source);
            })
            ->when($this->search !== null, function ($query) {
                $query->where(function ($subQuery) {
                    $subQuery->whereHas('visitor', function ($query) {
                        $query->where('name', 'like', '%' . $this->search . '%');
                    })
                        ->orWhereHas('exhibitor', function ($query) {
                            $query->where('name', 'like', '%' . $this->search . '%');
                        });
                });
            })
            ->when(($this->dateFilter !== null), function ($query) {
                $query->whereDate('appointments.created_at', $this->dateFilter);
            })
            ->when(isset($this->appointmentStatus), function ($status) {
                $status->where('status', $this->appointmentStatus);
            })
            ->when($this->orderByName, function ($sort) {
                if ($this->orderByName == 'visitor') {
                    $sort->join('visitors', 'appointments.visitor_id', '=', 'visitors.id')
                        ->select('appointments.*')->orderBy('visitors.name', $this->orderBy);
                } else if ($this->orderByName == 'designation') {
                    $sort->join('visitors', 'appointments.visitor_id', '=', 'visitors.id')
                        ->select('appointments.*')->orderBy('visitors.designation', $this->orderBy);
                } else if ($this->orderByName == 'exhibitor') {
                    $sort->join('exhibitors', 'appointments.exhibitor_id', '=', 'exhibitors.id')
                        ->select('appointments.*')->orderBy('exhibitors.name', $this->orderBy);
                } else {
                    $sort->orderBy($this->orderByName, $this->orderBy);
                }
            });

        return $appointments;
    }
    public function render()
    {
        $appointments = $this->getAppointmentRecords()->paginate(
            $this->perPage,
            pageName: 'p'
        );
        // if ($this->isSelectAll) {
        //     $this->selectedRecordIds =  $appointments->pluck('id')->toArray();
        //     $this->selectRecordsCount = count($this->selectedRecordIds);
        //     $this->totalCount = $appointments->total();
        // }

        // if ($this->fetchAllRecords) {
        //     $allAppointments = $this->getAppointmentRecords()->get();
        //     $this->selectedRecordIds  = $allAppointments->pluck('id');
        //     $this->selectRecordsCount = count($this->selectedRecordIds);
        // }

        if ($this->search !== '' || $this->dateFilter !== '') {
            $this->resetPage();
        }

        $activities = Activity::select('activity_log.*')->where('log_name', 'appointment_log')
            ->join('appointments', 'activity_log.subject_id', '=', 'appointments.id')
            ->where('appointments.event_id', $this->eventId)
            ->orderBy('activity_log.id', 'desc')->paginate(10, pageName: 'appointment-activity');

        return view(
            'livewire.appointment-summary',
            [
                'appointments' => $appointments,
                'selectRecordsCount' => $this->selectRecordsCount,
                'totalCount' => $this->totalCount,
                'feedbacks' => $this->sortedFeedbacks,
                'sourceOptions' => $this->sourceOptions,
                'activities' => $activities,
            ]
        )->layout('layouts.admin');
    }
    public function updated($propertyName)
    {
        if ($propertyName === 'search' || $propertyName === 'dateFilter') {
            $this->resetPage();
        }
    }
}
