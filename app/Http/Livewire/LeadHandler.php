<?php

namespace App\Http\Livewire;

use App\Models\Address;
use App\Models\Branch;
use App\Models\Category;
use App\Models\Exhibitor;
use App\Models\Lead;
use App\Models\MasterAddress;
use App\Models\Product;
use DB;
use Livewire\Component;

class LeadHandler extends Component
{
    public $leadSources = [];
    public $countries = [];
    public $leadId = null;

    public $lead = [
        'lead_no',
        'name' => '',
        'alias_name' => '',
        'type' => 'domestic',
        'category' => 'direct',
        'source',
        'currency' => '',
        'director_name' => '',
        'director_mobile' => '',
        'director_email' => '',
        'expo_participation' => '',
        'rating' => 0,
    ];
    public $primaryAddress = [
        'country' => '',
        'state' => '',
        'city' => '',
        'address' => '',
        'area' => '',
        'pincode' => '',
        'dial_code' => '',
        'landline_no' => '',
        'gst' => '',
        'pan' => '',
    ];
    public $primaryContact = [
        'contact_person' => '',
        'contact_no' => '',
        'email' => '',
        'designation' => '',
    ];
    public $otherAddress = [
        'state' => '',
        'city' => '',
        'gst' => '',
        'pan' => '',
        'landline_no' => '',
        'address' => '',
        'address_type' => '',
        'index' => '',
    ];
    public $otherContactPerson = [
        'contact_person' => '',
        'contact_no' => '',
        'email' => '',
        'designation' => '',
    ];
    public $productDetails = [
        'products' => [],
        'categories' => [],
    ];
    public $products = [];
    public $productCategories = [];
    public $otherBillingAddress = [];
    public $masterAddress = [];
    public $states = [];
    public $cities = [];
    public $areas = [];
    public $otherAddressCities = [];
    protected function rules()
    {
        $rules = [
            'lead.name' => 'required',
            'lead.source' => 'required',
            'primaryContact.contact_person' => 'required',
            'primaryContact.contact_no' => 'required',
            'primaryContact.email' => 'required|email',
            'primaryAddress.country' => 'required',
            'lead.director_email' => 'email',
        ];

        if ($this->lead['type'] === 'domestic') {
            $rules['primaryAddress.state'] = 'required';
        }

        return $rules;
    }

    protected $messages = [
        'lead.name.required' => 'Name is required',
        'lead.source.required' => 'Source is required',
        'primaryContact.contact_person.required' => 'Contact Person is required',
        'primaryContact.contact_no.required' => 'Contact No is required',
        'primaryContact.email.required' => 'Email is required',
        'primaryContact.email.email' => 'Email is invalid',
        'primaryAddress.state.required' => 'State is required',
        'primaryAddress.country.required' => 'Country is required',
        'lead.director_email.email' => 'Email is invalid',
    ];

