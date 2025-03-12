<?php

namespace Database\Seeders;

use App\Models\Hall;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class HallSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $names = [
            "Conference Hall",
            "Ballroom",
            "Banquet Hall",
            "Auditorium",
            "Meeting Room",
            "Function Room",
            "Exhibition Hall",
            "Seminar Room",
            "Boardroom"
        ];
        foreach ($names as $name) {
            Hall::create([
                'name' => $name,
                'price' => random_int(10000, 22000),
                'deposit' => random_int(2000, 5600),
                'promo_price' => 0,
                'type_id' => random_int(1, 3),
                'capacity' => random_int(4, 6),
                'promo_start_day' => now()->subDays(random_int(4, 6)),
                'promo_end_day' => now()->subDays(random_int(1, 3)),
                'remarks' => 'sellable',
                'description' => $name,
                'hotel_id' => 1,
            ]);
        }
    }
}