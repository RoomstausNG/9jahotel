<?php

namespace Database\Seeders;

use App\Models\Room;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class RoomSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        for ($i = 0; $i < 12; $i++) {
            Room::create([
                'number' => Str::random(3) . $i,
                'type_id' => random_int(1, 5),
                'floor_id' => random_int(1, 7),
                'capacity' => random_int(1, 5),
                'hotel_id' => 1,
                'status' => 'available',
                // 'status' => fake()->randomElement(["available", "pending", "available", "unpaid", "available", "occupied", "available", "late", "available", "repair", "available", "dirty", "available", "paid", "available"]),
                'bed_type' => fake()->randomElement(["Single bed", "Twin bed", "Double bed", "Queen bed", "King bed", "Sofa bed", "Bunk bed"])
            ]);
        }
    }
}