<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Nnjeim\World\Models\City;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('hotels', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->string('background_image')->nullable();
            $table->string('address');
            $table->string("type")->nullable()->default("both");
            $table->string('primary_currency')->nullable()->constrained('currencies')->nullOnDelete();
            $table->string('secondary_currency')->nullable()->constrained('currencies')->nullOnDelete();
            $table->foreignIdFor(City::class)->nullable()->constrained()->nullOnDelete();
            $table->string('zip_code')->nullable();
            $table->integer('service_charge')->nullable();
            $table->boolean('vat')->nullable()->default(false);
            $table->integer('surcharge')->nullable();
            $table->string('logo')->nullable();
            $table->string('primary_color')->nullable();
            $table->string('secondary_color')->nullable();
            $table->time('checked_out')->nullable();
            $table->string('telephone');
            $table->string('email')->unique();
            $table->foreignId('team_id')->nullable()->constrained()->onDelete('cascade');
            $table->integer('star_rating')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hotels');
    }
};