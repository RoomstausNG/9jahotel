<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('refunds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_invoice_id')->constrained('booking_invoices')->onDelete('cascade');
            $table->string('bank_name');
            $table->string('account_no');
            $table->string('account_name');
            $table->string('reason');
            $table->string('other_reason')->nullable();
            $table->string('status')->default('pending');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('refunds');
    }
};
