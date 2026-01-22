<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('behavioral_data', function (Blueprint $table) {
            if (!Schema::hasColumn('behavioral_data', 'barangay')) {
                $table->string('barangay', 255)->after('user_id');
            }
        });
    }

    public function down()
    {
        Schema::table('behavioral_data', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn(['user_id', 'barangay']);
        });
    }
};