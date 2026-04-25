<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Departments
        $departments = [
            'Marcom (Marketing Communication)',
            'Sales',
            'Aftersales',
            'Finance',
            'Sparepart',
            'Purchasing',
            'HR & General Affair',
            'IT',
            'Management',
        ];

        foreach ($departments as $name) {
            Department::firstOrCreate(['name' => $name]);
        }

        $marcomDept = Department::where('name', 'Marcom (Marketing Communication)')->first();
        $mgmtDept   = Department::where('name', 'Management')->first();
        $salesDept  = Department::where('name', 'Sales')->first();

        // Default User (Edit this as needed)
        User::firstOrCreate(
            ['email' => 'admin@hartonogroup.com'], // Identity key
            [
                'name'          => 'Administrator',
                'username'      => 'admin',
                'email'         => 'admin@hartonogroup.com',
                'password'      => Hash::make('admin1234'),
                'role'          => 'admin', // Options: admin, marcom, manager, staff
                'department_id' => 1
            ]
        );

        // Seed department heads and key users
        // $this->call(DepartmentUserSeeder::class);
    }
}
