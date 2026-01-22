<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CleanupIsDisconnectSeenColumn extends Migration
{
    public function up()
    {
        // Clean up invalid data in the is_disconnect_seen column
        DB::table('clients')
            ->where('is_disconnect_seen', '!=', null)
            ->where('is_disconnect_seen', '!=', '0')
            ->update(['is_disconnect_seen' => null]);

        // Now change the column type to DATE
        Schema::table('clients', function (Blueprint $table) {
            $table->date('is_disconnect_seen')->nullable()->change();
        });
    }

    public function down()
    {
        // Revert the column to its previous state (if needed)
        Schema::table('clients', function (Blueprint $table) {
            $table->string('is_disconnect_seen')->nullable()->change();
        });
    }
}