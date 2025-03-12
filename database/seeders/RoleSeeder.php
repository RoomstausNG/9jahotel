<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = ["super-admin", "admin", "back-office", "marketer", "manager", 'frontdesk', 'frontdesk-manager', "sales-staff", "sales-manager"];
        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role]);
        }
    }
}