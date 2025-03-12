<?php

use App\Models\Floor;
use App\Models\Hotel;
use App\Models\Type;
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
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->string('number');
            $table->foreignId('type_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Floor::class)->nullable()->constrained()->nullOnDelete();
            $table->foreignIdFor(Hotel::class)->nullable()->constrained()->cascadeOnDelete();
            $table->integer('price')->nullable();
            $table->integer('capacity');
            $table->string('bed_type');
            $table->string('status')->nullable()->default('available');
            $table->string('character')->nullable()->default('no smoking');
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->boolean('is_available')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};
