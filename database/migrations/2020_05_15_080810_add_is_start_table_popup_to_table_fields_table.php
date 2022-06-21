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
            $table->tinyInteger('fld_display_name')->default(1);
            $table->tinyInteger('fld_display_value')->default(1);
            $table->tinyInteger('fld_display_border')->default(1);
            $table->tinyInteger('is_start_table_popup')->default(0);
            $table->tinyInteger('is_topbot_in_popup')->default(0);
            $table->tinyInteger('is_dcr_section')->default(0);
            $table->string('image_display_view', 16)->default('scroll');
            $table->string('image_display_fit', 16)->default('fill');
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
            $table->dropColumn('fld_display_name');
            $table->dropColumn('fld_display_value');
            $table->dropColumn('fld_display_border');
            $table->dropColumn('is_start_table_popup');
            $table->dropColumn('is_topbot_in_popup');
            $table->dropColumn('is_dcr_section');
            $table->dropColumn('image_display_view');
            $table->dropColumn('image_display_fit');
        });
    }
}
