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
        Schema::table('user_billing', function ($table) {
            $table->decimal('total_amount', 15, 2)->nullable()->after('current_bill');
        });
    }

    public function down()
    {
        Schema::table('user_billing', function ($table) {
            $table->dropColumn('total_amount');
        });
    }
};
