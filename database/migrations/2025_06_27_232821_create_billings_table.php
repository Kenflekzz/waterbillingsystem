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
        Schema::create('billings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients')->onDelete('cascade');
            $table->string('billing_id')->unique();
            $table->date('billing_date');
            $table->integer('previous_reading');
            $table->integer('present_reading');
            $table->integer('consumed');
            $table->decimal('current_bill', 10, 2);
            $table->decimal('total_penalty',10, 2)->default(0);
            $table->decimal('maintenance_cost', 10, 2)->default(0);
            $table->decimal('total_amount', 10,2);
            $table->decimal('installation_fee',10 ,2)->default(0);          
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('billings');
    }
};
