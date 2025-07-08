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
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->integer('percent');
            $table->string('type')->comment('marketer, agent, user');
            $table->date('valid_from');
            $table->date('valid_till');
            $table->integer('limit_usage')->nullable();
            $table->decimal('highest_value', 8, 2)->nullable();
            $table->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('status')->default(true);
            $table->integer('usage_count')->default(0);
            $table->foreignIdFor(Hotel::class)->nullable()->constrained()->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coupons');
    }
};
