<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Floor;
use App\Models\Hotel;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Eloquent::unguard();

        //disable foreign key check for this connection before running seeders
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        // $floors = ["first", "second", "third", "fourth", "fifth", "sixth", "seventh"];
        // $floors = ["ground", "eighth", "ninth", "tenth"];
        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
        // floor seeder
        // foreach ($floors as $floor) {
        //     Floor::create(['name' => $floor]);
        // }
        $this->call([
            WorldSeeder::class,
            FloorSeeder::class,
            // HotelSeeder::class,
            // DepartmentSeeder::class,
            // RevenueSeeder::class,
            // ExpenditureSeeder::class,
            RoleSeeder::class,
            PermissionSeeder::class,
            // UserSeeder::class,
            // TypeSeeder::class,
            // RoomSeeder::class,
            // HallSeeder::class,
            // ItemSeeder::class,
            // CustomerSeeder::class,
        ]);

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $user = User::where('email', 'segunoyebolu@gmail.com')->first();
        $hotels = Hotel::all();
        foreach ($hotels as $hotel) {
            $user->hotels()->attach($hotel);
        }
    }
}