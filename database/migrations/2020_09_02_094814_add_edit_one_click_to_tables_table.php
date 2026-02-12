<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddEditOneClickToTablesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tables', function (Blueprint $table) {
            $table->unsignedInteger('no_span_data_cell_in_popup')->default(1);
            $table->unsignedInteger('refill_auto_oncopy')->default(1);
            $table->unsignedInteger('mirror_edited_underline')->default(0);
            $table->unsignedInteger('edit_one_click')->default(0);
            $table->unsignedInteger('vert_tb_bgcolor')->nullable();
            $table->unsignedInteger('vert_tb_floating')->nullable();
            $table->unsignedInteger('vert_tb_hdrwidth')->default(30);
            $table->unsignedInteger('vert_tb_rowspacing')->default(5);
            $table->unsignedInteger('vert_tb_width_px')->default(768);
            $table->unsignedInteger('vert_tb_width_px_min')->default(200);
            $table->unsignedInteger('vert_tb_width_px_max')->nullable();
            $table->unsignedInteger('vert_tb_height')->default(80);
            $table->unsignedInteger('vert_tb_height_min')->default(20);
            $table->unsignedInteger('vert_tb_height_max')->nullable();
            $table->unsignedInteger('linkd_tb_width_px')->default(768);
            $table->unsignedInteger('linkd_tb_width_px_min')->default(200);
            $table->unsignedInteger('linkd_tb_width_px_max')->nullable();
            $table->unsignedInteger('linkd_tb_height')->default(80);
            $table->unsignedInteger('linkd_tb_height_min')->default(20);
            $table->unsignedInteger('linkd_tb_height_max')->nullable();
            $table->unsignedInteger('openai_tb_key_id')->nullable();
            $table->unsignedInteger('multi_link_app_fld_id')->nullable();
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
            $table->dropColumn('no_span_data_cell_in_popup');
            $table->dropColumn('refill_auto_oncopy');
            $table->dropColumn('mirror_edited_underline');
            $table->dropColumn('edit_one_click');
            $table->dropColumn('vert_tb_bgcolor');
            $table->dropColumn('vert_tb_floating');
            $table->dropColumn('vert_tb_hdrwidth');
            $table->dropColumn('vert_tb_rowspacing');
            $table->dropColumn('vert_tb_width_px');
            $table->dropColumn('vert_tb_width_px_min');
            $table->dropColumn('vert_tb_width_px_max');
            $table->dropColumn('vert_tb_height');
            $table->dropColumn('vert_tb_height_min');
            $table->dropColumn('vert_tb_height_max');
            $table->dropColumn('linkd_tb_width_px');
            $table->dropColumn('linkd_tb_width_px_min');
            $table->dropColumn('linkd_tb_width_px_max');
            $table->dropColumn('linkd_tb_height');
            $table->dropColumn('linkd_tb_height_min');
            $table->dropColumn('linkd_tb_height_max');
            $table->dropColumn('openai_tb_key_id');
            $table->dropColumn('multi_link_app_fld_id');
        });
    }
}
