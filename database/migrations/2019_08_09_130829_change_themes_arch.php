<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeThemesArch extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tables', function (Blueprint $table) {
            $table->dropColumn('nav_background');
            $table->dropColumn('top_theme_color');
            $table->dropColumn('theme_ribbon_color');
            $table->dropColumn('theme_mainbg_color');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('theme_bg_color');
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
            $table->string('nav_background', 32)->nullable();
            $table->string('top_theme_color', 32)->nullable();
            $table->string('theme_ribbon_color', 32)->nullable();
            $table->string('theme_mainbg_color', 32)->nullable();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('theme_bg_color', 32)->nullable();
        });
    }
}
