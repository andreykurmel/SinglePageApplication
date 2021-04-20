<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddShowUsernameToUserSettings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('table_user_settings', function (Blueprint $table) {
            $table->unsignedInteger('user_fld_show_username')->default(0);
            $table->unsignedInteger('history_user_show_username')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('table_user_settings', function (Blueprint $table) {
            $table->dropColumn('user_fld_show_username');
            $table->dropColumn('history_user_show_username');
        });
    }
}
