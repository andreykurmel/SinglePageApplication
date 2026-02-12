<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeRowHeightForTableFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('table_fields', function (Blueprint $table) {
            $table->dropColumn('row_height');
            $table->tinyInteger('verttb_row_height')->default(1);
            $table->tinyInteger('verttb_cell_height')->default(1);
            $table->tinyInteger('verttb_he_auto')->default(1);
            $table->integer('form_row_spacing')->nullable();
            $table->string('pop_tab_name', 128)->nullable();
            $table->unsignedInteger('pop_tab_order')->nullable();
            $table->string('section_header', 255)->nullable();
            $table->string('section_font', 255)->nullable();
            $table->integer('section_size')->nullable();
            $table->string('section_align_h', 32)->nullable();
            $table->string('section_align_v', 32)->nullable();
            $table->integer('section_height')->nullable();
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
            $table->dropColumn('verttb_cell_height');
            $table->dropColumn('verttb_row_height');
            $table->dropColumn('verttb_he_auto');
            $table->dropColumn('form_row_spacing');
            $table->dropColumn('pop_tab_name');
            $table->dropColumn('pop_tab_order');
            $table->dropColumn('section_header');
            $table->dropColumn('section_font');
            $table->dropColumn('section_size');
            $table->dropColumn('section_align_h');
            $table->dropColumn('section_align_v');
            $table->dropColumn('section_height');
            $table->integer('row_height')->default(1);
        });
    }
}
