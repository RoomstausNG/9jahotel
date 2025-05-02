<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Hotel;
use App\Models\Type;
use App\Models\Hall;

class UpdatePrice extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:price';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update hotel lowest prices for both Type and Hall models';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Updating hotel lowest prices for all hotels...');
        $hotels = Hotel::all();
        foreach ($hotels as $hotel) {
            Type::updateHotelLowestPrice($hotel->id);
            Hall::updateHotelLowestPrice($hotel->id);
            $this->info("Updated lowest prices for hotel ID: {$hotel->id}");
        }
        $this->info('All hotel lowest prices updated successfully!');
    }
}