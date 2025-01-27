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
}
