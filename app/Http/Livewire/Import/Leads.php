<?php

namespace App\Http\Livewire\Import;

use App\Models\Branch;
use App\Models\Category;
use App\Models\Lead;
use Livewire\Component;

class Leads extends Component
{
    public function render()
    {
        return view('livewire.import.leads')->layout('layouts.admin');
    }

    public function importingData($data)
    {
        if (count($data) <= 1) {
            session()->flash('err_message', 'No data to import');
            return;
        }

        $insertedLeadsCount = 0;
        $updatedLeadsCount = 0;
        $authData = getAuthData();

        for ($ii = 1; $ii < count($data); $ii++) {
            $companyName = $data[$ii][0];
            if (empty($companyName)) {
                continue;
            }
            $companyName = strtoupper(trim($companyName));
            $leadType = $data[$ii][1] ?? 'domestic';
            $leadCategory = $data[$ii][2] ?? 'direct';
            $leadSource = $data[$ii][3] ?? '';
            $directorName = $data[$ii][4] ?? '';
            $directorMobile = $data[$ii][5] ?? '';
            $country = $data[$ii][6];
            $state = $data[$ii][7];
            $city = $data[$ii][8];
            $address = $data[$ii][9] ?? '';
            $contactPerson = $data[$ii][10] ?? '';
            $contactNo = $data[$ii][11] ?? '';
            $email = $data[$ii][12] ?? '';

            $leadType = strtolower($leadType) == 'domestic' ? 'domestic' : 'international';
            $leadCategory = strtolower($leadCategory) == 'direct' ? 'direct' : 'agent';
            $country = ($leadType == 'domestic' || empty($country)) ? 'India' : $country;
            $checkLeadSource = Category::where('name', $leadSource)->where('type', 'lead_source')->first();
            if (!$checkLeadSource) {
                $checkLeadSource = Category::create([
                    'name' => $leadSource,
                    'type' => 'lead_source',
                    'created_by' => $authData->id,
                    'updated_by' => $authData->id,
                ]);
            }
            $leadSource = $checkLeadSource->id;

            $lead = Lead::where('name', $companyName)->first();
            if (!empty($lead)) {
                $lead->update([
                    'type' => $leadType,
                    'category' => $leadCategory,
                    'lead_source_id' => $leadSource,
                    'director_name' => $directorName,
                    'director_mobile_no' => $directorMobile,
                    'updated_by' => $authData->id,
                ]);

                $branch = $lead->branches()->where('is_head', 1)->first();
                $branch->address()->update([
                    'country' => $country,
                    'state' => $state,
                    'city' => $city,
                    'address' => $address,
                ]);
                $branch->contactPersons()->update([
                    'name' => $contactPerson,
                    'contact_number' => $contactNo,
                    'email' => $email,
                    'updated_by' => $authData->id,
                ]);
                $updatedLeadsCount++;
            } else {
                $leadNo = Lead::generateLeadNo();
                $lead = Lead::create([
                    'lead_no' => $leadNo,
                    'name' => $companyName,
                    'type' => $leadType,
                    'category' => $leadCategory,
                    'lead_source_id' => $leadSource,
                    'director_name' => $directorName,
                    'director_mobile_no' => $directorMobile,
                    'created_by' => $authData->id,
                    'updated_by' => $authData->id,
                ]);

                $address = $lead->address()->create([
                    'country' => $country,
                    'address' => $address,
                    'landmark' => 'primary',
                ]);

                $branch = Branch::create([
                    'is_head' => 1,
                    'lead_id' => $lead->id,
                    'address_id' => $address->id,
                ]);

                $branch->contactPersons()->create([
                    'name' => $contactPerson,
                    'contact_number' => $contactNo,
                    'email' => $email,
                    'is_primary' => 1,
                    'lead_id' => $lead->id,
                    'created_by' => $authData->id,
                    'updated_by' => $authData->id,
                ]);

                $insertedLeadsCount++;
            }
        }
        session()->flash('success', 'Imported ' . $insertedLeadsCount . ' leads');
        session()->flash('info', 'Updated ' . $updatedLeadsCount . ' leads');
        return redirect()->route('import.leads');
    }
}
