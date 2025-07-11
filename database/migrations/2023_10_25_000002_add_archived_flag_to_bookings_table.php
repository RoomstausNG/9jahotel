<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddArchivedFlagToBookingsTable extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    if (Schema::hasTable('bookings') && !Schema::hasColumn('bookings', 'archived')) {
      Schema::table('bookings', function (Blueprint $table) {
        $table->boolean('archived')->default(false)->after('status');
        $table->index('archived', 'bookings_archived_index');
      });
    }
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
  {
    if (Schema::hasTable('bookings') && Schema::hasColumn('bookings', 'archived')) {
      Schema::table('bookings', function (Blueprint $table) {
        $table->dropIndex('bookings_archived_index');
        $table->dropColumn('archived');
      });
    }
  }
}
