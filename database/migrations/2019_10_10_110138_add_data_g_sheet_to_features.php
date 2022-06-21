<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDataGSheetToFeatures extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('plan_features', function (Blueprint $table) {
            $table->unsignedInteger('data_g_sheets')->nullable()->after('data_paste');
            $table->unsignedInteger('data_web_scrap')->nullable()->after('data_g_sheets');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('plan_features', function (Blueprint $table) {
            $table->dropColumn('data_g_sheets');
            $table->dropColumn('data_web_scrap');
        });
    }
}
