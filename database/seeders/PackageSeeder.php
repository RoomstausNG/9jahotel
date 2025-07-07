<?php

namespace Database\Seeders;

use App\Models\Package;
use Illuminate\Database\Seeder;

class PackageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $packages = [
            [
                'name' => 'monthly',
                'amount' => 15000,
            ],
            [
                'name' => 'annually',
                'amount' => 99995,
            ],
            [
                'name' => 'quarterly',
                'amount' => 35000,
            ],
        ];

        foreach ($packages as $package) {
            Package::firstOrCreate(
                ['name' => $package['name']],
                ['amount' => $package['amount']]
            );
        }
    }
}
