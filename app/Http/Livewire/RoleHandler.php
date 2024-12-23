<?php

namespace App\Http\Livewire;

use App\Models\User;
use Livewire\Component;
use Livewire\Attributes\Validate;
use Spatie\Permission\Models\Role;

class RoleHandler extends Component
{
    public $roleId = null;
    public $role = [
        'name' => '',
        'is_active' => true
    ];
    public $is_active = 1;
    protected $rules = [
        'role.name' => 'required|unique:roles,name',
    ];

    protected $messages = [
        'role.name.required' => 'Role is required',
        'role.name.unique' => 'Role already exists',
    ];
    public function create()
    {
        $this->authorize('Create Role');
        $this->validate();
        $role = Role::create($this->role);
        session()->flash('success', 'Role created successfully.');
        return redirect(route('roles'));
    }
    public function edit($id)
    {
        $this->resetErrorBag();
        $this->roleId = $id;
        $roleData = Role::find($this->roleId);
        $this->role['name'] = $roleData->name;
        $this->role['is_active'] = $roleData->is_active ? true : false;
    }
    public function update()
    {
        $roleExists = Role::where('name', $this->role['name'])
            ->where('id', '!=', $this->roleId)->first();

        if ($roleExists) {
            $this->addError("role.name", "Role already exists");
            return;
        }
        if ($this->roleId) {
            $role = Role::find($this->roleId);
            $role->update([
                'name' => $this->role['name'],
                'is_active' => $this->role['is_active']
            ]);
        }
        session()->flash('success', 'Role updated successfully.');
        return redirect(route('roles'));
    }
    public function delete($id)
    {
        $role = Role::find($id);
        // $roleExists = User::where('type', $role->id)->first();
        // if ($roleExists) {
        //     $checkDeletedStatus = $this->js("confirm('Role can not be deleted as it is assigned to user')");
        //     if ($checkDeletedStatus) {
        //         $role->delete();
        //     }

        // } else {
        //     if ($this->js("confirm('Are you sure you want to delete this role?')")) {
        //         $role->delete();
        //     }
        // }
        $role->delete();
        session()->flash('success', 'Role deleted successfully.');
        return redirect(route('roles'));
    }
    public function render()
    {
        $user = getAuthData();
        $roles = Role::orderBy('id')
            ->when($user->hasRole('Enterprise Admin'), function ($query) {
                return $query;
            })
            ->when($user->hasRole('Super Admin'), function ($query) {
                return $query->whereNotIn('name', ['Super Admin', 'Enterprise Admin']);
            })
            ->when(!$user->hasAnyRole(['Super Admin', 'Enterprise Admin']), function ($query) {
                return $query->whereNotIn('name', ['Super Admin', 'Enterprise Admin', 'Admin']);
            })
            ->paginate(10);
        return view('livewire.role-handler', ['roles' => $roles])->layout('layouts.admin');
    }
}
