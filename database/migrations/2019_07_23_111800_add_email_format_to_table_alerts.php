<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddEmailFormatToTableAlerts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('table_alerts', function (Blueprint $table) {
            $table->string('mail_format', 32)->default('table');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('table_alerts', function (Blueprint $table) {
            $table->dropColumn('mail_format', 32);
        });
    }
}
