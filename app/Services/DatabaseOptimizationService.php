<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class DatabaseOptimizationService
{
  /**
   * Apply optimized indexes to database tables
   */
  public function applyOptimizedIndexes(): array
  {
    $optimizationResults = [];

    // Rooms table optimization
    if (Schema::hasTable('rooms') && !Schema::hasIndex('rooms', 'rooms_hotel_id_type_id_status_index')) {
      Schema::table('rooms', function (Blueprint $table) {
        $table->index(['hotel_id', 'type_id', 'status'], 'rooms_hotel_id_type_id_status_index');
      });
      $optimizationResults[] = 'Added composite index to rooms table for hotel_id, type_id, and status';
    }

    // Room statuses table optimization
    if (Schema::hasTable('room_statuses') && !Schema::hasIndex('room_statuses', 'room_statuses_room_id_date_index')) {
      Schema::table('room_statuses', function (Blueprint $table) {
        $table->index(['room_id', 'date'], 'room_statuses_room_id_date_index');
      });
      $optimizationResults[] = 'Added composite index to room_statuses table for room_id and date';
    }

    // Bookings table optimization
    if (Schema::hasTable('bookings') && !Schema::hasIndex('bookings', 'bookings_user_id_status_index')) {
      Schema::table('bookings', function (Blueprint $table) {
        $table->index(['user_id', 'status'], 'bookings_user_id_status_index');
      });
      $optimizationResults[] = 'Added composite index to bookings table for user_id and status';
    }

    // Add more table indexes as needed

    return $optimizationResults;
  }

  /**
   * Find and report slow queries from the query log
   */
  public function findSlowQueries(float $thresholdInMs = 100.0): array
  {
    $slowQueries = [];

    // This requires query logging to be enabled
    $queries = DB::getQueryLog();

    foreach ($queries as $query) {
      if ($query['time'] > $thresholdInMs) {
        $slowQueries[] = [
          'query' => $query['query'],
          'bindings' => $query['bindings'],
          'time' => $query['time'],
        ];
      }
    }

    return $slowQueries;
  }

  /**
   * Optimize a specific database table
   */
  public function optimizeTable(string $table): bool
  {
    try {
      DB::statement("OPTIMIZE TABLE {$table}");
      return true;
    } catch (\Exception $e) {
      return false;
    }
  }
}
