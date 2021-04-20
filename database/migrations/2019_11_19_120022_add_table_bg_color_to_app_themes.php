<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTableBgColorToAppThemes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('app_themes', function (Blueprint $table) {
            $table->string('table_hdr_bg_color', 32)->nullable();
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
            $table->dropColumn('table_hdr_bg_color');
        });
    }
}
