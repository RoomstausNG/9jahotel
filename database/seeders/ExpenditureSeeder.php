<?php

namespace Database\Seeders;

use App\Models\Expenditure;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ExpenditureSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $expenditures = ["PHCN Bill", "Tax", "Wages", "Advert", "Gas", "Water", "Cleaning", "Petrol", "Taxi services"];
        foreach ($expenditures as $expenditure) {
            Expenditure::create(['source' => $expenditure]);
        }
    }
}
