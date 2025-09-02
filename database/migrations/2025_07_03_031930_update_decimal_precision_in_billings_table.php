<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('billings', function (Blueprint $table) {
            $table->decimal('current_bill', 15, 2)->change();
            $table->decimal('total_penalty', 15, 2)->change();
            $table->decimal('maintenance_cost', 15, 2)->change();
            $table->decimal('total_amount', 15, 2)->change();
            $table->decimal('installation_fee', 15, 2)->change();
        });
    }

    public function down(): void
    {
        Schema::table('billings', function (Blueprint $table) {
            $table->decimal('current_bill', 10, 2)->change();
            $table->decimal('total_penalty', 10, 2)->change();
            $table->decimal('maintenance_cost', 10, 2)->change();
            $table->decimal('total_amount', 10, 2)->change();
            $table->decimal('installation_fee', 10, 2)->change();
        });
    }
};
