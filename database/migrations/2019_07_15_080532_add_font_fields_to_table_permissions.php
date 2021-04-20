<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFontFieldsToTablePermissions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('table_permissions', function (Blueprint $table) {
            $table->string('dcr_title_font_type', 64)->nullable();
            $table->tinyInteger('dcr_title_line_top')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('table_permissions', function (Blueprint $table) {
            $table->dropColumn('dcr_title_font_type');
            $table->dropColumn('dcr_title_line_top');
        });
    }
}
