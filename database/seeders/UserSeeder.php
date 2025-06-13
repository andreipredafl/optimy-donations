<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::firstOrCreate([
            'email' => 'admin@acme.com',
        ], [
            'employee_ref' => 'ADMIN001',
            'name' => 'ACME Admin',
            'password' => Hash::make('password'),
            'department' => 'Administration',
            'job_title' => 'System Administrator',
        ]);
        $admin->assignRole('admin');

        $employees = [
            [
                'employee_ref' => 'EMP001',
                'name' => 'Jane Doe',
                'email' => 'jane.doe@acme.com',
                'department' => 'Human Resources',
                'job_title' => 'HR Manager',
            ],
            [
                'employee_ref' => 'EMP002',
                'name' => 'John Smith',
                'email' => 'john.smith@acme.com',
                'department' => 'Engineering',
                'job_title' => 'Software Developer',
            ],
            [
                'employee_ref' => 'EMP003',
                'name' => 'Alice Johnson',
                'email' => 'alice.johnson@acme.com',
                'department' => 'Marketing',
                'job_title' => 'Marketing Specialist',
            ],
            [
                'employee_ref' => 'EMP004',
                'name' => 'Bob Lee',
                'email' => 'bob.lee@acme.com',
                'department' => 'Finance',
                'job_title' => 'Financial Analyst',
            ],
            [
                'employee_ref' => 'EMP005',
                'name' => 'Maria Garcia',
                'email' => 'maria.garcia@acme.com',
                'department' => 'Sales',
                'job_title' => 'Sales Representative',
            ],
            [
                'employee_ref' => 'EMP006',
                'name' => 'David Kim',
                'email' => 'david.kim@acme.com',
                'department' => 'Engineering',
                'job_title' => 'DevOps Engineer',
            ],
            [
                'employee_ref' => 'EMP007',
                'name' => 'Sara Patel',
                'email' => 'sara.patel@acme.com',
                'department' => 'Operations',
                'job_title' => 'Operations Manager',
            ],
            [
                'employee_ref' => 'EMP008',
                'name' => 'Tom Brown',
                'email' => 'tom.brown@acme.com',
                'department' => 'Customer Service',
                'job_title' => 'Customer Support Lead',
            ],
            [
                'employee_ref' => 'EMP009',
                'name' => 'Emma Wilson',
                'email' => 'emma.wilson@acme.com',
                'department' => 'Legal',
                'job_title' => 'Legal Counsel',
            ],
            [
                'employee_ref' => 'EMP010',
                'name' => 'Michael Chen',
                'email' => 'michael.chen@acme.com',
                'department' => 'Product',
                'job_title' => 'Product Manager',
            ],
        ];

        foreach ($employees as $employee) {
            $user = User::firstOrCreate([
                'email' => $employee['email'],
            ], [
                'employee_ref' => $employee['employee_ref'],
                'name' => $employee['name'],
                'password' => Hash::make('password'),
                'department' => $employee['department'],
                'job_title' => $employee['job_title'],
            ]);
            $user->assignRole('employee');
        }
    }
}
