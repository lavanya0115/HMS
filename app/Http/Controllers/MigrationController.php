<?php

namespace App\Http\Controllers;

use App\Jobs\SendWelcomeNotificationThorughWhatsappBotJob;
use App\Models\Address;
use App\Models\Appointment;
use App\Models\Category;
use App\Models\EventExhibitor;
use App\Models\EventSeminarParticipant;
use App\Models\EventVisitor;
use App\Models\Exhibitor;
use App\Models\ExhibitorContact;
use App\Models\ExhibitorProduct;
use App\Models\Product;
use App\Models\User;
use App\Models\Visitor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class MigrationController extends Controller
{

    public function export10TRecords()
    {
        $currentEvent = getCurrentEvent();
        $visitors = Visitor::where('registration_type', '10t')
            ->whereHas('eventVisitors', function ($query) use ($currentEvent) {
                $query->where('event_id', $currentEvent->id);
            })->get();

        $filename = '10t-visitors-' . date('Y-m-d') . '.csv';
        $file = fopen(public_path('/assets/' . $filename), 'w');

        $header = [
            'Reg_Id',
            'title',
            'name',
            'number',
            'email',
            'company',
            'designation',
            'city',
        ];

        fputcsv($file, $header);
        $count = 0;
        foreach ($visitors as $visitor) {
            $eventInfo = $visitor->eventVisitors()->where('event_id', $currentEvent->id)->first();

            $data = [
                $eventInfo->_meta['reference_no'] ?? '',
                $visitor->salutation,
                $visitor->name,
                $visitor->mobile_number,
                $visitor->email,
                $visitor->organization,
                $visitor->designation,
                $visitor->address->city ?? '',
            ];
            fputcsv($file, $data);
            $count++;
        }
        fclose($file);

        return '10t visitors exported successfully. - ' . $count . ' records exported.';
    }

    public function setRegistrationTypeForCurrentEvent()
    {
        $currentEvent = getCurrentEvent();

        $eventVisitors = EventVisitor::where('event_id', $currentEvent->id)->get();
        foreach ($eventVisitors as $eventVisitor) {
            $visitor = Visitor::find($eventVisitor->visitor_id);
            if ($visitor) {
                $eventVisitor->registration_type = $visitor->registration_type;
                $eventVisitor->save();
            }
        }

        return 'Registration Type migrated successfully';
    }
    public function removeExhibitors()
    {
        $currentEvent = getCurrentEvent();
        $eventExhibitors = EventExhibitor::where('event_id', $currentEvent->id)->get();
        $deletedCount = 0;
        foreach ($eventExhibitors as $eventExhibitor) {
            //
            $participatedWithOtherEvent = EventExhibitor::where('exhibitor_id', $eventExhibitor->exhibitor_id)
                ->where('event_id', '!=', $currentEvent->id)
                ->first();

            if (!$participatedWithOtherEvent) {
                echo "Exhibitor doesnt participated in other event. <br>";
                echo 'Exhibitor ID: ' . $eventExhibitor->exhibitor_id . ' - Email: ' . $eventExhibitor->exhibitor->email . ' - Name: ' . $eventExhibitor->exhibitor->name . ' - Stall No: ' . $eventExhibitor->stall_no . ' - Deleted <br>';
                $exhibitor = Exhibitor::withTrashed()->find($eventExhibitor->exhibitor_id);
                if ($exhibitor) {
                    ExhibitorProduct::where('exhibitor_id', $exhibitor->id)->delete();
                    ExhibitorContact::where('exhibitor_id', $exhibitor->id)->delete();
                    Address::where('addressable_id', $exhibitor->id)->where('addressable_type', 'App\Models\Exhibitor')->delete();
                    $exhibitor->delete();
                }
            }

            $result = $eventExhibitor->forceDelete();

            if ($result) {
                $deletedCount++;
            }
        }

        echo "Total $deletedCount exhibitors removed successfully for the current event.";

        $deletedCount = 0;
        // remove exhibitors who are not participated in any event
        $exhibitors = Exhibitor::doesntHave('eventExhibitors')->withTrashed()->get();
        foreach ($exhibitors as $exhibitor) {
            echo 'Exhibitor ID: ' . $exhibitor->id . ' - Email: ' . $exhibitor->email . ' - Name: ' . $exhibitor->name . ' - Deleted <br>';
            ExhibitorProduct::where('exhibitor_id', $exhibitor->id)->delete();
            ExhibitorContact::where('exhibitor_id', $exhibitor->id)->delete();
            Address::where('addressable_id', $exhibitor->id)->where('addressable_type', 'App\Models\Exhibitor')->delete();
            Appointment::where('exhibitor_id', $exhibitor->id)->forceDelete();
            $exhibitor->forceDelete();
            $deletedCount++;
        }

        return $deletedCount . ' - All exhibitors removed successfully.';
    }
    public function sendVisitorsWelcomeNotification()
    {
        $visitors = Visitor::get();

        foreach ($visitors as $visitorIndex => $visitor) {
            dispatch(new SendWelcomeNotificationThorughWhatsappBotJob($visitor))
                ->onQueue('visitor_welcome_notification')
                ->delay(now()->addSeconds(10));
            echo ($visitorIndex + 1) . " . sent to $visitor->name <br>";
        }
        // HOw to trigger the queue
        echo 'All visitors welcome notification dispatched to queue.';
        echo '<br>';
        echo 'Now run the queue worker to send the welcome notification to the visitors.';
        echo '<br>';
    }

    public function updateEventProductsInStringFormat()
    {
        $eventExhibitors = EventExhibitor::get();

        foreach ($eventExhibitors as $eventExhibitor) {

            $productIds = $eventExhibitor->products;

            $productIds = array_map(function ($productId) {
                return strval($productId);
            }, $productIds);

            $eventExhibitor->products = $productIds;
            $eventExhibitor->save();
        }

        echo 'All event exhibitors products updated to string format.';
    }

    public function updateDesignationFieldIn10TVisitorsTable()
    {
        $data = readCSV(public_path('/assets/10t-visitors-dec-23.csv'));

        $updatedCount = 0;
        foreach ($data as $visitorInfo) {
            $mobileNumber = $visitorInfo['Phone'] ?? '';
            $designation = $visitorInfo['Designation'] ?? '';

            $visitor = Visitor::where('mobile_number', $mobileNumber)->first();

            if ($visitor) {
                $visitor->designation = $designation;
                $visitor->save();
                $updatedCount++;
            }
        }

        echo 'Total ' . $updatedCount . ' visitors designation updated.<br>';
    }

    public function registeringVisitorsWhoMakesAppointmentsWithoutRegister()
    {
        $eventId = 12;

        $visitorIds = Appointment::select('visitor_id')->where('event_id', $eventId)
            ->where('visitor_id', '>', 0)
            ->groupBy('visitor_id')
            ->pluck('visitor_id')
            ->toArray();

        foreach ($visitorIds as $visitorId) {
            $exitsInEvent = EventVisitor::where('event_id', $eventId)
                ->where('visitor_id', $visitorId)
                ->first();

            if ($exitsInEvent) {
                continue;
            }

            $visitor = Visitor::find($visitorId);
            if ($visitor) {
                $eventVisitor = new EventVisitor();
                $eventVisitor->event_id = $eventId;
                $eventVisitor->visitor_id = $visitorId;
                $eventVisitor->is_visited = 1;
                $eventVisitor->save();
            }
        }
        return 'All visitors registered successfully.';
    }

    public function updateExhibitorWebsiteUrl()
    {
        $exhibitors = Exhibitor::where('website', '!=', null)->get();
        foreach ($exhibitors as $exhibitor) {
            $meta = $exhibitor->_meta ?? null;
            if (isset($exhibitor->website) && empty($meta['website_url'])) {
                $meta['website_url'] = $exhibitor->website;
                $exhibitor->_meta = $meta;
                $exhibitor->save();
            }
        }
        return 'All exhibitors website url updated successfully.';
    }

    public function importVisitorsFromGivenFile(Request $request)
    {
        $filename = $request->filename ?? '';
        $source = $request->source ?? 'medicall';
        $isVisited = $request->is_visited ?? 'no';
        $isVisited = $isVisited == 'yes' ? 1 : 0;
        $eventname = $request->eventname ?? '';
        $batch = $request->batch ?? 1;
        $limit = $request->limit ?? 300;

        if (empty($filename)) {
            return 'Please provide the file name.';
        }

        $data = readCSV(public_path('/assets/' . $filename));

        $totalVisitors = count($data);
        $insertedVisitorsCount = 0;
        $alreadyRegisteredCount = 0;
        $notInsertedVisitorsCount = 0;
        $registeredVisitorsCount = 0;
        $missingMobileNumbersCount = 0;

        $currentEventInfo = getCurrentEvent();
        if (!empty($eventname)) {
            $currentEventInfo = getEventByName($eventname);
        }

        if (!$currentEventInfo) {
            return 'Event not found';
        }

        $batchData = array_slice($data, ($batch - 1) * $limit, $limit);

        echo "Batch Starting From " . ($batch - 1) * $limit . " to " . ($batch * $limit) . "<br>";

        foreach ($batchData as $visitorInfo) {

            $source = $visitorInfo['source'] ?? $source;

            $visitorFormattedData = [
                'salutation' => $visitorInfo['title'] ?? 'Mr',
                'name' => $visitorInfo['name'] ?? '',
                'email' => $visitorInfo['email'] ?? '',
                'mobile' => $visitorInfo['mobile'] ?? '',
                'designation' => $visitorInfo['designation'] ?? '',
                'organization' => $visitorInfo['organization'] ?? '',
                'address' => $visitorInfo['address'] ?? '',
                'city' => $visitorInfo['city'] ?? '',
                'cityname' => $visitorInfo['cityname'] ?? '',
                'state' => $visitorInfo['state'] ?? '',
                'country' => $visitorInfo['country'] ?? '',
                'pincode' => $visitorInfo['post_code'] ?? '',
                'source' => $source,
                'nature_of_business' => $visitorInfo['businesstype'] ?? '',
                'reason_for_visit' => $visitorInfo['products_interested'] ?? '',
                'known_source' => $visitorInfo['knowingname'] ?? '',
            ];

            if (empty($visitorFormattedData['mobile'])) {
                $missingMobileNumbersCount++;
                continue;
            }

            $addVisitor = $this->addVisitor($visitorFormattedData, $currentEventInfo, $isVisited);

            if ($addVisitor['status'] == 'success') {
                if ($addVisitor['type'] == "VISITOR_REGISTERED") {
                    $insertedVisitorsCount++;
                } else if ($addVisitor['type'] == 'VISITOR_ALREADY_EXISTS_AND_REGISTERED_FOR_EVENT') {
                    $registeredVisitorsCount++;
                } else if ($addVisitor['type'] == 'VISITOR_ALREADY_REGISTERED_FOR_EVENT') {
                    $alreadyRegisteredCount++;
                }
            } else {
                $notInsertedVisitorsCount++;
            }
        }

        return 'Total ' . $totalVisitors . ' visitors found. <br> ' . $insertedVisitorsCount . ' visitors inserted. <br> ' . $alreadyRegisteredCount . ' visitors already registered. <br> ' . $notInsertedVisitorsCount . ' visitors not inserted. <br> ' . $registeredVisitorsCount . ' visitors are registered for the event. <br> ' . $missingMobileNumbersCount . ' - Missing Mobile Numbers Count';
    }

    public function addVisitor($visitorData, $currentEvent, $isVisited = 0)
    {
        $visitor = Visitor::where('mobile_number', $visitorData['mobile'])->first();

        if ($visitor) {

            $eventVisitor = $visitor->eventVisitors()->where('event_id', $currentEvent->id ?? 0)->first();
            if ($eventVisitor) {
                return [
                    'status' => 'success',
                    'type' => 'VISITOR_ALREADY_REGISTERED_FOR_EVENT',
                    'message' => 'Visitor already registered for the event',
                ];
            }

            $visitor->eventVisitors()->create([
                'event_id' => $currentEvent->id ?? 0,
                'is_visited' => $isVisited,
            ]);

            return [
                'status' => 'success',
                'type' => 'VISITOR_ALREADY_EXISTS_AND_REGISTERED_FOR_EVENT',
                'message' => 'Visitor already exists with mobile number ' . $visitorData['mobile'],
            ];
        }

        $username = getUniqueUsernameFromGivenName($visitorData['name']);
        $defaultPassword = config('app.default_user_password');

        $natureOfBusinessId = null;
        $natureOfBusiness = $visitorData['nature_of_business'] ?? '';
        if (!empty($natureOfBusiness)) {
            $category = Category::where('type', 'visitor_business_type')
                ->where('name', 'like', '%' . $natureOfBusiness . '%')
                ->first();

            if (!$category) {
                $category = Category::create([
                    'name' => $natureOfBusiness,
                    'type' => 'visitor_business_type',
                    'is_active' => 1,
                ]);
            }
            $natureOfBusinessId = $category->id ?? null;
        }

        $visitor = new Visitor();
        $visitor->username = $username;
        $visitor->password = Hash::make($defaultPassword);
        $visitor->salutation = $visitorData['salutation'];
        $visitor->name = $visitorData['name'];
        $visitor->mobile_number = $visitorData['mobile'];
        $visitor->email = $visitorData['email'];
        $visitor->organization = $visitorData['organization'];
        $visitor->designation = $visitorData['designation'];
        $visitor->registration_type = $visitorData['source'] ?? 'medicall';
        $visitor->category_id = $natureOfBusinessId ?? null;
        $visitor->reason_for_visit = $visitorData['reason_for_visit'];
        $visitor->known_source = $visitorData['known_source'];
        $visitor->save();

        if (!$visitor) {
            return [
                'status' => 'error',
                'type' => 'VISITOR_NOT_REGISTERED',
                'message' => 'Visitor not registered',
            ];
        }

        $visitor->address()->create([
            'address' => $visitorData['address'] ?? '',
            'pincode' => $visitorData['pincode'] ?? '',
            'city' => $visitorData['city'] ?? '',
            'state' => $visitorData['state'] ?? '',
            'country' => $visitorData['country'] ?? '',
        ]);

        $visitor->eventVisitors()->create([
            'event_id' => $currentEvent->id ?? 0,
            'is_visited' => $isVisited,
        ]);

        return [
            'status' => 'success',
            'type' => 'VISITOR_REGISTERED',
            'message' => 'Visitor registered successfully',
        ];
    }

    public function import10TData(Request $request)
    {
        $filename = $request->filename ?? '';
        if (empty($filename)) {
            return 'Please provide the file name.';
        }

        $data = readCSV(asset('/assets/' . $filename));
        $totalVisitors = count($data);
        $insertedVisitorsCount = 0;
        $alreadyRegisteredCount = 0;
        $notInsertedVisitorsCount = 0;
        $registeredVisitorsCount = 0;

        $currentEventInfo = getCurrentEvent();

        foreach ($data as $visitorInfo) {
            $salutation = $visitorInfo['Salutation'] ?? 'Mr';

            $visitorFormattedData = [
                'salutation' => !empty($salutation) ? $salutation : 'Mr',
                'name' => $visitorInfo['Name'] ?? '',
                'email' => $visitorInfo['Email'] ?? '',
                'mobile' => $visitorInfo['Phone'] ?? '',
                'designation' => $visitorInfo['Designation'] ?? '',
                'organization' => $visitorInfo['Company'] ?? '',
                'address' => $visitorInfo['address'] ?? '',
                'city' => $visitorInfo['City'] ?? '',
                'cityname' => $visitorInfo['cityname'] ?? '',
                'state' => $visitorInfo['state'] ?? '',
                'country' => $visitorInfo['Country'] ?? '',
                'pincode' => $visitorInfo['post_code'] ?? '',
                'source' => '10t',
                'nature_of_business' => $visitorInfo['businesstype'] ?? '',
                'reason_for_visit' => $visitorInfo['Which topics or products are you eager to explore?'] ?? '',
                'known_source' => $visitorInfo['knowingname'] ?? '',
            ];

            $addVisitor = $this->addVisitor($visitorFormattedData, $currentEventInfo);

            if ($addVisitor['status'] == 'success') {
                if ($addVisitor['type'] == "VISITOR_REGISTERED") {
                    $insertedVisitorsCount++;
                } else if ($addVisitor['type'] == 'VISITOR_ALREADY_EXISTS_AND_REGISTERED_FOR_EVENT') {
                    $registeredVisitorsCount++;
                } else if ($addVisitor['type'] == 'VISITOR_ALREADY_REGISTERED_FOR_EVENT') {
                    $alreadyRegisteredCount++;
                }
            } else {
                $notInsertedVisitorsCount++;
            }
        }

        return 'Total ' . $totalVisitors . ' visitors found. <br> ' . $insertedVisitorsCount . ' visitors inserted. <br> ' . $alreadyRegisteredCount . ' visitors already registered. <br> ' . $notInsertedVisitorsCount . ' visitors not inserted. <br> ' . $registeredVisitorsCount . ' visitors are registered for the event.';
    }

    public function deleteNotExistExhibitorProducts()
    {
        $exhibitors = Exhibitor::all();
        $deletedCount = 0;

        foreach ($exhibitors as $exhibitor) {
            if ($exhibitor->exhibitorProducts) {
                foreach ($exhibitor->exhibitorProducts as $exhibitorProduct) {
                    $product = Product::find($exhibitorProduct->product_id);
                    if (!$product) {
                        $exhibitorProduct->delete();
                        $deletedCount++;
                    }
                }
            }
        }

        if ($deletedCount > 0) {
            return "$deletedCount products deleted successfully";
        } else {
            return "No products deleted";
        }
    }

    public function setVisitedAtValues()
    {
        $eventVisitors = EventVisitor::where('is_visited', 1)
            ->whereNull('visited_at')
            ->get();
        $updatedCount = 0;
        foreach ($eventVisitors as $eventVisitor) {
            $eventVisitor->visited_at = $eventVisitor->updated_at;
            $eventVisitor->save();
            $updatedCount++;
        }
        return 'Total ' . $updatedCount . ' visitors visited at values updated.';
    }

    public function migrateRegistrationTypes()
    {
        $visitors = Visitor::all();

        foreach ($visitors as $visitor) {

            foreach ($visitor->eventVisitors as $eventvisitor) {

                $eventvisitor->registration_type = $visitor->registration_type;

                $eventvisitor->save();
            }
        }
        return 'Registration Type migrated successfully';
    }

    public function migrateKnownSource()
    {
        $visitors = Visitor::all();
        foreach ($visitors as $visitor) {
            foreach ($visitor->eventVisitors as $eventVisitor) {
                $eventVisitor->known_source = $visitor->known_source;
                $eventVisitor->save();
            }
        }

        return 'Known sources migrated successfully';
    }

    public function updateRoleForExistingUsers()
    {
        $usersCollection = User::get();
        $users = $usersCollection->where('type', 'user');
        $admins = $usersCollection->where('type', 'admin');
        $superAdmins = $usersCollection->where('type', 'super_admin');
        $salesPersons = $usersCollection->where('type', 'sales_person');
        if ($users) {
            foreach ($users as $user) {
                $user->assignRole('User');
            }
        }
        if ($admins) {
            foreach ($admins as $admin) {
                $admin->assignRole('Admin');
            }
        }
        if ($superAdmins) {
            foreach ($superAdmins as $superAdmin) {
                $superAdmin->assignRole('Super Admin');
            }
        }
        if ($salesPersons) {
            foreach ($salesPersons as $salesPerson) {
                $salesPerson->assignRole('Sales Person');
            }
        }
        return 'Role updated successfully.';
    }

    public function createNewPermissions()
    {
        $permissions = [
            [
                'name' => 'Reports',
                'category_name' => 'Insights',
            ],
            [
                'name' => 'Event Dashboard',
                'category_name' => 'Insights',
            ],

            [
                'name' => 'Email Templates Summary',
                'category_name' => 'Email Templates',

            ],

            [
                'name' => 'Password Reset',
                'category_name' => 'Exhibitor',

            ],

            [
                'name' => 'Update Payment Status',
                'category_name' => 'Delegate',
            ],

            [
                'name' => 'View Stall',
                'category_name' => 'Stall',
            ],
            [
                'name' => 'Create Stall',
                'category_name' => 'Stall',
            ],
            [
                'name' => 'Delete Stall',
                'category_name' => 'Stall',
            ],
            [
                'name' => 'Update Stall',
                'category_name' => 'Stall',
            ],
        ];

        foreach ($permissions as $permission) {
            $isPermissionExist = Permission::where('name', $permission['name'])->first();

            if (!$isPermissionExist) {
                Permission::create([
                    'name' => $permission['name'],
                    'category_name' => $permission['category_name'],
                ]);
            }
        }
        return 'Permissions created successfully';
    }
    public function deletePermissions()
    {
        $permissions = [

            [
                'name' => 'Import Tele-Calling Visitors',
                'category_name' => 'Tele Calling',
            ],
            [
                'name' => 'Tele-caller List',
                'category_name' => 'Tele Calling',
            ],
            [
                'name' => 'Make Call',
                'category_name' => 'Tele Calling',
            ],
            [
                'name' => 'Register for Current Event',
                'category_name' => 'Tele Calling',
            ],

        ];

        foreach ($permissions as $permission) {
            $isPermissionExist = Permission::where('name', $permission['name'])->first();

            if (!$isPermissionExist) {
                continue;
            }

            $roles = Role::all();

            foreach ($roles as $role) {
                if ($role->hasPermissionTo($permission['name'])) {
                    $role->revokePermissionTo($permission['name']);
                }
            }

            $isPermissionExist->delete();
        }

        return 'Permissions deleted successfully';
    }

    public function createRoleAndAssignPermissions()
    {
        $superAdmin = Role::create([
            'name' => 'Super Admin',
            'is_active' => 1,
        ]);
        $enterpriseAdmin = Role::create([
            'name' => 'Enterprise Admin',
            'is_active' => 1,
        ]);
        $admin = Role::create([
            'name' => 'Admin',
            'is_active' => 1,
        ]);
        $user = Role::create([
            'name' => 'User',
            'is_active' => 1,
        ]);
        $salesPerson = Role::create([
            'name' => 'Sales Person',
            'is_active' => 1,
        ]);
        $allPermissions = Permission::pluck('name');
        $superAdmin->syncPermissions($allPermissions);
        $enterpriseAdmin->syncPermissions($allPermissions);
        $admin->syncPermissions($allPermissions);
        $user->syncPermissions($allPermissions);

        $salesPerson->givePermissionTo([
            'View Exhibitor',
            'Export Exhibitor',
            'View Visitor',
            'Export Visitor',
            'Create Appointment',
            'View Appointment',
            'Export Appointment',
            'Create Category',
            'View Category',
            'Create Product',
            'View Product',
            'View Previous Event',

        ]);

        return 'Roles created and permissions assigned successfully';
    }

    public function listDeletedExhibitorsContact()
    {
        $exhibitorContact = ExhibitorContact::doesntHave('exhibitor')->pluck('name', 'id');
        echo $exhibitorContact;
    }

    public function updateDelegatePaymentStatus(Request $request)
    {
        $event = getCurrentEvent();
        $eventId = $event->id;
        $delegates = EventSeminarParticipant::where('event_id', $eventId)
            ->whereNull('payment_status')->orWhere('payment_status', '=', '')->get();
        foreach ($delegates as $delegate) {
            $delegate->payment_status = "paid";
            $delegate->save();
        }
        return 'Payment status updated successfully';
    }
}