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
        $table->decimal('total_penalty', 15, 2)->nullable()->after('total_amount');
    });
}

public function down()
{
    Schema::table('user_billing', function ($table) {
        $table->dropColumn('total_penalty');
    });
}
};
