<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        // ["super-admin", "admin", 'frontdesk', 'frontdesk-manager', "sales-staff", "sales-manager"];
        $permissions = [
            "super-admin" => [
                "hotel" => ['c', 'r', 'u', 'd'],
                "configuration" => ['c', 'r', 'u', 'd'],
                "department" => ['c', 'r', 'u', 'd'],
                "user" => ['c', 'r', 'u', 'd'],
                "revenue" => ['c', 'r', 'u', 'd'],
                "admin" => ['c', 'r', 'u', 'd'],
                "role" => ['c', 'r', 'u', 'd'],
                "permission" => ['c', 'r', 'u', 'd'],
                "booking" => ['c', 'r', 'u', 'd', 'a'],
                "payment" => ['c', 'r', 'u', 'd'],
                "finance" => ['c', 'r', 'u', 'd'],
                "room" => ['c', 'r', 'b', 'u', 'd'],
                "hall" => ['c', 'r', 'b', 'u', 'd'],
                "business" => ['c', 'r',  'u', 'd'],
                "customer" => ['c', 'r',  'u', 'd'],
                "expenditure" => ['c', 'r',  'u', 'd'],
                "item" => ['c', 'r',  'u', 'd'],
                "sales" => ['c', 'r',  'u', 'd'],
                "report" => ['r'],
                "reservation" => ['r'],
                'checkout' => ['u']
            ],
            "admin" => [
                "hotel" => ['c', 'r', 'u'],
                "department" => ['c', 'r', 'u', 'd'],
                "configuration" => ['c', 'r', 'u', 'd'],
                "user" => ['c', 'r', 'u', 'd'],
                "revenue" => ['c', 'r', 'u', 'd'],
                "item" => ['c', 'r',  'u', 'd'],
                "role" => ['r',],
                "permission" => ['r'],
                "booking" => ['c', 'r', 'u', 'd', 'a'],
                "payment" => ['c', 'r', 'u', 'd'],
                "finance" => ['c', 'r', 'u', 'd'],
                "room" => ['c', 'r', 'b', 'u', 'd'],
                "hall" => ['c', 'r', 'b', 'u', 'd'],
                "business" => ['c', 'r',  'u', 'd'],
                "customer" => ['c', 'r',  'u', 'd'],
                "expenditure" => ['c', 'r',  'u', 'd'],
                "sales" => ['c', 'r',  'u', 'd'],
                "report" => ['r'],
                "reservation" => ['r'],
                'checkout' => ['u']
            ],
            "back-office" => [
                "hotel" => ['c', 'r', 'u'],
                "department" => ['c', 'r', 'u', 'd'],
                "configuration" => ['c', 'r', 'u', 'd'],
                "user" => ['c', 'r', 'u', 'd'],
                "revenue" => ['c', 'r', 'u', 'd'],
                "item" => ['c', 'r',  'u', 'd'],
                "role" => ['r',],
                "permission" => ['r'],
                "booking" => ['c', 'r', 'u', 'd', 'a'],
                "payment" => ['c', 'r', 'u', 'd'],
                "finance" => ['c', 'r', 'u', 'd'],
                "room" => ['c', 'r', 'b', 'u', 'd'],
                "hall" => ['c', 'r', 'b', 'u', 'd'],
                "business" => ['c', 'r',  'u', 'd'],
                "customer" => ['c', 'r',  'u', 'd'],
                "expenditure" => ['c', 'r',  'u', 'd'],
                "sales" => ['c', 'r',  'u', 'd'],
                "report" => ['r'],
                "reservation" => ['r'],
                'checkout' => ['u']
            ],
            "sales-manager" => [
                "configuration" => ['r',],
                "department" => ['r'],
                "booking" => ['r'],
                "payment" => ['r'],
                "finance" => ['r'],
                "item" => ['c', 'r',  'u', 'd'],
                "sales" => ['c', 'r',  'u', 'd'],
                "reservation" => ['r'],
                'checkout' => ['u']
            ],
            "frontdesk-manager" => [
                "configuration" => ['r'],
                "booking" => ['c', 'r', 'u', 'd', 'a'],
                "item" => ['c', 'r',  'u', 'd'],
                "payment" => ['c', 'r', 'u', 'd'],
                "finance" => ['c', 'r', 'u', 'd'],
                "business" => ['c', 'r',  'u', 'd'],
                "customer" => ['c', 'r',  'u', 'd'],
                "sales" => ['c', 'r',  'u', 'd'],
                "reservation" => ['c', 'r',  'u', 'd']
            ],
            "frontdesk" => [
                "configuration" => ['r'],
                "booking" => ['c', 'r', 'u', 'd', 'a'],
                "payment" => ['c', 'r', 'u', 'd'],
                "item" => ['r',],
                "finance" => ['c', 'r', 'u', 'd'],
                "business" => ['c', 'r',  'u', 'd'],
                "customer" => ['c', 'r',  'u'],
                "sales" => ['c', 'r',  'u', 'd'],
                "reservation" => ['c', 'r',  'u', 'd']
            ],
            "sales-staff" => [
                "configuration" => ['r',],
                "booking" => ['r'],
                "payment" => ['r'],
                "item" => ['r'],
                "finance" => ['r'],
                "sales" => ['c', 'r',  'u', 'd'],
                "reservation" => ['r'],
            ],
            "marketer" => [
                "hotel" => ['c', 'r', 'u'],
                // "user" => ['c', 'r', 'u', 'd'],
                "item" => ['c', 'r',  'u', 'd'],
            ],
        ];

        // assign permision to superadmin role
        foreach ($permissions["super-admin"] as $perm => $ops) {
            foreach ($ops as $op) {
                $permission = Permission::firstOrCreate(['name' => $this->getOperation($op, $perm)]);
            }
        }
        Role::where('name', 'super-admin')->first()->givePermissionTo(Permission::all());

        foreach ($permissions["admin"] as $perm => $ops) {
            foreach ($ops as $op) {
                $permission = Permission::firstOrCreate(['name' => $this->getOperation($op, $perm)]);
                $permission->assignRole("admin"); //assign role of admin o each permission
            }
        }

        foreach ($permissions["sales-manager"] as $perm => $ops) {
            foreach ($ops as $op) {
                $permission = Permission::firstOrCreate(['name' => $this->getOperation($op, $perm)]);
                $permission->assignRole("sales-manager"); //assign role of admin o each permission
            }
        }

        foreach ($permissions["frontdesk-manager"] as $perm => $ops) {
            foreach ($ops as $op) {
                $permission = Permission::firstOrCreate(['name' => $this->getOperation($op, $perm)]);
                $permission->assignRole("frontdesk-manager"); //assign role of admin o each permission
            }
        }

        foreach ($permissions["frontdesk"] as $perm => $ops) {
            foreach ($ops as $op) {
                $permission = Permission::firstOrCreate(['name' => $this->getOperation($op, $perm)]);
                $permission->assignRole("frontdesk"); //assign role of admin o each permission
            }
        }

        foreach ($permissions["back-office"] as $perm => $ops) {
            foreach ($ops as $op) {
                $permission = Permission::firstOrCreate(['name' => $this->getOperation($op, $perm)]);
                $permission->assignRole("back-office"); //assign role of admin o each permission
            }
        }

        foreach ($permissions["marketer"] as $perm => $ops) {
            foreach ($ops as $op) {
                $permission = Permission::firstOrCreate(['name' => $this->getOperation($op, $perm)]);
                $permission->assignRole("marketer"); //assign role of admin o each permission
            }
        }


        foreach ($permissions["sales-staff"] as $perm => $ops) {
            foreach ($ops as $op) {
                $permission = Permission::firstOrCreate(['name' => $this->getOperation($op, $perm)]);
                $permission->assignRole("sales-staff"); //assign role of admin o each permission
            }
        }

        // reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }

    function getOperation(string $val, string $perm): string
    {
        $ops = ['c' => 'create', "r" => 'read', "u" => 'update', 'b' => 'buy',  "d" => 'delete', 'a' => 'approve'];
        return $ops[$val] . " " . $perm;
    }
}
