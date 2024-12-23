<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;


use Carbon\Carbon;
use App\Models\User;
use App\Models\Event;
use App\Models\Product;
use App\Models\Visitor;
use App\Models\Category;
use App\Models\Exhibitor;
use Illuminate\Support\Arr;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {

        $usersData = $this->getUsersData();

        foreach ($usersData as $userData) {
            User::factory()->create($userData);
        }

    }

    private function getUsersData()
    {
        return [
            [
                'name' => 'Admin',
                'email' => 'admin@gmail.com',
                'emp_no' => 'EMP0001',
                'type' => 'admin',
                'mobile_number' => 9944599441,
                'is_active' => 1,
            ],
            [
                'name' => 'Manager',
                'email' => 'manager@gmail.com',
                'emp_no' => 'EMP0002',
                'type' => 'manager',
                'mobile_number' => 9987754321,
                'is_active' => 1,
                'created_by' => 1,
                'updated_by' => 1,
            ],
        ];
    }

    public function getNatureOfBusiness()
    {
        return [
            'Doctor',
            'Hospital Owner',
            'Hospital Administrator',
            'OT Assistant',
            'Manufacturer',
            'Dealer',
            'Physiyotherapist',
            'Psychiatrist',
            'Government Sector',
        ];
    }

    public function getBusinessType()
    {
        return [
            'Importer / Exportar',
            'Others',
            'Distributors',
            'Esteemed Clients',
            'Veterinarion',
            'Process Engineers',
            'Bio medical Engineer',
            'Doctors',
        ];
    }

    public function getProductType()
    {
        return [
            'Electronic Health Records',
            'Patient Engagement and Portal Software',
            'Revenue Cycle Management (RCM) ',
            'Health Information Exchange (HIE)',
            'Telemedicine and Telehealth Platforms',
            'Surgical Information System',
            'Pharmacy Management System',
            'Laboratory Information System (LIS)',
            'Clinical Decision Support Systems',
            'Picture Archiving and Communication System',
        ];
    }

    public function getProductTag()
    {
        return [
            'EHR Software',
            'Health Records Management',
            'Medical Imaging Software',
            'Patient Information System',
            'Radiology Information System',
            'Digital Imaging Management',
            'Hospital Operations Software',
            'Healthcare Administration',
            'HMIS Solutions',
            'Clinical Decision Support',
            'Medical Decision Support',
            'Evidence-Based Medicine',
            'Clinical Lab Software',
            'Lab Management System',
            'Diagnostic Testing Software',
        ];
    }

    public function getProducts()
    {
        return [
            'Real List',
            'Dial 247',
            'ERP',
            'Microsoft Office Suite',
            'Adobe Creative Cloud',
            'Google Workspace',
            'Slack',
            'Zoom',
            'Salesforce',
            'GitHub',
            'Dropbox',
            'Trello',
            'Jira',
            'Atlassian Confluence',
            'Spotify',
            'Netflix',
        ];
    }

    public function getEvents()
    {
        return [
            'Delhi Medicall 2023',
            'Mumbai Medicall 2023',
            'Kolkata Medicall 2023',
            'Chennai Medicall 2024',
            'Bangalore Medicall 2023',
            'Hyderabad Medicall 2023',
            'Pune Medicall 2024',
            'Ahmedabad Medicall 2023',
            'Jaipur Medicall 2024',
            'Chandigarh Medicall 2023',
        ];
    }

    public function getVisitorBussinessTypes()
    {
        return [
            'Doctor',
            'Hospital Owner',
            'Hospital Administrator',
            'Dealer / Distributor',
            'Manufacturer',
            'Nurse',
            'OT Assistants',
            'Diagnostics & Lab Technicians',
            'Hospital Staffs',
            'Medical Shops',
            'Physiotherapist',
            'Psychiatrist',
            'Dietitian',
            'Purchase Manager',
            'Biomedical Engineer',
            'Logistics',
            'Government Sector',
            'Corporate',
            'IT - Other IT Development Sectors',
            'Hotels',
            'Academician',
            'Student',
            'Other Business',
        ];
    }

    public function getVisitorProductsLookingfor()
    {
        return [
            'Radiation Oncology',
            'Radiography',
            'Pathology',
            'Patient Monitoring',
            'Fluoroscopy',
            'Ultrasound',
            'Ventilation',

        ];
    }
    public function getExhibitorData()
    {

        return [
            [
                'username' => 'Bhuvana02',
                'name' => 'Agile',
                'category_id' => 1,
                'email' => 'bhuvana@gmail.com',
                'password' => Hash::make('password'),
                'mobile_number' => 9987754321,
                'known_source' => 'Google',
                'registration_type' => 'web',
                'created_by' => 1,
                'contact_info' => [
                    'salutation' => 'Ms',
                    'name' => 'Bhuvana',
                    'contact_number' => 9987754321,
                    'designation' => 'Manager',
                ],
                'address' => [
                    'address' => 'Pune',
                    'city' => 'Pune',
                    'state' => 'Maharashtra',
                    'country' => 'India',
                    'pincode' => '411001',
                ],
                'events' => [
                    'products' => ['1', '2'],
                ],

            ],
            [
                'username' => 'Rajesh45',
                'name' => 'LND',
                'category_id' => 1,
                'email' => 'rajesh@gmail.com',
                'password' => Hash::make('password'),
                'mobile_number' => 9987754323,
                'known_source' => 'Google',
                'registration_type' => 'web',
                'created_by' => 1,
                'contact_info' => [
                    'salutation' => 'Mr',
                    'name' => 'Rajesh',
                    'contact_number' => 9987754323,
                    'designation' => 'Doctor',
                ],
                'address' => [
                    'address' => 'Chennai',
                    'city' => 'Chennai',
                    'state' => 'Tamilnadu',
                    'country' => 'India',
                    'pincode' => '60001',
                ],
                'events' => [
                    'products' => ['1', '2'],
                ],
            ],
            [
                'username' => 'Vaibhava1',
                'name' => 'Agile',
                'category_id' => 2,
                'email' => 'vaibhava@gmail.com',
                'password' => Hash::make('password'),
                'mobile_number' => 9987754324,
                'known_source' => 'Google',
                'registration_type' => 'web',
                'created_by' => 1,
                'contact_info' => [
                    'salutation' => 'Ms',
                    'name' => 'Vaibhava',
                    'contact_number' => 9987754324,
                    'designation' => 'Developer',
                ],
                'address' => [
                    'address' => 'Trichy',
                    'city' => 'Trichy',
                    'state' => 'Tamilnadu',
                    'country' => 'India',
                    'pincode' => '60002',
                ],
                'events' => [
                    'products' => ['1', '2', '3'],
                ],


            ],
            [
                'username' => 'Vishal32',
                'name' => 'ZOHO',
                'category_id' => 2,
                'email' => 'vishal@gmail.com',
                'password' => Hash::make('password'),
                'mobile_number' => 9987754325,
                'known_source' => 'Friend',
                'registration_type' => 'web',
                'created_by' => 1,
                'contact_info' => [
                    'salutation' => 'Mr',
                    'name' => 'Vishal',
                    'contact_number' => 9987754325,
                    'designation' => 'Developer',
                ],
                'address' => [
                    'address' => 'Perambalur',
                    'city' => 'Perambalur',
                    'state' => 'Tamilnadu',
                    'country' => 'India',
                    'pincode' => '621114',
                ],
                'events' => [
                    'products' => ['1', '2', '3', '4'],
                ],

            ],
            [
                'username' => 'Lavanya23',
                'name' => 'KMC',
                'category_id' => 3,
                'email' => 'lavanya@gmail.com',
                'password' => Hash::make('password'),
                'mobile_number' => 9987754326,
                'known_source' => 'Friend',
                'registration_type' => 'web',
                'created_by' => 1,
                'contact_info' => [
                    'salutation' => 'Ms',
                    'name' => 'Lavanya',
                    'contact_number' => 9987754326,
                    'designation' => 'Manager',
                ],
                'address' => [
                    'address' => 'Chennai',
                    'city' => 'Chennai',
                    'state' => 'Tamilnadu',
                    'country' => 'India',
                    'pincode' => '60001',
                ],
                'events' => [
                    'products' => ['1', '2', '3', '4'],
                ],


            ],
            [
                'username' => 'Arjun',
                'name' => 'Agile',
                'category_id' => 3,
                'email' => 'arjun@gmail.com',
                'password' => Hash::make('password'),
                'mobile_number' => 9987754327,
                'known_source' => 'Friend',
                'registration_type' => 'web',
                'created_by' => 1,
                'contact_info' => [
                    'salutation' => 'Mr',
                    'name' => 'Arjun',
                    'contact_number' => 9987754377,
                    'designation' => 'Doctor',
                ],
                'address' => [
                    'address' => 'Trichy',
                    'city' => 'Trchy',
                    'state' => 'Tamilnadu',
                    'country' => 'India',
                    'pincode' => '60001',
                ],
                'events' => [
                    'products' => ['1', '2', '3', '4'],
                ],


            ]

        ];
    }


    private function getVisitorsData()
    {
        return [
            [
                'username' => 'Abinaya123',
                'password' => Hash::make("password"),
                'salutation' => 'Ms',
                'name' => 'Abinaya',
                'mobile_number' => 6398701234,
                'email' => 'abinaya@gmail.com',
                'category_id' => 1,
                'organization' => 'Healthy Doctor',
                'designation' => 'Patient Care Technician',
                'known_source' => 'Bus Panel',
                'reason_for_visit' => 'Stay updated on new trends ',
                'newsletter' => 1,
                'created_by' => 1,
                'registration_type' => 'web',
                'product_looking' => ["1", "2"],
                'address' => [
                    'country' => 'India',
                    'state' => 'Tamil Nadu',
                    'pincode' => '611114',
                    'address' => 'Ezhil Nagar',
                    'city' => 'Nagapattinam',
                ],
            ],

            [
                'username' => 'Akshaya123',
                'password' => Hash::make("password"),
                'salutation' => 'Ms',
                'name' => 'Abinaya',
                'mobile_number' => 6798701234,
                'email' => 'akshaya@gmail.com',
                'category_id' => 1,
                'organization' => 'Hope hill',
                'designation' => 'Dentist ',
                'known_source' => 'Bus Panel',
                'reason_for_visit' => 'Stay updated on new trends ',
                'newsletter' => 1,
                'created_by' => 1,
                'registration_type' => 'web',
                'product_looking' => ["1", "4"],
                'address' => [
                    'country' => 'India',
                    'state' => 'Tamil Nadu',
                    'pincode' => '611114',
                    'address' => 'Ezhil Nagar',
                    'city' => 'Nagapattinam',
                ],

            ],
            [
                'username' => 'Arjun123',
                'password' => Hash::make("password"),
                'salutation' => 'Mr',
                'name' => 'Arjun',
                'mobile_number' => 6998701234,
                'email' => 'arjun@gmail.com',
                'category_id' => 1,
                'organization' => 'Health House',
                'designation' => 'Patient Care Technician',
                'known_source' => 'Bus Panel',
                'reason_for_visit' => 'Stay updated on new trends ',
                'newsletter' => 1,
                'created_by' => 1,
                'registration_type' => 'web',
                'product_looking' => ["1", "2"],
                'address' => [
                    'country' => 'India',
                    'state' => 'Tamil Nadu',
                    'pincode' => '611114',
                    'address' => 'Ezhil Nagar',
                    'city' => 'Nagapattinam',
                ],
            ],
            [
                'username' => 'Bhavani123',
                'password' => Hash::make("password"),
                'salutation' => 'Ms',
                'name' => 'Bhavani',
                'mobile_number' => 9398701234,
                'email' => 'bhavani@gmail.com',
                'category_id' => 1,
                'organization' => 'The Dreamers',
                'designation' => 'Software Engineer',
                'known_source' => 'Facebook',
                'reason_for_visit' => 'get a good idea ',
                'newsletter' => 1,
                'created_by' => 1,
                'registration_type' => 'web',
                'product_looking' => ["1", "3"],
                'address' => [
                    'country' => 'India',
                    'state' => 'Tamil Nadu',
                    'pincode' => '611114',
                    'address' => 'Ezhil Nagar',
                    'city' => 'Nagapattinam',
                ],
            ],
            [
                'username' => 'Bharathi123',
                'password' => Hash::make("password"),
                'salutation' => 'Ms',
                'name' => 'Bharathi',
                'mobile_number' => 6398701234,
                'email' => 'barathi@gmail.com',
                'category_id' => 1,
                'organization' => 'Silverline',
                'designation' => 'Software Developer',
                'known_source' => 'Bus Panel',
                'reason_for_visit' => 'Stay updated on new trends ',
                'newsletter' => 1,
                'created_by' => 1,
                'registration_type' => 'web',
                'product_looking' => ["1", "2"],
                'address' => [
                    'country' => 'India',
                    'state' => 'Tamil Nadu',
                    'pincode' => '611114',
                    'address' => 'Ezhil Nagar',
                    'city' => 'Nagapattinam',
                ],
            ],
            [
                'username' => 'Gowri123',
                'password' => Hash::make("password"),
                'salutation' => 'Ms',
                'name' => 'Gowri',
                'mobile_number' => 8318701234,
                'email' => 'gowri@gmail.com',
                'category_id' => 1,
                'organization' => 'Healthy Doctor',
                'designation' => 'Patient Care Technician',
                'known_source' => 'Newspaper Ad',
                'reason_for_visit' => 'Stay updated on new trends ',
                'newsletter' => 1,
                'proof_type' => 'PAN',
                'created_by' => 1,
                'registration_type' => 'web',
                'product_looking' => ["1", "2"],
                'address' => [
                    'country' => 'India',
                    'state' => 'Tamil Nadu',
                    'pincode' => '611114',
                    'address' => 'Ezhil Nagar',
                    'city' => 'Nagapattinam',
                ],
            ],
            [
                'username' => 'Anupriy123',
                'password' => Hash::make("password"),
                'salutation' => 'Ms',
                'name' => 'Anupriya',
                'mobile_number' => 8498701234,
                'email' => 'anu@gmail.com',
                'category_id' => 1,
                'organization' => 'Castle Health Care',
                'designation' => 'Patient Care Technician',
                'known_source' => 'Bus Panel',
                'reason_for_visit' => 'Stay updated on new trends ',
                'newsletter' => 1,
                'proof_type' => 'Aadhar',
                'created_by' => 1,
                'registration_type' => 'web',
                'product_looking' => ["1", "3"],
                'address' => [
                    'country' => 'India',
                    'state' => 'Tamil Nadu',
                    'pincode' => '620014',
                    'address' => 'Ezhil Nagar',
                    'city' => 'Tiruchirappalli',
                ],
            ],
            [
                'username' => 'Akil123',
                'password' => Hash::make("password"),
                'salutation' => 'Mr',
                'name' => 'Akil',
                'mobile_number' => 6898701234,
                'email' => 'akil@gmail.com',
                'category_id' => 1,
                'organization' => 'Healthy Doctor',
                'designation' => 'Patient Care Technician',
                'known_source' => 'Newspaper Ad',
                'reason_for_visit' => 'Stay updated on new trends ',
                'newsletter' => 1,
                'proof_type' => 'PAN',
                'created_by' => 1,
                'registration_type' => 'web',
                'product_looking' => ["1", "2"],
                'address' => [
                    'country' => 'India',
                    'state' => 'Tamil Nadu',
                    'pincode' => '620014',
                    'address' => 'Ezhil Nagar',
                    'city' => 'Tiruchirappalli',
                ],
            ],
            [
                'username' => 'Kumar123',
                'password' => Hash::make("password"),
                'salutation' => 'Mr',
                'name' => 'Kumar',
                'mobile_number' => 6998701234,
                'email' => 'kumar@gmail.com',
                'category_id' => 1,
                'organization' => 'Castle Health Care',
                'designation' => 'Patient Care Technician',
                'known_source' => 'Newspaper Ad',
                'reason_for_visit' => 'Stay updated on new trends ',
                'newsletter' => 1,
                'proof_type' => 'PAN',
                'created_by' => 1,
                'registration_type' => 'web',
                'product_looking' => ["1"],
                'address' => [
                    'country' => 'India',
                    'state' => 'Tamil Nadu',
                    'pincode' => '620014',
                    'address' => 'kumaresapuram',
                    'city' => 'Tiruchirappalli',
                ],
            ],

            [
                'username' => 'Sarath123',
                'password' => Hash::make("password"),
                'salutation' => 'Mr',
                'name' => 'Sarath',
                'mobile_number' => 6498701234,
                'email' => 'sarath@gmail.com',
                'category_id' => 15,
                'organization' => 'Healthy Doctor',
                'designation' => 'Patient Care Technician',
                'known_source' => 'Field Force',
                'reason_for_visit' => 'Stay updated on new trends ',
                'newsletter' => 1,
                'proof_type' => 'PAN',
                'created_by' => 1,
                'registration_type' => 'web',
                'product_looking' => ["4"],
                'address' => [
                    'country' => 'India',
                    'state' => 'Tamil Nadu',
                    'pincode' => '620014',
                    'address' => 'kumaresapuram',
                    'city' => 'Tiruchirappalli',
                ],
            ],
            [
                'username' => 'Saranya123',
                'password' => Hash::make("password"),
                'salutation' => 'Ms',
                'name' => 'Saranya',
                'mobile_number' => 6398700234,
                'email' => 'saranya@gmail.com',
                'category_id' => 16,
                'organization' => 'Silverline',
                'designation' => 'Software Architect',
                'known_source' => 'Bus Panel',
                'reason_for_visit' => 'Stay updated on new trends ',
                'newsletter' => 1,
                'proof_type' => 'PAN',
                'created_by' => 1,
                'registration_type' => 'web',
                'product_looking' => ["3"],
                'address' => [
                    'country' => 'India',
                    'state' => 'Tamil Nadu',
                    'pincode' => '620014',
                    'address' => 'kumaresapuram',
                    'city' => 'Tiruchirappalli',
                ],
            ],
            [
                'username' => 'Vimalda123',
                'password' => Hash::make("password"),
                'salutation' => 'Ms',
                'name' => 'Vimalda',
                'mobile_number' => 8398701234,
                'email' => 'vimalda@gmail.com',
                'category_id' => 1,
                'organization' => 'Meds USA',
                'designation' => 'Patient Care Technician',
                'known_source' => 'Field Force',
                'reason_for_visit' => 'Stay updated on new trends ',
                'newsletter' => 1,
                'proof_type' => 'PAN',
                'created_by' => 1,
                'registration_type' => 'web',
                'product_looking' => ["1"],
                'address' => [
                    'country' => 'India',
                    'state' => 'Tamil Nadu',
                    'pincode' => '620014',
                    'address' => 'Kailsapuram',
                    'city' => 'Tiruchirappalli',
                ],
            ],
            [
                'username' => 'Vimal123',
                'password' => Hash::make("password"),
                'salutation' => 'Mr',
                'name' => 'Vimal',
                'mobile_number' => 7398701234,
                'email' => 'vimal@gmail.com',
                'category_id' => 12,
                'organization' => 'Silverline',
                'designation' => 'Software Architect',
                'known_source' => 'Bus Panel',
                'reason_for_visit' => 'Stay updated on new trends ',
                'newsletter' => 0,
                'proof_type' => 'PAN',
                'created_by' => 1,
                'registration_type' => 'web',
                'product_looking' => ["1", "2"],
                'address' => [
                    'country' => 'India',
                    'state' => 'Tamil Nadu',
                    'pincode' => '620014',
                    'address' => 'Kailsapuram',
                    'city' => 'Tiruchirappalli',
                ],
            ],
            [
                'username' => 'Vinothini1235',
                'password' => Hash::make("password"),
                'salutation' => 'Ms',
                'name' => 'Vinothini',
                'mobile_number' => 6398731234,
                'email' => 'vinothini@gmail.com',
                'category_id' => 12,
                'organization' => 'Meds USA',
                'designation' => 'Patient Care Technician',
                'known_source' => 'Field Force',
                'reason_for_visit' => 'Stay updated on new trends ',
                'newsletter' => 0,
                'proof_type' => 'PAN',
                'created_by' => 1,
                'registration_type' => 'web',
                'product_looking' => ["1"],
                'address' => [
                    'country' => 'India',
                    'state' => 'Tamil Nadu',
                    'pincode' => '620014',
                    'address' => 'Kailsapuram',
                    'city' => 'Tiruchirappalli',
                ],

            ],
        ];
    }
}
