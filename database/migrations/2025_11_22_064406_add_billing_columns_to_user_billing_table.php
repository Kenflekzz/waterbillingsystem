<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('user_billing', function (Blueprint $table) {
            $table->decimal('current_bill', 10, 2)->default(0);
            $table->decimal('arrears', 10, 2)->default(0);
            $table->decimal('penalty', 10, 2)->default(0);
            $table->decimal('consumed', 10, 2)->default(0);
        });
    }

    public function down()
    {
        Schema::table('user_billing', function (Blueprint $table) {
            $table->dropColumn(['current_bill', 'arrears', 'penalty', 'consumed']);
        });
    }
};