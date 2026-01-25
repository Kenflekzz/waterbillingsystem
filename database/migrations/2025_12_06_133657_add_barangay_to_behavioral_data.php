<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Check if the column 'barangay' already exists in the table
        if (!Schema::hasColumn('behavioral_data', 'barangay')) {
            Schema::table('behavioral_data', function (Blueprint $table) {
                $table->string('barangay')->nullable()->after('user_id');
            });
        }
    }

    public function down()
    {
        Schema::table('behavioral_data', function (Blueprint $table) {
            $table->dropColumn('barangay');
        });
    }
};
