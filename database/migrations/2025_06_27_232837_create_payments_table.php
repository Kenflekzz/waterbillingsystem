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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients')->onDelete('cascade');
            $table->date('billing_month');
            $table->decimal('current_bill', 10, 2);
            $table->decimal('arrears', 10, 2)->default(0);
            $table->decimal('penalty', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2);
            $table->string('status')->default('unpaid'); // unpaid, paid, overdue
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};