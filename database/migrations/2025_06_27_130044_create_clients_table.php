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
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('group');
            $table->string('meter_no')->unique();
            $table->string('full_name');
            $table->string('barangay');
            $table->string('purok');
            $table->date('date_cut')->nullable(); // Corrected typo from 'data_cut' to 'date_cut' 
            $table->date('installation_date')->nullable();
            $table->string('meter_series')->nullable();
            $table->string('status')->default('Active');           
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
