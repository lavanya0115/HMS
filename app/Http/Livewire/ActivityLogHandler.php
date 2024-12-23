<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Livewire\Attributes\Url;
use Livewire\WithPagination;
use App\Models\UserLoginActivity;
use Spatie\Activitylog\Models\Activity;

class ActivityLogHandler extends Component
{
    use WithPagination;
    public $toggleFilter = true;
    #[Url(keep: false)]
    public $log = '';
    #[Url(keep: false)]
    public $action = '';
    #[Url(keep: false)]
    public $role = '';

    public $logPerPage = 10;

    public function toggleBtn()
    {
        $this->toggleFilter  = !$this->toggleFilter;
    }
    public function resetAttributes()
    {
        $this->log = '';
        $this->action = '';
        $this->role = '';
    }

    private function roleMapping($role)
    {
        $roles = [
            'admin' => 'App\Models\User',
            'visitor' => 'App\Models\Visitor',
            'exhibitor' => 'App\Models\Exhibitor',
        ];
        $role = ($roles[$role]) ? $roles[$role] : '';
        return $role;
    }

    public function changeLoginPageValue($perPageValue)
    {
        $this->logPerPage = $perPageValue;
        $this->resetPage();
    }


    public function render()
    {

        $logs = Activity::when(!empty($this->log), function ($query) {
            $query->where('log_name', $this->log);
        })->when(!empty($this->action), function ($query) {
            $query->where('event', $this->action);
        })->orderBy('id', 'desc')->paginate(10, pageName: 'activity-logs');

        $userLogs = UserLoginActivity::when(!empty($this->role), function ($query) {
            $query->where('userable_type', $this->roleMapping($this->role));
        })->orderBy('id', 'desc')->paginate($this->logPerPage, pageName: 'user-login-activity');
        return view('livewire.activity-log-handler', [
            'logs' => $logs,
            'userLogs' => $userLogs,
        ])->layout('layouts.admin');
    }
}
