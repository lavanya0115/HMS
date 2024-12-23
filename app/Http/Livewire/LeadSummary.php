<?php

namespace App\Http\Livewire;

use App\Models\Lead;
use Livewire\Component;
use App\Models\Category;
use Livewire\WithPagination;
use Spatie\Activitylog\Models\Activity;

class LeadSummary extends Component
{
    use WithPagination;
    public $showFilter = true;
    public $leadsTotalCount = 0;
    public $leadType = '';
    public $leadCategory = '';
    public $leadCountry = '';
    public $source = '';
    public $leadSources = [];
    public $search = '';
    public $productCategory = '';
    public $countries = [];
    public $productCategories = [];
    public $sortDirection = 'desc';
    public $sortBy = 'id';

    public function mount()
    {
        $this->leadsTotalCount = Lead::count();
        $this->countries = getCountriesWithCurrencyCode();
        $this->leadSources = Category::where('type', 'lead_source')->get();
        $this->productCategories = Category::where('type', 'product_category')->pluck('name', 'id');
    }
    public function toggleFilter()
    {
        $this->showFilter = !$this->showFilter;
    }
    public function sortColumn($field, $order = 'asc')
    {
        $this->sortDirection = $order;
        $this->sortBy = $field;
    }
    public function applySorting($query)
    {
        if ($this->sortBy && in_array($this->sortBy, ['id', 'lead_no', 'name', 'created_at', 'updated_at'])) {
            $query->orderBy($this->sortBy, $this->sortDirection);
        }
    }
    public function getFilteredLeads()
    {
        $leads = Lead::with('leadContact', 'branches', 'address')->when($this->leadType, function ($query) {
            $query->where('type', $this->leadType);
        })->when($this->leadCategory, function ($query) {
            $query->where('category', $this->leadCategory);
        })->when($this->leadCountry, function ($query) {
            $query->whereHas('address', function ($query) {
                $query->where('country', $this->leadCountry);
            });
        })->when($this->source, function ($query) {
            $query->where('lead_source_id', $this->source);
        })->when($this->search, function ($query) {
            $query->where('name', 'like', '%' . $this->search . '%')
                ->orWhere('lead_no', 'like', '%' . $this->search . '%');
        })->when($this->productCategory, function ($query) {
            $query->whereJsonContains('product_category_id', $this->productCategory);
        })
            ->when($this->sortBy, function ($query) {
                $this->applySorting($query);
            })
            ->paginate(20)
            ->through(function ($lead) {
                $contactPerson = $lead->leadContact?->where('is_primary', 1)->first();
                $headBranch = $lead->branches?->where('is_head', 1)->first();
                $address = $headBranch->address ?? '';

                return [
                    'id' => $lead->id,
                    'lead_no' => $lead->lead_no,
                    'name' => $lead->name ?? '',
                    'type' => $lead->type ?? '',
                    'source' => $lead->leadSource?->name ?? '',
                    'category' => $lead->category ?? '',
                    'contact_person' => $contactPerson->name ?? '',
                    'contact_no' => $contactPerson->contact_number ?? '',
                    'contact_email' => $contactPerson->email ?? '',
                    'designation' => $contactPerson->designation ?? '',
                    'address' => $address->address ?? '',
                    'country' => $address->country ?? '',
                    'city' => $address->city ?? '',
                    'state' => $address->state ?? '',
                    'pincode' => $address->pincode ?? '',
                    'pincode' => $address->area ?? '',
                    'landline_no' => $address->landline_number ?? '',
                    'director_name' => $lead->director_name ?? '',
                    'director_mobile' => $lead->director_mobile_no ?? '',
                    'director_email' => $lead->director_email ?? '',
                    'gst_no' => !empty($headBranch->gst) ? $headBranch->gst : '--',
                    'products' => $lead->getProductNames() ?? '--',
                    'categories' => $lead->getCategoryNames() ?? '--',
                    'created_at' => $lead->created_at->format('d-m-Y'),
                    'updated_at' => $lead->updated_at->format('d-m-Y'),
                    'created_by' => $lead->createdByUser?->name ?? '',
                    'updated_by' => $lead->updatedByUser?->name ?? '',
                ];
            });
        return $leads;
    }
    public function render()
    {
        $leads = $this->getFilteredLeads();
        $leadActivities = Activity::where('log_name', 'lead_log')->orderBy('id', 'desc')->paginate(10, pageName: 'activity');
        // dump($leadActivities);
        return view('livewire.lead-summary', compact([
            'leadActivities',
            'leads'
        ]))->layout('layouts.admin');
    }
}
