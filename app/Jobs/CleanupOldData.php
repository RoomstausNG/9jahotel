<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class CleanupOldData implements ShouldQueue
{
  use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

  /**
   * The number of days to keep data for
   */
  protected int $daysToKeep;

  /**
   * Create a new job instance.
   */
  public function __construct(int $daysToKeep = 90)
  {
    $this->daysToKeep = $daysToKeep;
  }

  /**
   * Execute the job.
   */
  public function handle(): void
  {
    $cutoffDate = Carbon::now()->subDays($this->daysToKeep)->format('Y-m-d');
    Log::info("Starting old data cleanup for data before {$cutoffDate}");

    try {
      // Clean up old room statuses
      $deletedStatuses = DB::table('room_statuses')
        ->where('date', '<', $cutoffDate)
        ->delete();
      Log::info("Deleted {$deletedStatuses} old room statuses");

      // Clean up old notifications
      if (Schema::hasTable('notifications')) {
        $deletedNotifications = DB::table('notifications')
          ->where('created_at', '<', Carbon::now()->subDays($this->daysToKeep))
          ->delete();
        Log::info("Deleted {$deletedNotifications} old notifications");
      }

      // Clean up old failed jobs
      if (Schema::hasTable('failed_jobs')) {
        $deletedFailedJobs = DB::table('failed_jobs')
          ->where('failed_at', '<', Carbon::now()->subDays($this->daysToKeep))
          ->delete();
        Log::info("Deleted {$deletedFailedJobs} old failed jobs");
      }

      // Clean up old job batches
      if (Schema::hasTable('job_batches')) {
        $deletedJobBatches = DB::table('job_batches')
          ->where('created_at', '<', Carbon::now()->subDays($this->daysToKeep))
          ->delete();
        Log::info("Deleted {$deletedJobBatches} old job batches");
      }

      // Archive old completed bookings (instead of deleting) by marking them
      if (Schema::hasTable('bookings') && Schema::hasColumn('bookings', 'archived')) {
        $archivedBookings = DB::table('bookings')
          ->where('check_out', '<', $cutoffDate)
          ->where('status', 'completed')
          ->where('archived', false)
          ->update(['archived' => true]);
        Log::info("Archived {$archivedBookings} old completed bookings");
      }

      Log::info("Old data cleanup completed successfully");
    } catch (\Exception $e) {
      Log::error("Error during old data cleanup: " . $e->getMessage());
      throw $e;
    }
  }
}
