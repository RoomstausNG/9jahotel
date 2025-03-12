<?php

use App\Models\Department;
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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('firstname');
            $table->foreignIdFor(Department::class)->nullable()->constrained()->nullOnDelete();
            $table->string('lastname');
            $table->string('username')->unique();
            $table->string('phone')->unique();
            $table->string('email')->nullable()->unique();
            $table->string('nok_name')->nullable();
            $table->string('nok_avatar')->nullable();
            $table->string('nok_phone')->nullable();
            $table->string('nok_email')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->boolean('can_login')->default(false);
            $table->date('date_of_employment');
            $table->string('avatar')->nullable();
            $table->string('current_role')->nullable();
            $table->string('designation');
            $table->string('gender')->nullable();
            $table->text('address');
            $table->string('account_number')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('account_name')->nullable();
            $table->string('signature')->nullable();
            $table->bigInteger('gross_annual_salary')->nullable();
            $table->bigInteger('payee')->nullable();
            $table->bigInteger('surcharges')->nullable();
            $table->bigInteger('special_bonus')->nullable();
            $table->bigInteger('health_insurance')->nullable();
            $table->bigInteger('transport_insurance')->nullable();
            $table->bigInteger('housing_insurance')->nullable();
            $table->bigInteger('overtime_insurance')->nullable();
            $table->bigInteger('special_duty_insurance')->nullable();
            $table->bigInteger('other_deductions')->nullable();
            $table->bigInteger('city_id')->nullable();
            $table->bigInteger('state_id')->nullable();
            $table->bigInteger('country_id')->nullable();
            $table->string('password');
            $table->boolean('is_new')->default(1);
            $table->timestamp('email_verified_at')->nullable();
            $table->foreignIdFor(Hotel::class)->nullable()->constrained()->cascadeOnDelete();
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
