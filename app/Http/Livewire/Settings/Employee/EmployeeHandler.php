<?php

namespace App\Http\Livewire\Settings\Employee;

use App\Models\Category;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Spatie\Permission\Models\Role;

class EmployeeHandler extends Component
{
    public $departments;
    public $roles;
    #[Validate('required', message: 'The type is required.')]
    public $roleId;

    public $employee = [
        'name' => '',
        'email' => '',
        'mobile_number' => '',
        'is_active' => 1,
    ];

    protected $rules = [
        'employee.emp_no' => 'required|string',
        'employee.name' => 'required|string',
        'employee.mobile_number' => 'required|digits:10',
        'employee.email' => 'required|string|email',
    ];

    protected $messages = [
        'employee.emp_no.required' => 'The employee number filed is required.',
        'employee.name.required' => 'The employee name field is required.',
        'employee.mobile_number.required' => 'The phone.no name field is required.',
        'employee.mobile_number.digits' => 'Please give the valid phone no',
        'employee.email.required' => 'The email address field is required.',
        // 'employee.department_id.required ' => 'The department field is required',
    ];

    public function mount($employeeId)
    {
        $user = getAuthData();
        // $this->departments = Category::where('type', 'department')
        //     ->where('is_active', 1)
        //     ->pluck('name', 'id')
        //     ->toArray();
        $this->departments =[];
       
        if ($employeeId) {
           
            $employee = User::find($employeeId);
            if ($employee) {
                $this->employee = $employee->toArray();
            } else {
                return redirect()->back()->with('warning', 'Employee not found');
            }
        }
    }

    public function create()
    {
        $this->validate();

        $employeeEmailExists = User::where('email', $this->employee['email'])->first();

        if ($employeeEmailExists) {
            $this->addError('employee.email', 'Employee email address already exists.');
            return;
        }

        $employeePhoneNoExists = User::where('mobile_number', $this->employee['mobile_number'])->first();

        if ($employeePhoneNoExists) {
            $this->addError('employee.mobile_number', 'Employee phone number already exists.');
            return;
        }

        $authorId = auth()->user()->id;
        $this->employee['created_by'] = $authorId;
        $this->employee['updated_by'] = $authorId;

        $this->employee['department_id'] = 0;

        // try {
            $this->employee['password'] = Hash::make(config('app.default_user_password'));
            $employee = User::create($this->employee);
    
            if ($employee) {
                session()->flash('success', 'Employee created successfully.');
                // $this->dispatch('callShowNoticeEvent', 'Success', 'Employee created successfully.');
                return redirect()->route('employees.index');
            }
            session()->flash('info', 'Something went wrong employee not created');
            // $this->dispatch('callShowNoticeEvent', 'info', 'Something went wrong employee not created');
        // } catch (\Exception $e) {
        //     session()->flash('error', $e->getMessage());

        // }
    }

    public function update()
    {
        $this->validate();

        $employeeEmailExists = User::where('email', $this->employee['email'])->where('id', '!=', $this->employee['id'])->first();
        if ($employeeEmailExists) {
            $this->addError('employee.email', 'Employee Email Address already exists.');
            return;
        }
        $employeePhoneNoExists = User::where('mobile_number', $this->employee['mobile_number'])->where('id', '!=', $this->employee['id'])->first();
        if ($employeePhoneNoExists) {
            $this->addError('employee.mobile_number', 'Employee Phone number already exists.');
            return;
        }

        $authorId = auth()->user()->id;
        $this->employee['updated_by'] = $authorId;

        if ($this->employee['type'] === 'caller') {
            $this->employee['department_id'] = 0;
        }

        try {
            $employee = User::find($this->employee['id']);
            $isSalesPersonCount = $employee->exhibitors->where('sales_person_id', $this->employee['id'])->count();

            if ($employee->type != $this->employee['type'] && $isSalesPersonCount > 0) {
                session()->flash('info', 'Unmap the exhibitors from ' . $employee->name . ' to update their type.');
                return redirect(route('employees.index'));
            }
            $employee->update($this->employee);
            
            session()->flash('success', 'Employee updated successfully.');
            $this->dispatch('callShowNoticeEvent', 'success', 'Employee updated successfully.');
            $this->redirect(route('employees.index'));
        } catch (\Exception $e) {
            $this->dispatch('callShowNoticeEvent', 'error', $e->getMessage());
            return;
        }
    }

    public function render()
    {
        return view('livewire.settings.employee.employee-handler')
            ->layout('layouts.admin');
    }
}
