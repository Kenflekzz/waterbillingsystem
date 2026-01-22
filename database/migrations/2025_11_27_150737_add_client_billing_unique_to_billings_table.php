<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('billings', function (Blueprint $table) {
            // Drop the existing unique index on billing_id if exists
            $table->dropUnique(['billing_id']);

            // Add composite unique key (client_id + billing_id)
            $table->unique(['client_id', 'billing_id'], 'client_billing_unique');
        });
    }

    public function down(): void
    {
        Schema::table('billings', function (Blueprint $table) {
            // Remove the composite unique key
            $table->dropUnique('client_billing_unique');

            // Restore original unique index on billing_id
            $table->unique('billing_id');
        });
    }
};
