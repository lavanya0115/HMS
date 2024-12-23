<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Livewire\Attributes\Validate;

class PermissionsHandler extends Component
{
    #[Validate('required', message: 'The Role is required.')]
    public $role;
    public $roles = [];
    public $permissionsCategory = [];
    public $permissionIds = [];
    public function mount()
    {
        $user = getAuthData();
        $permissions = Permission::get();
        foreach ($permissions as $permission) {
            $this->permissionsCategory[$permission->category_name][] = $permission;
        }
        $this->roles = Role::where('is_active', 1)
            ->when($user->hasRole('Enterprise Admin'), function ($query) {
                return $query;
            })
            ->when($user->hasRole('Super Admin'), function ($query) {
                return $query->whereNotIn('name', ['Enterprise Admin']);
            })
            ->when($user->hasRole('Admin'), function ($query) {
                return $query->whereNotIn('name', ['Super Admin', 'Enterprise Admin', 'Admin']);
            })
            ->when(!$user->hasAnyRole(['Super Admin', 'Enterprise Admin', 'Admin']), function ($query) {
                return collect();
            })
            ->pluck('name', 'id');
    }
    public function assignPermissions()
    {
        $this->validate();
        $role = Role::find($this->role);
        $role->permissions()->sync($this->permissionIds);
        session()->flash('success', 'Permissions assigned successfully.');
        return redirect(route('permissions'));
    }

    public function getPermissionIds()
    {
        $this->resetErrorBag('role');
        $role = Role::find($this->role);
        if ($role) {
            $this->permissionIds = $role->permissions()->pluck('id')->toArray();
        } else {
            $this->permissionIds = [];
        }
    }
    public function render()
    {
        return view('livewire.permissions-handler')->layout('layouts.admin');
    }
}
