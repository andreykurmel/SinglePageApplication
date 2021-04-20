<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddThemeBgColorToUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('theme_bg_color', 32)->nullable();
        });
        Schema::table('tables', function (Blueprint $table) {
            $table->string('top_theme_color', 32)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('theme_bg_color');
        });
        Schema::table('tables', function (Blueprint $table) {
            $table->dropColumn('top_theme_color');
        });
    }
}
