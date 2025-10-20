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
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('client_id')->nullable(); 
            $table->string('title')->nullable(); 
            $table->text('body'); 
            $table->enum('type', ['general', 'personal']); 
            $table->enum('status', ['pending', 'sent', 'failed'])->default('pending'); 
            $table->string('sms_api_message_id')->nullable(); // from SMS API response
            $table->timestamps();

            $table->foreign('client_id')
                ->references('id')->on('clients')
                ->onDelete('cascade');
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
