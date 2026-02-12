<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAppFontColorToAppThemesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('app_themes', function (Blueprint $table) {
            $table->string('app_font_color', 32)->nullable();
            $table->string('app_font_family', 32)->nullable();
            $table->unsignedInteger('appsys_font_size')->nullable();
            $table->string('appsys_font_color', 32)->nullable();
            $table->string('appsys_font_family', 32)->nullable();
            $table->unsignedInteger('appsys_tables_font_size')->nullable();
            $table->string('appsys_tables_font_color', 32)->nullable();
            $table->string('appsys_tables_font_family', 32)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('app_themes', function (Blueprint $table) {
            $table->dropColumn('app_font_color');
            $table->dropColumn('app_font_family');
            $table->dropColumn('appsys_font_size');
            $table->dropColumn('appsys_font_color');
            $table->dropColumn('appsys_font_family');
            $table->dropColumn('appsys_tables_font_size');
            $table->dropColumn('appsys_tables_font_color');
            $table->dropColumn('appsys_tables_font_family');
        });
    }
}
