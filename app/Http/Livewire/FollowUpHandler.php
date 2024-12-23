<?php

namespace App\Http\Livewire;

use Exception;
use App\Models\Lead;
use Livewire\Component;
use App\Models\Category;
use App\Models\FollowUp;
use App\Models\Exhibitor;
use App\Models\Potential;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\ExhibitorContact;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class FollowUpHandler extends Component
{

    public $potentialId;
    public $potential = [
        'event_name',
        'lead_name',
        'lead_id',
        'lead_category',
        'assigned_id',
        'primary_contact',
        'address',
    ];
    public $potential_status, $activity_type, $contact_mode, $remarks;
    public $lead = [];
    public $categories, $countries;
    public $autoUserName;
    public $isEditing, $authId;
    public $followUpId;

    protected $rules = [
        'potential_status' => 'required|string',
        'activity_type' => 'required|string',
        'contact_mode' => 'required|string',
        'lead.salutation' => 'required|string|in:Dr,Mr,Ms,Mrs',
        'lead.name' => 'required|string|min:2|max:100',
        'lead.contact_number' => 'required|numeric|digits_between:0,10',
        'lead.designation' => 'required|string|max:50',
        'lead.contact_email' => 'required|email|max:100',
        'lead.company_name' => 'required|string|max:100',
        'lead.category_id' => 'required|exists:categories,id',
        'lead.user_name' => 'required|string|unique:exhibitors,username|max:50',
        'lead.email' => 'required|email|max:100|unique:exhibitors,email',
        'lead.mobile_number' => 'required|numeric|digits_between:10,15',
        'lead.known_source' => 'required|string',
        'lead.country' => 'required|string|max:50',
        'lead.pincode' => 'required|numeric|min:100000|max:999999',
        'lead.city' => 'nullable|string|max:50',
        'lead.state' => 'nullable|string|max:50',
        'lead.address' => 'required|string|max:255',
        'lead.news_letter' => 'nullable|boolean',
    ];


    protected $messages = [
        'potential_status.required' => 'The potential status field is required.',
        'activity_type.required' => 'The activity type field is required.',
        'contact_mode.required' => 'The contact mode field is required.',
        'lead.salutation.required' => 'Please select a salutation.',
        'lead.name.required' => 'Contact person name is required.',
        'lead.name.min' => 'The name must be at least 2 characters long.',
        'lead.contact_number.required' => 'Contact number is required.',
        'lead.contact_number.numeric' => 'Contact number must be a valid number.',
        'lead.designation.required' => 'Designation is required.',
        'lead.contact_email.required' => 'Email is required.',
        'lead.contact_email.email' => 'Please provide a valid email address.',
        'lead.company_name.required' => 'Company name is required.',
        'lead.category_id.required' => 'Please select a business type.',
        'lead.user_name.required' => 'Profile name is required.',
        'lead.user_name.unique' => 'This profile name is already in use.',
        'lead.email.required' => 'Email is required.',
        'lead.email.unique' => 'This email is already registered.',
        'lead.mobile_number.required' => 'Phone number is required.',
        'lead.mobile_number.numeric' => 'Phone number must be a valid number.',
        'lead.known_source.required' => 'Please select how you came to know about us.',
        'lead.country.required' => 'Country is required.',
        'lead.pincode.required' => 'Pincode/Zipcode is required.',
        'lead.pincode.numeric' => 'Pincode must be a valid number.',
        'lead.address.required' => 'Address is required.',
    ];

    public function mount(Request $request)
    {
        $this->potentialId = $request->potentialId;

        $this->categories = Category::where('type', 'exhibitor_business_type')
            ->where('is_active', 1)
            ->select('id', 'name')->get();

        $this->countries = getCountries();

        $this->loadPotentialData();

        $this->authId = auth()?->id() ?? null;

        $this->followUpId = $request->followUpId ?? null;
        if (!empty($this->followUpId)) {
            $followup = FollowUp::find($this->followUpId);
            $this->potential_status = $followup->status;
            $this->activity_type = $followup->activity_type;
            $this->remarks = $followup->remarks;
            $this->contact_mode = $followup->contact_mode;
        }
    }
    public function loadPotentialData()
    {
        if ($this->potentialId) {
            $potential = Potential::findOrFail($this->potentialId);

            $this->potential = [
                'event_name' => $potential->event->title ?? '--',
                'lead_name' => $potential->lead->name ?? '--',
                'lead_id' => $potential->lead->lead_no ?? '--',
                'lead_category' => $potential->lead->category ?? '--',
                'assigned_id' => $potential->assignedPerson->name ?? '--',
                'primary_contact' => $potential->lead->contactPerson->contact_number ?? '--',
                'address' => $potential->lead->branchPrimary->address->address ?? '--',
            ];
        }
    }
    public function loadLeadData()
    {
        if ($this->potentialId) {
            $potential = Potential::findOrFail($this->potentialId);
            // dd($this->potentialId, $potential?->lead, $potential?->lead->contactPerson->id);
            $this->lead = [
                'id' => $potential?->lead?->id,
                'salutation' => $potential?->lead?->contactPerson?->salutation ?? '',
                'name' => $potential?->lead?->contactPerson?->name ?? '',
                'contact_number' => $potential?->lead?->contactPerson?->contact_number ?? '',
                'contact_email' => $potential?->lead?->contactPerson?->email ?? '',
                'designation' => $potential?->lead?->contactPerson?->designation ?? '',
                'company_name' => $potential?->lead?->exhibitor?->name ?? '',
                'category_id' => $potential?->lead?->exhibitor?->category_id ?? null,
                'user_name' => $potential?->lead?->exhibitor->username ?? '',
                'email' => $potential?->lead?->exhibitor?->email ?? '',
                'mobile_number' => $potential?->lead?->exhibitor?->mobile_number ?? '',
                'known_source' => $potential?->lead?->exhibitor?->known_source ?? '',
                'country' => $potential?->lead?->branchPrimary?->address?->country ?? '',
                'pincode' => $potential?->lead?->branchPrimary?->address?->pincode ?? '',
                'city' => $potential?->lead?->branchPrimary?->address?->city ?? '',
                'state' => $potential?->lead?->branchPrimary?->address?->state ?? '',
                'address' => $potential?->lead?->branchPrimary?->address?->address ?? '',
                'news_letter' => $potential?->lead?->exhibitor?->newsletter ?? false,
            ];
        }
    }
    // public function updatedPotentialStatus($value)
    // {
    //     if ($value === "closed won") {
    //         $this->dispatch('confirmEditExhibitor');
    //         $this->loadLeadData();
    //     }
    // }
    public function checkPotentialStatus($value)
    {
        if ($value === "closed won") {
            $this->dispatch('confirmEditExhibitor');
            $this->loadLeadData();
            $this->isEditing = true;
        }
    }
    public function updatedLeadCompanyName($value)
    {
        $this->lead['user_name'] = Str::replace(' ', '', $value);
        try {
            $this->validateOnly('lead.user_name');
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->autoGenerateUserName();
        }
    }
    public function updatedLeadUserName($value)
    {
        try {
            $this->validateOnly('lead.user_name');
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->autoGenerateUserName();
        }
    }
    public function resetFields()
    {
        // $this->reset(['potential_status', 'activity_type', 'contact_mode', 'remarks']);
        $this->reset();
    }
    public function clearLocationFields()
    {
        $this->lead['city'] = null;
        $this->lead['state'] = null;
        $this->lead['pincode'] = null;
    }
    public function pincode()
    {
        if (strtolower($this->lead['country']) == 'india' && isset($this->lead['pincode'])) {
            $pincodeData = getPincodeData($this->lead['pincode']);
            if ($pincodeData['state'] === null && $pincodeData['city'] === null) {
                $this->addError("lead.pincode", "Pincode is not Exists");
                $this->lead['state'] = null;
                $this->lead['city'] = null;
            } else {
                $this->resetErrorBag("lead.pincode");
                $this->lead['state'] = $pincodeData['state'];
                $this->lead['city'] = $pincodeData['city'];
            }
        }
    }
    public function autoGenerateUserName()
    {
        $companyName = $this->lead['company_name'];
        $randomNumber = rand(1000, 9999);
        $this->autoUserName = $companyName . $randomNumber;
        $this->lead['user_name'] =  $this->autoUserName;
    }
    public function createFollowUp()
    {
        $this->validate([
            'potential_status' => 'required|string',
            'activity_type' => 'required|string',
            'contact_mode' => 'required|string',
        ]);
        try {
            $potential = Potential::find($this->potentialId);
            if ($potential) {
                $potential->update(['status' => $this->potential_status]);
            }

            $followUp = FollowUp::create([
                'potential_id' => $this->potentialId,
                'status' => $this->potential_status,
                'activity_type' => $this->activity_type,
                'contact_mode' => $this->contact_mode,
                'remarks' => $this->remarks,
                'created_by' => $this->authId,
                'updated_by' => $this->authId,
            ]);

            $this->checkPotentialStatus($this->potential_status);

            if ($this->isEditing) {
                return;
            }
            $message = $followUp
                ? 'Follow Up created successfully.'
                : 'Cannot create follow-up.';
            session()->flash('success', $message);
            return redirect()->route('potential-follow-up', ['potentialId' => $this->potentialId]);
        } catch (Exception $e) {
            session()->flash('error', 'An error occurred: ' . $e->getMessage());
            return redirect()->route('potential-follow-up', ['potentialId' => $this->potentialId]);
        }
    }
    public function editLeadDetails()
    {
        $this->validateOnly('lead');
        DB::beginTransaction();

        try {
            $lead = Lead::find($this->lead['id']);
            $potential = Potential::find($this->potentialId);
            $exhibitor = null;
            $isExhibitorCreatedOrUpdated = null;
            $isContactPersonCreatedOrUpdated = null;

            if ($lead) {
                if ($lead->exhibitor) {
                    $lead->exhibitor->update([
                        'name' => $this->lead['company_name'] ?? '',
                        'category_id' => $this->lead['category_id'] ?? null,
                        'username' => $this->lead['user_name'] ?? '',
                        'email' => $this->lead['email'] ?? '',
                        'mobile_number' => $this->lead['mobile_number'] ?? '',
                        'known_source' => $this->lead['known_source'] ?? '',
                        'newsletter' => $this->lead['news_letter'] ?? false,
                        'registration_type' => 'web',
                        'lead_id' => $lead->id ?? null,
                        'sales_person_id' => $potential->sales_person_id,
                        'updated_by' => $this->authId,
                    ]);
                    $lead?->contactPerson->update([
                        'salutation' => $this->lead['salutation'],
                        'name' => $this->lead['name'],
                        'contact_number' => $this->lead['contact_number'],
                        'email' => $this->lead['contact_email'] ?? '',
                        'designation' => $this->lead['designation'] ?? '',
                        'lead_id' => $lead->id ?? null,
                        'branch_id' => $lead?->branchPrimary?->id ?? null,
                        'exhibitor_id' => $exhibitor->id ?? $lead->exhibitor->id,
                        'updated_by' => $this->authId,
                    ]);
                    $lead->update(['exhibitor_id' => $lead->exhibitor->id,  'updated_by' => $this->authId,]);
                    $lead?->branchPrimary->update([
                        'exhibitor_id' => $lead->exhibitor->id,
                    ]);
                    $lead?->branchPrimary?->address->update([
                        'country' => $this->lead['country'],
                        'pincode' => $this->lead['pincode'],
                        'city' => $this->lead['city'],
                        'state' => $this->lead['state'],
                        'address' => $this->lead['address'],
                    ]);
                    $isExhibitorCreatedOrUpdated = $lead?->exhibitor->wasChanged('name', 'newsletter', 'category_id', 'username', 'email', 'mobile_number', 'known_source');
                    $isContactPersonCreatedOrUpdated = $lead?->contactPerson->wasChanged('salutation', 'name', 'contact_number', 'email', 'designation');
                } else {

                    $exhibitor = Exhibitor::create([
                        'name' => $this->lead['company_name'] ?? '',
                        'category_id' => $this->lead['category_id'] ?? null,
                        'username' => $this->lead['user_name'] ?? '',
                        'email' => $this->lead['email'] ?? '',
                        'mobile_number' => $this->lead['mobile_number'] ?? '',
                        'known_source' => $this->lead['known_source'] ?? '',
                        'newsletter' => $this->lead['news_letter'] ?? false,
                        'registration_type' => 'web',
                        'lead_id' => $this->lead['id'] ?? null,
                        'sales_person_id' => $potential->sales_person_id,
                        'created_by' => $this->authId,
                        'updated_by' => $this->authId,
                    ]);
                    $exhibitor->exhibitorContact()->create([
                        'salutation' => $this->lead['salutation'],
                        'name' => $this->lead['name'],
                        'contact_number' => $this->lead['contact_number'],
                        'email' => $this->lead['contact_email'] ?? '',
                        'designation' => $this->lead['designation'] ?? '',
                        'exhibitor_id' => $exhibitor->id ?? $lead->exhibitor->id,
                        'lead_id' => $this->lead['id'],
                        'branch_id' => $lead?->branchPrimary?->id ?? null,
                        'created_by' => $this->authId,
                        'updated_by' => $this->authId,
                    ]);
                    $lead->update([
                        'exhibitor_id' => $exhibitor->id,
                        'updated_by' => $this->authId,
                    ]);
                    $lead?->branchPrimary->update(['exhibitor_id' => $exhibitor->id]);
                    $lead?->branchPrimary?->address->update([
                        'country' => $this->lead['country'],
                        'pincode' => $this->lead['pincode'],
                        'city' => $this->lead['city'],
                        'state' => $this->lead['state'],
                        'address' => $this->lead['address'],
                    ]);
                    // dd($exhibitor);
                    $isExhibitorCreatedOrUpdated = $exhibitor->wasChanged('name', 'newsletter', 'category_id', 'username', 'email', 'mobile_number', 'known_source');
                    $isContactPersonCreatedOrUpdated = $exhibitor?->exhibitorContact->wasChanged('salutation', 'name', 'contact_number', 'email', 'designation');
                }
                DB::commit();
                if ($isExhibitorCreatedOrUpdated || $isContactPersonCreatedOrUpdated) {
                    $this->isEditing = false;
                    session()->flash('success', 'Lead details successfully updated or created!');
                    return redirect()->route('potential-follow-up', ['potentialId' => $this->potentialId]);
                }
            } else {
                session()->flash('error', 'Lead Not Found!');
                return redirect()->route('potential-follow-up', ['potentialId' => $this->potentialId]);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Something went wrong: ' . $e->getMessage());
            return redirect()->route('potential-follow-up', ['potentialId' => $this->potentialId]);
        }
    }
    public function updateFollowUp()
    {
        $this->validate([
            'potential_status' => 'required|string',
            'activity_type' => 'required|string',
            'contact_mode' => 'required|string',
        ]);
        try {
            $followUp = FollowUp::find($this->followUpId);
            if ($followUp) {
                $potential = Potential::find($this->potentialId);
                if ($potential && $potential->status !== $this->potential_status) {
                    $potential->update(['status' => $this->potential_status]);
                }
                $followUp->update([
                    'status' => $this->potential_status,
                    'activity_type' => $this->activity_type,
                    'contact_mode' => $this->contact_mode,
                    'remarks' => $this->remarks,
                    'updated_by' => $this->authId,
                ]);
                // $this->checkPotentialStatus($this->potential_status);
                session()->flash('success', 'Follow Up updated successfully.');
                return redirect()->route('potential-follow-up', ['potentialId' => $this->potentialId]);
            } else {
                session()->flash('error', 'FollowUp not found.');
                return redirect()->route('potential-follow-up', ['potentialId' => $this->potentialId]);
            }
        } catch (Exception $e) {
            session()->flash('error', 'An error occurred: ' . $e->getMessage());
            return redirect()->route('potential-follow-up', ['potentialId' => $this->potentialId]);
        }
    }
    public function render()
    {
        return view('livewire.follow-up-handler')->layout('layouts.admin');
    }
}
