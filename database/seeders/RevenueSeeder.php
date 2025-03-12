<?php

namespace Database\Seeders;

use App\Models\Revenue;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RevenueSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $revenues = ["Restaurant", "Pool", "Bar", "Beach Side", "Theater", "Games", "Fishing"];
        foreach ($revenues as $revenue) {
            Revenue::create(['source' => $revenue]);
        }
    }
}
