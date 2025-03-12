<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        for ($i = 0; $i < 50; $i++) {
            Customer::create([
                'firstname' => fake()->firstName(),
                'lastname' => fake()->lastName(),
                'phone' => fake()->unique()->e164PhoneNumber(),
                'email' => fake()->unique()->email(),
                'date_of_birth' => now()->subYears(18, 40),
                'gender' => fake()->randomElement(['male', 'female']),
                'address' => fake()->address(),
            ]);
        }
    }
}