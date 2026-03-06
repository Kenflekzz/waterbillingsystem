<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::create('iot_devices', function (Blueprint $table) {
        $table->id();
        $table->foreignId('client_id')->nullable()->constrained('clients')->nullOnDelete();
        $table->string('device_name');        // e.g. "ESP32-001"
        $table->string('ip_address');         // e.g. "192.168.1.105"
        $table->integer('port')->default(81); // WebSocket port
        $table->boolean('is_active')->default(true);
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('iot_devices');
    }
};
