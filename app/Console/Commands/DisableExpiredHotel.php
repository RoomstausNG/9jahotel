<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class DisableExpiredHotel extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:disable-expired-hotel';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Disable all hotel where the renewal date has passed';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        //
    }
}
