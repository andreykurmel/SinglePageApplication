<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CteateAppThemesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('app_themes', function (Blueprint $table) {
            $table->increments('id');
            $table->string('obj_type');
            $table->unsignedInteger('obj_id')->nullable();
            $table->string('navbar_bg_color', 32)->nullable();
            $table->string('ribbon_bg_color', 32)->nullable();
            $table->string('button_bg_color', 32)->nullable();
            $table->string('main_bg_color', 32)->nullable();

            $table->index('obj_type');
            $table->index('obj_id');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->unsignedInteger('app_theme_id')->nullable();

            $table->foreign('app_theme_id', 'users__app_theme_id')
                ->references('id')
                ->on('app_themes')
                ->onDelete('set null');
        });

        Schema::table('tables', function (Blueprint $table) {
            $table->unsignedInteger('app_theme_id')->nullable();

            $table->foreign('app_theme_id', 'tables__app_theme_id')
                ->references('id')
                ->on('app_themes')
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
        Schema::dropIfExists('app_themes');

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign('users__app_theme_id');
            $table->dropColumn('app_theme_id');
        });

        Schema::table('tables', function (Blueprint $table) {
            $table->dropForeign('tables__app_theme_id');
            $table->dropColumn('app_theme_id');
        });
    }
}
