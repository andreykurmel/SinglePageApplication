<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TransferUserShowToTableUserSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tables', function (Blueprint $table) {
            $table->dropColumn('user_fld_show_image');
            $table->dropColumn('user_fld_show_first');
            $table->dropColumn('user_fld_show_last');
            $table->dropColumn('user_fld_show_email');
        });

        Schema::table('table_user_settings', function (Blueprint $table) {
            $table->unsignedInteger('user_fld_show_image')->default(1);
            $table->unsignedInteger('user_fld_show_first')->default(1);
            $table->unsignedInteger('user_fld_show_last')->default(1);
            $table->unsignedInteger('user_fld_show_email')->default(1);

            $table->unsignedInteger('history_user_show_image')->default(1);
            $table->unsignedInteger('history_user_show_first')->default(1);
            $table->unsignedInteger('history_user_show_last')->default(1);
            $table->unsignedInteger('history_user_show_email')->default(1);

            $table->unsignedInteger('vote_user_show_image')->default(1);
            $table->unsignedInteger('vote_user_show_first')->default(1);
            $table->unsignedInteger('vote_user_show_last')->default(1);
            $table->unsignedInteger('vote_user_show_email')->default(1);
            $table->unsignedInteger('vote_user_show_username')->default(1);

            $table->unsignedInteger('max_cell_rows')->default(0);
            $table->unsignedInteger('cell_height')->default(1);
            $table->unsignedInteger('left_menu_width')->default(250);
            $table->unsignedInteger('stim_filter_width')->default(220);
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
            $table->unsignedInteger('user_fld_show_image')->default(1);
            $table->unsignedInteger('user_fld_show_first')->default(1);
            $table->unsignedInteger('user_fld_show_last')->default(1);
            $table->unsignedInteger('user_fld_show_email')->default(1);
        });

        Schema::table('table_user_settings', function (Blueprint $table) {
            $table->dropColumn('user_fld_show_image');
            $table->dropColumn('user_fld_show_first');
            $table->dropColumn('user_fld_show_last');
            $table->dropColumn('user_fld_show_email');

            $table->dropColumn('history_user_show_image');
            $table->dropColumn('history_user_show_first');
            $table->dropColumn('history_user_show_last');
            $table->dropColumn('history_user_show_email');

            $table->dropColumn('vote_user_show_image');
            $table->dropColumn('vote_user_show_first');
            $table->dropColumn('vote_user_show_last');
            $table->dropColumn('vote_user_show_email');
            $table->dropColumn('vote_user_show_username');

            $table->dropColumn('max_cell_rows');
            $table->dropColumn('cell_height');
            $table->dropColumn('left_menu_width');
            $table->dropColumn('stim_filter_width');
        });
    }
}
