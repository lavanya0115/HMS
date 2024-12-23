<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            [
                'name' => 'Create Exhibitor',
                'category_name' => 'Exhibitor',
            ],
            [
                'name' => 'Update Exhibitor',
                'category_name' => 'Exhibitor',
            ],
            [
                'name' => 'View Exhibitor',
                'category_name' => 'Exhibitor',
            ],
            [
                'name' => 'Export Exhibitor',
                'category_name' => 'Exhibitor',
            ],
            [
                'name' => 'Transfer Exhibitor',
                'category_name' => 'Exhibitor'
            ],
            [
                'name' => 'Import Exhibitor Data',
                'category_name' => 'Exhibitor'
            ],
            [
                'name' => 'Create Visitor',
                'category_name' => 'Visitor',
            ],
            [
                'name' => 'Update Visitor',
                'category_name' => 'Visitor',
            ],
            [
                'name' => 'View Visitor',
                'category_name' => 'Visitor',
            ],
            [
                'name' => 'Export Visitor',
                'category_name' => 'Visitor',
            ],
            [
                'name' => 'Transfer Visitor',
                'category_name' => 'Visitor'
            ],
            [
                'name' => 'Create Appointment',
                'category_name' => 'Appointment'
            ],
            [
                'name' => 'View Appointment',
                'category_name' => 'Appointment'
            ],
            [
                'name' => 'Export Appointment',
                'category_name' => 'Appointment'
            ],
            [
                'name' => 'View Feedback',
                'category_name' => 'Appointment'
            ],
            [
                'name' => 'Create User',
                'category_name' => 'User'
            ],
            [
                'name' => 'View User',
                'category_name' => 'User'
            ],
            [
                'name' => 'Update User',
                'category_name' => 'User'
            ],
            [
                'name' => 'Reset Password',
                'category_name' => 'User'
            ],
            [
                'name' => 'Create Event',
                'category_name' => 'Event'
            ],
            [
                'name' => 'Update Event',
                'category_name' => 'Event'
            ],
            [
                'name' => 'View Event',
                'category_name' => 'Event'
            ],
            [
                'name' => 'Delete Event',
                'category_name' => 'Event'
            ],
            [
                'name' => 'View Previous Event',
                'category_name' => 'Event'
            ],
            [
                'name' => 'Create Category',
                'category_name' => 'Category'
            ],
            [
                'name' => 'Update Category',
                'category_name' => 'Category'
            ],
            [
                'name' => 'View Category',
                'category_name' => 'Category'
            ],
            [
                'name' => 'Delete Category',
                'category_name' => 'Category'
            ],
            [
                'name' => 'Create Product',
                'category_name' => 'Product'
            ],
            [
                'name' => 'Update Product',
                'category_name' => 'Product'
            ],
            [
                'name' => 'View Product',
                'category_name' => 'Product'
            ],
            [
                'name' => 'Delete Product',
                'category_name' => 'Product'
            ],
            [
                'name' => 'Create Role',
                'category_name' => 'Role'
            ],
            [
                'name' => 'Update Role',
                'category_name' => 'Role'
            ],
            [
                'name' => 'View Role',
                'category_name' => 'Role'
            ],
            [
                'name' => 'Delete Role',
                'category_name' => 'Role'
            ],
            [
                'name' => 'View Permission',
                'category_name' => 'Permission'
            ],
            [
                'name' => 'Assign Permission',
                'category_name' => 'Permission'
            ],
            [
                'name' => 'View Sales Person Mapping',
                'category_name' => 'Sales Person Mapping'
            ],
            [
                'name' => 'Assign Exhibitor',
                'category_name' => 'Sales Person Mapping'
            ]
        ];

        // Looping and Inserting Array's Permissions into Permission Table
        foreach ($permissions as $permission) {
            Permission::create([
                'name' => $permission['name'],
                'category_name' => $permission['category_name'],
            ]);
        }
    }
}
