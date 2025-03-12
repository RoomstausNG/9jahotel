<?php

use App\Models\Hotel;
use App\Models\User;
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
        Schema::create('profiles', function (Blueprint $table) {
            $table->id();
            $table->string('account_number')->nullable();
            $table->string('account_name')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('special_bonus')->nullable();
            $table->integer('paye')->nullable();
            $table->integer('pension_fund_contribution')->nullable();
            $table->integer('other_deductions')->nullable();
            $table->integer('surcharges')->nullable();
            $table->integer('tip')->nullable();
            $table->integer('health_insurance')->nullable();
            $table->integer('transport_allowance')->nullable();
            $table->integer('housing_allowance')->nullable();
            $table->integer('special_duty_allowance')->nullable();
            $table->integer('overtime_allowance')->nullable();
            $table->foreignIdFor(User::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Hotel::class)->nullable()->constrained()->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profiles');
    }
};