<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\ResponseCache\Facades\ResponseCache;

class ClearResponseCache extends Command
{
  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'cache:response:clear';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Clear the response cache';

  /**
   * Execute the console command.
   */
  public function handle()
  {
    ResponseCache::clear();
    $this->info('Response cache cleared successfully!');
  }
}
