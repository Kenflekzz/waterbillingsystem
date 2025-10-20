<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBehavioralDataTable extends Migration
{
    public function up()
    {
        Schema::create('behavioral_data', function (Blueprint $table) {
            $table->id();
            $table->string('metric_name')->nullable(); // e.g. 'consumption'
            $table->double('value')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('behavioral_data');
    }
}
