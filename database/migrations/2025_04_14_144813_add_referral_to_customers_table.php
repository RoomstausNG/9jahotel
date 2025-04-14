<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->string("referral_code")->nullable()->unique()->after("id");
            $table->string("parent_id")->nullable()->after("id");

            // Add proper foreign key constraint
            $table->foreign('parent_id')
                ->references('id')
                ->on('customers')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropColumn(['referral_code', 'parent_id']);
        });
    }
};
