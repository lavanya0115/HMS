<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $superAdmin = Role::create([
            'name' => 'Super Admin',
            'is_active' => 1
        ]);
        $enterpriseAdmin = Role::create([
            'name' => 'Enterprise Admin',
            'is_active' => 1
        ]);
        $admin = Role::create([
            'name' => 'Admin',
            'is_active' => 1
        ]);
        $user = Role::create([
            'name' => 'User',
            'is_active' => 1
        ]);
        $salesPerson = Role::create([
            'name' => 'Sales Person',
            'is_active' => 1
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
            'View Previous Event'
        ]);
    }
}
