<?php

namespace Database\Seeders;

use App\Models\Item;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class ItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $items = ["Coca cola", "Fried Rice", "Eba and Egusi", "Amala", "Pounded Yam", "Origin", "Jollof Rice", "Poridge"];
        foreach ($items as $item) {
            Item::create([
                'name' => $item,
                'quantity' => random_int(1, 20),
                'price' => random_int(250, 5000),
                'revenue_id' => random_int(1, 3),
                'hotel_id' => 1,
            ]);
        }
    }
}