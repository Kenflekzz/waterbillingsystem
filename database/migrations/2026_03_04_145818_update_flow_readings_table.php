<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('flow_readings', function (Blueprint $table) {
            $table->foreignId('client_id')->nullable()->constrained('clients')->nullOnDelete()->after('id');
            $table->foreignId('iot_device_id')->nullable()->constrained('iot_devices')->nullOnDelete()->after('client_id');
            $table->decimal('cubic_meter', 10, 4)->default(0)->after('total_volume'); // total_volume ÷ 1000
        });
    }

    public function down()
    {
        Schema::table('flow_readings', function (Blueprint $table) {
            $table->dropForeign(['client_id']);
            $table->dropForeign(['iot_device_id']);
            $table->dropColumn(['client_id', 'iot_device_id', 'cubic_meter']);
        });
    }
};
