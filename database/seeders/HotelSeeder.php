<?php

namespace Database\Seeders;

use App\Models\Hotel;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class HotelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        for ($i = 0; $i < 1; $i++) {
            Hotel::create([
                'name' => '9jahotels',
                'telephone' => fake()->phoneNumber(),
                'email' => fake()->companyEmail(),
                'primary_color' => fake()->colorName(),
                'city_id' => random_int(1, 15000),
                'address' => fake()->streetAddress(),
            ]);
        }
    }
}
