<?php

namespace App\Http\Livewire\Import;

use App\Models\Exhibitor;
use App\Models\Product;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Livewire\Attributes\On;
use Livewire\Component;

class Exhibitors extends Component
{

    public function render()
    {
        return view('livewire.import.exhibitors')->layout('layouts.admin');
    }

    #[On('importingData')]
    public function import($data)
    {
        if (count($data) <= 1) {
            session()->flash('err_message', 'No data to import');
            return;
        }
        $insertedExhibitorsCount = 0;
        $updatedExhibitorsCount = 0;
        $currentEvent = getCurrentEvent();

        for ($ii = 1; $ii < count($data); $ii++) {
            $stallNo = $data[$ii][0];
            $companyName = $data[$ii][1];
            $address = $data[$ii][2];
            $city = $data[$ii][3];
            $state = $data[$ii][4];
            $country = $data[$ii][5];
            $pincode = $data[$ii][6];
            $officePhoneNo = $data[$ii][7];
            $contactPersons = $data[$ii][8];
            $contactPersonCellPhones = $data[$ii][9];
            $email = $data[$ii][10];
            $website = $data[$ii][11];
            $products = $data[$ii][12] ?? '';
            $picture = $data[$ii][13];
            $description = $data[$ii][14] ?? '';
            $hallNo = $data[$ii][15] ?? '';

            $profileName = Str::replace(' ', '', $companyName);
            $profileName = $profileName . rand(9999, 9999999);

            // email
            $email = str_replace('/', ',', $email);
            $emailIds = explode(',', $email);
            $primaryEmailAddress = $emailIds[0] ?? '';
            $primaryEmailAddress = trim($primaryEmailAddress);
            $alternativeEmails = [];
            if (count($emailIds) > 1) {
                for ($i = 1; $i < count($emailIds); $i++) {
                    $alternativeEmails[] = trim($emailIds[$i]);
                }
            }

            // products
            $products = str_replace('/', ',', $products);
            $products = str_replace(';', ',', $products);
            $products = explode(',', $products);

            $country = !empty($country) ? $country : 'India';
            // contact persons
            $contactPersons = str_replace('/', ',', $contactPersons);
            $contactPersonCellPhones = str_replace('/', ',', $contactPersonCellPhones);
            $contactPersons = explode(',', $contactPersons);
            $contactPersonCellPhones = explode(',', $contactPersonCellPhones);

            if (empty($primaryEmailAddress)) {
                continue;
            }

            $productIds = [];

            if ($products) {
                foreach ($products as $product) {
                    $product = trim($product);
                    $product = strtolower($product);
                    $product = Product::firstOrCreate(['name' => $product]);
                    $productIds[] = strval($product->id);
                }
            }
            $exhibitor = Exhibitor::where('email', $primaryEmailAddress)->first();
            if (isset($exhibitor->id)) {
                $registeredInCurrentEvent = $exhibitor->eventExhibitors()->where('event_id', $currentEvent->id)->first();
                if ($registeredInCurrentEvent) {
                    $registeredInCurrentEvent->update([
                        'stall_no' => $stallNo,
                        'is_active' => 1,
                        'products' => $productIds,
                        'hall_no' => $hallNo,
                    ]);
                } else {
                    $exhibitor->eventExhibitors()->create([
                        'event_id' => $currentEvent->id ?? 0,
                        'exhibitor_id' => $exhibitor->id,
                        'stall_no' => $stallNo,
                        'is_sponsorer' => 0,
                        'products' => $productIds,
                        'is_active' => 1, // set active by default
                        'hall_no' => $hallNo,
                    ]);
                }

                foreach ($productIds as $productId) {
                    $productExists = $exhibitor->exhibitorProducts()->where('product_id', intval($productId))->first();
                    if ($productExists) {
                        continue;
                    }
                    $exhibitor->exhibitorProducts()->create([
                        'exhibitor_id' => $exhibitor->id,
                        'product_id' => intval($productId),
                    ]);
                }

                $updatedExhibitorsCount++;

                continue;
            }

            $exhibitorData = [
                'salutation' => "Mr",
                'username' => $profileName,
                'name' => $companyName,
                'email' => $primaryEmailAddress,
                'designation' => "",
                'mobile_number' => $officePhoneNo,
                'password' => Hash::make(config('app.default_user_password')),
                'registration_type' => 'import-online',
                'description' => $description,
                '_meta' => [
                    'website_url' => $website,
                    'alternative_emails' => $alternativeEmails,
                    'alternate_mobile' => [],
                    'welcome_message_sent_for_mumbai_2024' => 'no',
                ],
            ];

            $exhibitor = Exhibitor::create($exhibitorData);

            // sendWelcomeMessageThroughWhatsappBot($exhibitor->mobile_number, 'exhibitor');

            Log::info('Contact Persons: ' . json_encode($contactPersons));
            if (!empty($contactPersons)) {
                foreach ($contactPersons as $contactPersonIndex => $contactPerson) {
                    $contactPerson = trim($contactPerson);
                    $exhibitor->exhibitorContact()->create([
                        'salutation' => "Mr",
                        'name' => $contactPerson ?? '',
                        'contact_number' => $contactPersonCellPhones[$contactPersonIndex] ?? '',
                    ]);
                }
            }

            $exhibitor->address()->create([
                'address' => $address,
                'pincode' => $pincode,
                'city' => $city ?? null,
                'state' => $state ?? null,
                'country' => $country ?? null,
            ]);

            foreach ($productIds as $productId) {
                $exhibitor->exhibitorProducts()->create([
                    'exhibitor_id' => $exhibitor->id,
                    'product_id' => intval($productId),
                ]);
            }

            $exhibitor->eventExhibitors()->create([
                'event_id' => $currentEvent->id ?? 0,
                'exhibitor_id' => $exhibitor->id,
                'stall_no' => $stallNo,
                'is_sponsorer' => 0,
                'products' => $productIds,
                'is_active' => 1, // set active by default
                'hall_no' => $hallNo,
            ]);

            $insertedExhibitorsCount++;
        }

        session()->flash('success', 'Imported ' . $insertedExhibitorsCount . ' exhibitors');
        session()->flash('info', 'Updated ' . $updatedExhibitorsCount . ' exhibitors');
        return redirect()->route('import.exhibitors');
    }
}
