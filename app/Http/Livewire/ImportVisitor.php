<?php

namespace App\Http\Livewire;

use App\Models\User;
use App\Models\Visitor;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\On;
use Livewire\Component;

class ImportVisitor extends Component
{
    public function mount()
    {
        try {
            $telecallingUsers = User::role('Tele Calling User')->get();
        } catch (\Exception $e) {
            logger()->error('Error in mount method: ' . $e->getMessage());
        }
    }
    #[On('importingData')]
    public function importVisitors($data)
    {
        try {
            if (count($data) <= 1) {
                session()->flash('err_message', 'No data to import');
                return;
            }

            $insertedVisitorsCount = 0;
            $currentEvent = getCurrentEvent();

            $telecallingUsers = User::role('Tele Calling User')->get();
            $visitorChunks = collect(array_slice($data, 1))->split($telecallingUsers->count());

            foreach ($telecallingUsers as $callerIndex => $telecaller) {
                $visitorsForTelecaller = $visitorChunks[$callerIndex] ?? collect();

                foreach ($visitorsForTelecaller as $visitorInfo) {
                    $salutation = $visitorInfo[2] ?? '';
                    $salutation = (strtolower($salutation) == 'manager') ? 'Mr' : $salutation;
                    $name = $visitorInfo[3] ?? '';
                    $mobileNumber = $visitorInfo[4] ?? '';
                    $email = $visitorInfo[5] ?? '';
                    $organization = $visitorInfo[6] ?? '';
                    $designation = $visitorInfo[7] ?? '';
                    $source = $visitorInfo[9] ?? '';
                    $city = $visitorInfo[8] ?? '';

                    $visitor = Visitor::where('mobile_number', $mobileNumber)
                        ->orWhere('email', $email)
                        ->first();

                    if (!$visitor) {

                        $defaultPassword = config('app.default_user_password');
                        $username = getUniqueUsernameFromGivenName($name) . rand(9999, 9999999);

                        $visitorData = [
                            'username' => $username,
                            'salutation' => $salutation,
                            'name' => $name,
                            'mobile_number' => $mobileNumber,
                            'email' => $email,
                            'password' => Hash::make($defaultPassword),
                            'organization' => $organization,
                            'designation' => $designation,
                            'registration_type' => $source,

                        ];

                        $visitor = Visitor::create($visitorData);

                        if ($visitor) {
                            $visitor->address()->create([
                                'city' => $city,
                            ]);

                            $visitor->telecalling_user_id = $telecaller->id;
                            $visitor->save();
                            $insertedVisitorsCount++;
                        }
                    }
                }
            }

            session()->flash('success', 'Imported ' . $insertedVisitorsCount . ' visitors');
            return redirect()->route('telecalling.import-visitors');
        } catch (\Exception $e) {
            logger()->error('Error in importVisitors method: ' . $e->getMessage());
            session()->flash('error', 'An error occurred while importing visitors: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.import-visitor')->layout('layouts.admin');
    }
}
