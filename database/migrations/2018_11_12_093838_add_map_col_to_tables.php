<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMapColToTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tables', function (Blueprint $table) {
            $table->unsignedInteger('map_icon_field_id')->nullable();
            $table->string('map_icon_style', 16)->default('dist');
            $table->unsignedInteger('map_position_refid')->nullable();
            $table->unsignedInteger('map_popup_hdr_id')->nullable();

            $table->foreign('map_icon_field_id', 'tables_map_icon_field_id')
                ->references('id')
                ->on('table_fields')
                ->onDelete('set null');
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
            $table->dropForeign('tables_map_icon_field_id');
            $table->dropColumn('map_icon_field_id');
            $table->dropColumn('map_icon_style');
            $table->dropColumn('map_position_refid');
            $table->dropColumn('map_popup_hdr_id');
        });
    }
}
