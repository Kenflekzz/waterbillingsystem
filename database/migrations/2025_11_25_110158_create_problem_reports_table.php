<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('problem_reports', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('client_id')->nullable(); 
            $table->string('subject');
            $table->text('description');
            $table->enum('status', ['pending', 'resolved'])->default('pending');
            $table->string('image')->nullable(); // optional screenshot
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('problem_reports');
    }
};
