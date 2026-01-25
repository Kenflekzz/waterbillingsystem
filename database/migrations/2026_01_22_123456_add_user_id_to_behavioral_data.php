<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUserIdToBehavioralData extends Migration
{
    public function up()
    {
        // Check if 'user_id' column exists, and add it if not
        if (!Schema::hasColumn('behavioral_data', 'user_id')) {
            Schema::table('behavioral_data', function (Blueprint $table) {
                $table->unsignedBigInteger('user_id')->nullable()->after('id');
            });
        }

    }

    public function down()
    {
        Schema::table('behavioral_data', function (Blueprint $table) {
            $table->dropColumn('barangay');
            $table->dropColumn('user_id');
        });
    }
}
