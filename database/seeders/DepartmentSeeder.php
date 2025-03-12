<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $hotelDepartments = [
            'Front Office',
            'Housekeeping',
            'Food and Beverage',
            'Kitchen',
            'Maintenance',
            'Sales and Marketing',
            'Accounting',
            'Human Resources',
            'Security',
            'Concierge',
            'Spa and Wellness',
            'Events and Banquets',
            'Engineering',

        ];
        foreach ($hotelDepartments as $department) {
            Department::create(['name' => $department]);
        }
    }
}