    public function mount($leadId = null)
    {
        $leadNo = Lead::generateLeadNo();
        $this->lead['lead_no'] = $leadNo;
        $this->masterAddress = MasterAddress::all();
        $this->countries = getCountriesWithCurrencyCode();
        if ($this->lead['type'] === 'domestic') {
            $this->primaryAddress['country'] = 'India';
            $this->changeCountry();
        }
        $this->leadSources = Category::where('type', 'lead_source')->where('is_active', 1)->get();
        $this->products = Product::pluck('name', 'id');
        $this->productCategories = Category::where('type', 'product_category')->where('is_active', 1)->pluck('name', 'id');
        if ($this->primaryAddress['country'] === 'India') {
            $this->states = $this->masterAddress->pluck('State')->unique();
        }

        if (!empty($this->leadId)) {
            $this->editLead();
        }
    }
    public function editLead()
    {
        $leadData = Lead::with(['leadContact', 'branches.address'])->find($this->leadId);
        if (empty($leadData)) {
            return redirect()->back()->with('error', 'Lead not found');
        }
        $this->lead['lead_no'] = $leadData->lead_no ?? '';
        $this->lead['name'] = $leadData->name ?? '';
        $this->lead['alias_name'] = $leadData->alias_name ?? '';
        $this->lead['type'] = $leadData->type ?? '';
        $this->lead['category'] = $leadData->category ?? '';
        $this->lead['source'] = $leadData->lead_source_id ?? '';
        $this->lead['currency'] = $leadData->currency ?? '';
        $this->lead['director_name'] = $leadData->director_name ?? '';
        $this->lead['director_mobile'] = $leadData->director_mobile_no ?? '';
        $this->lead['director_email'] = $leadData->director_email ?? '';
        $this->lead['expo_participation'] = $leadData->other_expo_participation ?? '';
        $this->lead['rating'] = !empty($leadData->rating) ? $leadData->rating : 0;
        $this->productDetails['products'] = $leadData->product_id ?? [];
        $this->productDetails['categories'] = $leadData->product_category_id ?? [];

        $primaryContactPerson = $leadData->leadContact?->where('is_primary', 1)->first();
        $this->primaryContact['contact_person'] = $primaryContactPerson->name ?? '';
        $this->primaryContact['contact_no'] = $primaryContactPerson->contact_number ?? '';
        $this->primaryContact['email'] = $primaryContactPerson->email ?? '';
        $this->primaryContact['designation'] = $primaryContactPerson->designation ?? '';

        $primaryBranch = $leadData->branches?->where('is_head', 1)->first();
        $primaryAddress = $leadData->address?->where('id', $primaryBranch->address_id)->first();
        $this->primaryAddress['pincode'] = $primaryAddress->pincode ?? '';
        $this->primaryAddress['state'] = $primaryAddress->state ?? '';
        $this->getCities('primary', $this->primaryAddress['state']);
        $this->primaryAddress['city'] = $primaryAddress->city ?? '';
        $this->getAreas($this->primaryAddress['state'], $this->primaryAddress['city']);
        $this->primaryAddress['area'] = $primaryAddress->area ?? '';
        $this->primaryAddress['gst'] = $primaryBranch->gst ?? '';
        $this->primaryAddress['pan'] = $primaryBranch->pan ?? '';
        $this->primaryAddress['landline_no'] = $primaryAddress->landline_number ?? '';
        $this->primaryAddress['address'] = $primaryAddress->address ?? '';
        $primaryCountry = !empty($this->countries[$primaryAddress->country ?? '']) ? $this->countries[$primaryAddress->country] : [];
        $this->primaryAddress['country'] = !empty($primaryCountry) ? $primaryAddress->country : '';
        $this->primaryAddress['dial_code'] = $primaryCountry['dial_code'] ?? '';

        $allBranches = $leadData->branches?->where('is_head', 0);
        foreach ($allBranches as $branch) {
            $branchAddress = $branch->address;
            $contactPerson = $branch->leadContactPerson?->where('branch_id', $branch->id)->first();
            $this->otherBillingAddress[] = [
                'branch_id' => $branch->id,
                'country' => $branchAddress->country ?? '',
                'state' => $branchAddress->state ?? '',
                'city' => $branchAddress->city ?? '',
                'gst' => $branch->gst ?? '',
                'pan' => $branch->pan ?? '',
                'landline_no' => $branchAddress->landline_number ?? '',
                'address' => $branchAddress->address ?? '',
                'address_type' => $branchAddress->landmark ?? '',
                'contact_person' => $contactPerson->name ?? '',
                'contact_no' => $contactPerson->contact_number ?? '',
                'email' => $contactPerson->email ?? '',
                'designation' => $contactPerson->designation ?? '',
            ];
        }
    }
    public function createLead()
    {
        $this->validate();
        try {
            DB::beginTransaction();
            if ($this->checkLeadValidtion() === false) {
                return;
            };
            $authUser = getAuthData();
            $productList = $this->productDetails['products'] ?? [];
            $selectedProducts = [];
            foreach ($productList as $product) {

                if ((int) $product) {
                    $selectedProducts[] = $product;
                } else {
                    $newProduct = Product::create([
                        'name' => $product,
                    ]);

                    $selectedProducts[] = (string) $newProduct->id;
                }
            }
            $categoryList = $this->productDetails['categories'] ?? [];
            $selectedCategories = [];
            foreach ($categoryList as $category) {

                if ((int) $category) {
                    $selectedCategories[] = $category;
                } else {
                    $newCategory = Category::create([
                        'name' => $category,
                        'type' => 'product_category',
                    ]);

                    $selectedCategories[] = (string) $newCategory->id;
                }
            }
            $isLeadNoExists = Lead::where('lead_no', $this->lead['lead_no'])->exists();
            if ($isLeadNoExists) {
                $this->lead['lead_no'] = Lead::generateLeadNo();
            }
            $changeLeadNametoUpperCase = strtoupper(trim($this->lead['name']));
            $rating = !empty($this->lead['rating']) ? $this->lead['rating'] : 0;
            $lead = Lead::create([
                'lead_no' => $this->lead['lead_no'],
                'name' => $changeLeadNametoUpperCase,
                'alias_name' => $this->lead['alias_name'],
                'type' => $this->lead['type'],
                'category' => $this->lead['category'],
                'lead_source_id' => $this->lead['source'],
                'currency' => $this->lead['currency'],
                'director_name' => $this->lead['director_name'],
                'director_mobile_no' => $this->lead['director_mobile'],
                'director_email' => $this->lead['director_email'],
                'rating' => $rating,
                'product_category_id' => $selectedCategories,
                'product_id' => $selectedProducts,
                'other_expo_participation' => $this->lead['expo_participation'],
                'created_by' => $authUser['id'],
                'updated_by' => $authUser['id'],
            ]);

            $address = $lead->address()->create([
                'country' => $this->primaryAddress['country'],
                'state' => $this->primaryAddress['state'],
                'city' => $this->primaryAddress['city'],
                'area' => $this->primaryAddress['area'],
                'pincode' => $this->primaryAddress['pincode'],
                'address' => $this->primaryAddress['address'],
                'landline_number' => $this->primaryAddress['landline_no'],
                'landmark' => 'primary',
            ]);
            $branch = Branch::create([
                'gst' => $this->primaryAddress['gst'],
                'pan' => $this->primaryAddress['pan'],
                'is_head' => 1,
                'lead_id' => $lead->id,
                'address_id' => $address->id,
            ]);
            $branch->contactPersons()->create([
                'name' => $this->primaryContact['contact_person'],
                'contact_number' => $this->primaryContact['contact_no'],
                'email' => $this->primaryContact['email'],
                'designation' => $this->primaryContact['designation'],
                'is_primary' => 1,
                'lead_id' => $lead->id,
                'created_by' => $authUser['id'],
                'updated_by' => $authUser['id'],
            ]);
            if (count($this->otherBillingAddress) > 0) {
                foreach ($this->otherBillingAddress as $otherAddress) {
                    $otherAddressData = $lead->address()->create([
                        'country' => $otherAddress['country'],
                        'state' => $otherAddress['state'],
                        'city' => $otherAddress['city'],
                        'address' => $otherAddress['address'],
                        'landline_number' => $otherAddress['landline_no'],
                        'landmark' => $otherAddress['address_type'],
                    ]);
                    $otherBranch = Branch::create([
                        'gst' => $otherAddress['gst'],
                        'pan' => $otherAddress['pan'],
                        'is_head' => 0,
                        'lead_id' => $lead->id,
                        'address_id' => $otherAddressData->id,
                    ]);
                    $otherBranch->contactPersons()->create([
                        'name' => $otherAddress['contact_person'],
                        'contact_number' => $otherAddress['contact_no'],
                        'email' => $otherAddress['email'],
                        'designation' => $otherAddress['designation'],
                        'is_primary' => 0,
                        'lead_id' => $lead->id,
                        'created_by' => $authUser['id'],
                        'updated_by' => $authUser['id'],
                    ]);
                }
            }

            DB::commit();
            return redirect()->route('leads.summary')->with('success', 'Lead Created Successfully');
        } catch (\Exception $e) {
            DB::rollback();
            session()->flash('error', $e->getMessage());
            return;
        }
    }
    public function updateLead()
    {
        $this->validate();
        try {
            DB::beginTransaction();
            if ($this->checkLeadValidtion() === false) {
                return;
            };
            $authUser = getAuthData();
            $productList = $this->productDetails['products'] ?? [];
            $selectedProducts = [];
            foreach ($productList as $product) {

                if ((int) $product) {
                    $selectedProducts[] = $product;
                } else {
                    $newProduct = Product::create([
                        'name' => $product,
                    ]);

                    $selectedProducts[] = (string) $newProduct->id;
                }
            }
            $categoryList = $this->productDetails['categories'] ?? [];
            $selectedCategories = [];
            foreach ($categoryList as $category) {

                if ((int) $category) {
                    $selectedCategories[] = $category;
                } else {
                    $newCategory = Category::create([
                        'name' => $category,
                        'type' => 'product_category',
                    ]);

                    $selectedCategories[] = (string) $newCategory->id;
                }
            }

            $leadData = Lead::findOrFail($this->leadId);
            $changeLeadNametoUpperCase = strtoupper(trim($this->lead['name']));
            $rating = !empty($this->lead['rating']) ? $this->lead['rating'] : 0;
            $leadData->update([
                'name' => $changeLeadNametoUpperCase,
                'alias_name' => $this->lead['alias_name'],
                'type' => $this->lead['type'],
                'category' => $this->lead['category'],
                'lead_source_id' => $this->lead['source'],
                'currency' => $this->lead['currency'],
                'director_name' => $this->lead['director_name'],
                'director_mobile_no' => $this->lead['director_mobile'],
                'director_email' => $this->lead['director_email'],
                'rating' => $rating,
                'product_category_id' => $selectedCategories,
                'product_id' => $selectedProducts,
                'other_expo_participation' => $this->lead['expo_participation'],
                'updated_by' => $authUser['id'],
            ]);
            $branch = $leadData->branches()->where('is_head', 1)->first();
            $branch->update([
                'gst' => $this->primaryAddress['gst'],
                'pan' => $this->primaryAddress['pan'],
            ]);
            $branch->address()->update([
                'country' => $this->primaryAddress['country'],
                'state' => $this->primaryAddress['state'],
                'city' => $this->primaryAddress['city'],
                'area' => $this->primaryAddress['area'],
                'pincode' => $this->primaryAddress['pincode'],
                'address' => $this->primaryAddress['address'],
                'landline_number' => $this->primaryAddress['landline_no'],
            ]);
            $branch->contactPersons()->update([
                'name' => $this->primaryContact['contact_person'],
                'contact_number' => $this->primaryContact['contact_no'],
                'email' => $this->primaryContact['email'],
                'designation' => $this->primaryContact['designation'],
                'updated_by' => $authUser['id'],
            ]);
            if (count($this->otherBillingAddress) > 0) {
                $currentBranchIds = Branch::where('lead_id', $this->leadId)->where('id', '!=', $branch->id)->pluck('id')->toArray();
                $otherAddressBranchIds = array_column($this->otherBillingAddress, 'branch_id');
                $removedBranchIds = array_diff($currentBranchIds, $otherAddressBranchIds);
                if (count($removedBranchIds) > 0) {
                    foreach ($removedBranchIds as $removedBranchId) {
                        $branchExists = Branch::where('lead_id', $this->leadId)
                            ->where('id', $removedBranchId)
                            ->first();

                        if ($branchExists) {
                            if ($branchExists->address) {
                                $branchExists->address()->delete();
                            }
                            if ($branchExists->contactPersons) {
                                $branchExists->contactPersons()->delete();
                            }
                            $branchExists->delete();
                        }
                    }
                }
                foreach ($this->otherBillingAddress as $otherAddress) {
                    if (!empty($otherAddress['branch_id'])) {
                        $otherBranch = Branch::findOrFail($otherAddress['branch_id']);
                        $otherBranch->update([
                            'gst' => $otherAddress['gst'],
                            'pan' => $otherAddress['pan'],
                        ]);
                        $otherBranch->address()->update([
                            'country' => $this->primaryAddress['country'],
                            'state' => $otherAddress['state'],
                            'city' => $otherAddress['city'],
                            'address' => $otherAddress['address'],
                            'landline_number' => $otherAddress['landline_no'],
                            'landmark' => $otherAddress['address_type'],
                        ]);
                        $otherBranch->contactPersons()->update([
                            'name' => $otherAddress['contact_person'],
                            'contact_number' => $otherAddress['contact_no'],
                            'email' => $otherAddress['email'],
                            'designation' => $otherAddress['designation'],
                            'updated_by' => $authUser['id'],
                        ]);
                    } else {
                        $otherAddressData = $leadData->address()->create([
                            'country' => $this->primaryAddress['country'],
                            'state' => $otherAddress['state'],
                            'city' => $otherAddress['city'],
                            'address' => $otherAddress['address'],
                            'landline_number' => $otherAddress['landline_no'],
                            'landmark' => $otherAddress['address_type'],
                        ]);
                        $otherBranch = Branch::create([
                            'gst' => $otherAddress['gst'],
                            'pan' => $otherAddress['pan'],
                            'is_head' => 0,
                            'lead_id' => $leadData->id,
                            'address_id' => $otherAddressData->id,
                        ]);
                        $otherBranch->contactPersons()->create([
                            'name' => $otherAddress['contact_person'],
                            'contact_number' => $otherAddress['contact_no'],
                            'email' => $otherAddress['email'],
                            'designation' => $otherAddress['designation'],
                            'is_primary' => 0,
                            'lead_id' => $leadData->id,
                            'created_by' => $authUser['id'],
                            'updated_by' => $authUser['id'],
                        ]);
                    }
                }
            }
            DB::commit();
            return redirect()->route('leads.summary')->with('success', 'Lead Updated Successfully');
        } catch (\Exception $e) {
            DB::rollback();
            session()->flash('error', $e->getMessage());
            return;
        }
    }
    public function changeCountry()
    {
        if (isset($this->countries[$this->primaryAddress['country']])) {
            $this->lead['currency'] = $this->countries[$this->primaryAddress['country']]['currency_code'];
            $this->primaryAddress['dial_code'] = $this->countries[$this->primaryAddress['country']]['dial_code'];
        } else {
            $this->lead['currency'] = null;
            $this->primaryAddress['dial_code'] = null;
        }
    }
    public function checkLeadValidtion()
    {
        $trimmedLeadName = trim($this->lead['name']);
        $isExhibitorExist = Exhibitor::where('name', $trimmedLeadName)->exists();
        if ($isExhibitorExist) {
            $this->addError('lead.name', 'The Lead already exists as Exhibitor');
            return false;
        }
        $isLeadNameExist = Lead::where('name', $trimmedLeadName)
            ->when(!empty($this->leadId), function ($query) {
                $query->where('id', '!=', $this->leadId);
            })->exists();
        if ($isLeadNameExist) {
            $this->addError('lead.name', 'Lead name already exists');
            return false;
        }
        if (!empty($this->lead['director_mobile'])) {
            $isDirectorMobilenoExist = Lead::where('director_mobile_no', $this->lead['director_mobile'])
                ->when(!empty($this->leadId), function ($query) {
                    $query->where('id', '!=', $this->leadId);
                })->exists();
            if ($isDirectorMobilenoExist) {
                $this->addError('lead.director_mobile', 'Director mobile no already exists');
                return false;
            }
        }
        if (!empty($this->lead['director_email'])) {
            $isDirectorEmailExist = Lead::where('director_email', $this->lead['director_email'])
                ->when(!empty($this->leadId), function ($query) {
                    $query->where('id', '!=', $this->leadId);
                })->exists();
            if ($isDirectorEmailExist) {
                $this->addError('lead.director_email', 'Director email already exists');
                return false;
            }
        }
        $checkAdderss = $this->checkContactPersonandAddress('primaryAddress', $this->primaryAddress);
        if ($checkAdderss === false) {
            return false;
        }
    }
    public function checkContactPersonandAddress($label, $address)
    {
        if (!empty($address['landline_no'])) {
            $isLandlineNoExists = Address::where('landline_number', $address['landline_no'])
                ->when(!empty($this->leadId), function ($query) {
                    $query->where('addressable_id', '!=', $this->leadId)
                        ->where('addressable_type', 'App\Models\Lead');
                })->exists();
            if ($isLandlineNoExists) {
                $this->addError("$label.landline_no", 'Landline no already exists');
                return false;
            }
        }
        if (!empty($address['gst'])) {
            if (strlen($address['gst']) !== 15) {
                $this->addError("$label.gst", 'GST number must be 15 characters long');
                return false;
            }
            $isGSTExists = Branch::where('gst', $address['gst'])
                ->when(!empty($this->leadId), function ($query) {
                    $query->where('lead_id', '!=', $this->leadId);
                })->exists();
            if ($isGSTExists) {
                $this->addError("$label.gst", 'GST already exists');
                return false;
            }
        }
        if (!empty($address['pan'])) {
            if (strlen($address['pan']) !== 10) {
                $this->addError("$label.pan", 'PAN number must be 10 characters long');
                return false;
            }
            $isPANExists = Branch::where('pan', $address['pan'])
                ->when(!empty($this->leadId), function ($query) {
                    $query->where('lead_id', '!=', $this->leadId);
                })->exists();
            if ($isPANExists) {
                $this->addError("$label.pan", 'PAN already exists');
                return false;
            }
        }
    }
    public function addOtherBillingAddress()
    {
        $this->resetErrorBag();
        $addressHasValue = $this->hasValue($this->otherAddress);
        $contactHasValue = $this->hasValue($this->otherContactPerson);
        if ($addressHasValue || $contactHasValue) {
            if (empty(trim($this->otherAddress['address_type']))) {
                $this->addError('otherAddress.address_type', 'Address Type is required');
                return;
            }
            $checkOtherAddress = $this->checkContactPersonandAddress('otherAddress', $this->otherAddress);
            if ($checkOtherAddress === false) {
                return false;
            }
            $data = [
                'country' => $this->primaryAddress['country'],
                'state' => $this->otherAddress['state'],
                'city' => $this->otherAddress['city'],
                'gst' => $this->otherAddress['gst'],
                'pan' => $this->otherAddress['pan'],
                'landline_no' => $this->otherAddress['landline_no'],
                'address' => $this->otherAddress['address'],
                'address_type' => $this->otherAddress['address_type'],
                'contact_person' => $this->otherContactPerson['contact_person'],
                'contact_no' => $this->otherContactPerson['contact_no'],
                'email' => $this->otherContactPerson['email'],
                'designation' => $this->otherContactPerson['designation'],
            ];

            if (isset($this->otherAddress['index']) && $this->otherAddress['index'] !== '') {
                $data['branch_id'] = $this->otherBillingAddress[$this->otherAddress['index']]['branch_id'];
                $this->otherBillingAddress[$this->otherAddress['index']] = $data;
            } else {
                $data['branch_id'] = null;
                $this->otherBillingAddress[] = $data;
            }
            $this->closeModal();
        } else {
            $this->dispatch('showNoChangesMessage');
        }
    }
    public function assignDataToModal($index)
    {
        $address = $this->otherBillingAddress[$index];
        $this->getCities('other', $address['state']);
        $this->otherAddress = [
            'address_type' => $address['address_type'],
            'state' => $address['state'],
            'city' => $address['city'],
            'gst' => $address['gst'],
            'pan' => $address['pan'],
            'landline_no' => $address['landline_no'],
            'address' => $address['address'],
            'index' => $index,
        ];

        $this->otherContactPerson = [
            'contact_person' => $address['contact_person'],
            'contact_no' => $address['contact_no'],
            'email' => $address['email'],
            'designation' => $address['designation'],
        ];
    }
    public function removeAddress($index)
    {
        unset($this->otherBillingAddress[$index]);
        $this->resetErrorBag();
        $this->reset([
            'otherAddress',
            'otherContactPerson',
        ]);
    }
    public function closeModal()
    {
        $this->resetErrorBag();
        $this->reset([
            'otherAddress',
            'otherContactPerson',
        ]);
        $this->dispatch('closeModal');
    }
    public function changeLeadType()
    {
        if ($this->lead['type'] === 'domestic') {
            $this->primaryAddress['country'] = 'India';
        } else {
            $this->primaryAddress['country'] = '';
        }
        $data = [
            'country' => $this->primaryAddress['country'],
            'leadType' => $this->lead['type'],
        ];
        $this->primaryAddress['state'] = '';
        $this->primaryAddress['city'] = '';
        $this->otherAddress['state'] = '';
        $this->otherAddress['city'] = '';
        $this->primaryAddress['area'] = '';
        $this->primaryAddress['pincode'] = '';
        $this->cities = [];
        $this->otherAddressCities = [];
        $this->areas = [];
        $this->dispatch('currentDistrictAreas', $this->areas);
        $this->dispatch('enableTomSelect', $data);

    }
    public function hasValue($array)
    {
        foreach ($array as $key => $value) {
            if (!empty(trim($value))) {
                return true;
            }
        }
        return false;
    }
    public function getCities($type, $state)
    {
        if (!empty($state)) {
            if ($type === 'primary') {
                $this->cities = $this->masterAddress->where('State', $state)->pluck('District')->unique();
            } else {
                $this->otherAddressCities = $this->masterAddress->where('State', $state)->pluck('District')->unique();
            }
        }
    }
    public function getAreas($state, $city)
    {
        if (!empty($state) && !empty($city)) {
            $this->areas = $this->masterAddress->where('State', $state)->where('District', $city)->pluck('City', 'id')->unique();
        }
    }
    public function changeState($type)
    {
        if ($type === 'primary') {
            $this->primaryAddress['city'] = '';
            $this->primaryAddress['area'] = '';
            $this->primaryAddress['pincode'] = '';
            $this->cities = [];
            $this->areas = [];
            $this->dispatch('currentDistrictAreas', $this->areas);
            if (!empty($this->primaryAddress['state'])) {
                $this->getCities('primary', $this->primaryAddress['state']);
            }
        } else {
            $this->otherAddress['city'] = '';
            $this->cities = [];
            if (!empty($this->otherAddress['state'])) {
                $this->getCities('other', $this->otherAddress['state']);
            }
        }
    }
    public function changeCity()
    {
        $this->primaryAddress['area'] = '';
        $this->primaryAddress['pincode'] = '';
        $this->areas = [];
        if (!empty($this->primaryAddress['city'])) {
            $this->getAreas($this->primaryAddress['state'], $this->primaryAddress['city']);
        }
        $this->dispatch('currentDistrictAreas', $this->areas);
    }
    public function changeArea()
    {
        $this->primaryAddress['pincode'] = '';
        if (!empty($this->primaryAddress['area'])) {
            $this->primaryAddress['pincode'] = $this->masterAddress->where('City', $this->primaryAddress['area'])->first()->Pincode;
        }
    }
    public function checkLeadName()
    {
        $trimmedLeadName = trim($this->lead['name']);
        $isExhibitorExist = Exhibitor::where('name', $trimmedLeadName)->exists();
        if ($isExhibitorExist) {
            $this->addError('lead.name', 'The Lead already exists as Exhibitor');
            session()->flash('info', 'The Lead already exists as Exhibitor');
            return false;
        }
        $isLeadNameExist = Lead::where('name', $trimmedLeadName)
            ->when(!empty($this->leadId), function ($query) {
                $query->where('id', '!=', $this->leadId);
            })->exists();
        if ($isLeadNameExist) {
            $this->addError('lead.name', 'Lead name already exists');
            session()->flash('info', 'Lead name already exists');
            return false;
        }
    }
    public function render()
    {
        return view('livewire.lead-handler')->layout('layouts.admin');
    }
}
