<?php

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
        Schema::table('payments', function (Blueprint $table) {
            $table->unsignedBigInteger('user_billing_id')->nullable()->after('client_id');

            // Optional: Add a foreign key constraint if you want
            $table->foreign('user_billing_id')
                  ->references('id')
                  ->on('user_billing')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['user_billing_id']);
            $table->dropColumn('user_billing_id');
        });
    }
};
