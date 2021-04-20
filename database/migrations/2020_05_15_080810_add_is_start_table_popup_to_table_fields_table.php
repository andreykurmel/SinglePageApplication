<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIsStartTablePopupToTableFieldsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('table_fields', function (Blueprint $table) {
            $table->tinyInteger('is_start_table_popup')->default(0);
            $table->tinyInteger('is_topbot_in_popup')->default(0);
            $table->tinyInteger('is_dcr_section')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('table_fields', function (Blueprint $table) {
            $table->dropColumn('is_start_table_popup');
            $table->dropColumn('is_topbot_in_popup');
            $table->dropColumn('is_dcr_section');
        });
    }
}
