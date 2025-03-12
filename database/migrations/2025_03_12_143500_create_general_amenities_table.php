<?php

use App\Models\Hotel;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('general_amenities', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string("icon")->nullable();
            $table->timestamps();
        });

        Schema::table('amenities', function (Blueprint $table) {
            $table->foreignId('general_amenity_id')->nullable()->constrained('amenities')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('general_amenities');
        Schema::table('amenities', function (Blueprint $table) {
            $table->dropColumn('general_amenity_id');
        });
    }
};