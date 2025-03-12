<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'firstname' => 'Bowofade',
            'username' => 'admin',
            'lastname' => 'Oyerinde',
            'department_id' => random_int(1, 7),
            'phone' => '07065720177',
            'email' => 'contact@bowofade.com',
            'date_of_birth' => now()->subYears(18, 40),
            'date_of_employment' => now()->subYears(7, 25),
            'can_login' => true,
            'address' => fake()->address(),
            'password' => bcrypt('123456'),
            'hotel_id' => 1,
            'current_role' => 'super-admin',
            'designation' => fake()->randomElement(['Mr', 'Miss', 'Dr', 'Mrs', 'Prof.'])
        ]);
        for ($i = 0; $i < 20; $i++) {
            $roles = ['frontdesk', 'frontdesk-manager', "sales-staff", "sales-manager"];
            User::create([
                'firstname' => fake()->firstName(),
                'lastname' => fake()->lastName(),
                'department_id' => random_int(1, 7),
                'phone' => fake()->unique()->e164PhoneNumber(),
                'email' => fake()->unique()->email(),
                'date_of_birth' => now()->subYears(18, 40),
                'date_of_employment' => now()->subYears(18, 40),
                'can_login' => random_int(0, 1),
                'address' => fake()->address(),
                'password' => bcrypt('password'),
                'hotel_id' => random_int(1, 3),
                'current_role' => $roles[array_rand($roles)],
                'designation' => fake()->randomElement(['Mr', 'Miss', 'Dr', 'Mrs', 'Prof.'])
            ]);
        }
    }
}