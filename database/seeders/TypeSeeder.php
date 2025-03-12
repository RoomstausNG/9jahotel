<?php

namespace Database\Seeders;

use App\Models\Type;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TypeSeeder extends Seeder
{

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            "Standard",
            "Deluxe",
            "Suite",
            "Executive",
            "Family",
            "Superior",
            "Premium"
        ];
        foreach ($types as $cat) {
            Type::create([
                'name' => $cat,
                'price1' => random_int(10000, 22000),
                'price2' => random_int(2000, 5600),
                'deposit1' => random_int(5000, 22000),
                'deposit2' => random_int(1000, 5000),
                'hotel_id' => 1,
                'weekend_price' => random_int(8000, 15000),
                'weekend_start_day' => fake()->randomElement(["saturday", "friday", "saturday"]),
                'weekend_end_day' => fake()->randomElement(["sunday", "monday", "sunday"]),
                'remarks' => 'sellable'
            ]);
        }
    }
}