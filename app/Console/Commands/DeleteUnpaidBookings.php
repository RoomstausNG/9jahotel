<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\Booking;
use Illuminate\Console\Command;

class DeleteUnpaidBookings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:deleteunpaidbookings';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete unpaid bookings for non-credit customers';

    /**
     * Execute the console command.
     */
    public function handle()
    {

        // Booking::whereHas('customer', function ($query) {
        //     $query->whereNull('business_id');
        // })->where('status', 'unpaid')->delete();

        Booking::whereHas('customer', function ($query) {
            $query->whereNull('business_id');
        })->where('status', 'unpaid')
            ->whereDate('created_at', '<=', Carbon::now()->subHours(12)->toDateString())
            ->delete();

        $this->info('Unpaid non-business customer bookings have been deleted successfully.');
    }
}