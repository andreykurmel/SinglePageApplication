<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddUserFieldSettingsToTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tables', function (Blueprint $table) {
            $table->unsignedInteger('user_fld_show_image')->default(1);
            $table->unsignedInteger('user_fld_show_first')->default(1);
            $table->unsignedInteger('user_fld_show_last')->default(1);
            $table->unsignedInteger('user_fld_show_email')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tables', function (Blueprint $table) {
            $table->dropColumn('user_fld_show_image');
            $table->dropColumn('user_fld_show_first');
            $table->dropColumn('user_fld_show_last');
            $table->dropColumn('user_fld_show_email');
        });
    }
}
