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
        Schema::create('user_billing', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id'); // Foreign key for users
            $table->string('bill_number')->unique();
            $table->decimal('amount_due', 10, 2);
            $table->decimal('amount_paid', 10, 2)->nullable();
            $table->date('due_date');
            $table->date('payment_date')->nullable();
            $table->string('status')->default('Unpaid'); // Unpaid, Paid, Overdue, etc.
            $table->string('payment_method')->nullable(); // GCash, PayMaya, etc.
            $table->timestamps();

            // Optional foreign key constraint
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_billing');
    }
};
