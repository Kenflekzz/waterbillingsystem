<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMeterStatusReplacementDateToClientsTable extends Migration
{
    public function up()
    {
        Schema::table('clients', function (Blueprint $table) {
            if (!Schema::hasColumn('clients', 'meter_status')) {
                $table->string('meter_status', 50)->nullable();
            }
            if (!Schema::hasColumn('clients', 'replacement_date')) {
                $table->date('replacement_date')->nullable();
            }
        });
    }

    public function down()
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn(['meter_status', 'replacement_date']);
        });
    }
}