<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class EnsureIsDisconnectSeenIsDate extends Migration
{
    public function up()
    {
        Schema::table('clients', function (Blueprint $table) {
            // Ensure the column is of type DATE and nullable
            $table->date('is_disconnect_seen')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('clients', function (Blueprint $table) {
            // Revert the column to its previous state (if needed)
            // This is optional and depends on your previous schema
            $table->dropColumn('is_disconnect_seen');
        });
    }
}