<?php

namespace Database\Seeders;

use App\Models\Floor;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class FloorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $floors = ["ground", "eighth", "ninth", "tenth", "first", "second", "third", "fourth", "fifth", "sixth", "seventh"];
        // $floors = ["ground", "eighth", "ninth", "tenth"];
        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
        // floor seeder
        foreach ($floors as $floor) {
            Floor::create(['name' => $floor]);
        }
    }
}
