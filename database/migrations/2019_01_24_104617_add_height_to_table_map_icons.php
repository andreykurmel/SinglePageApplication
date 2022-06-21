<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddHeightToTableMapIcons extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('table_map_icons', function (Blueprint $table) {
            $table->unsignedInteger('height')->after('icon_path')->nullable();
            $table->unsignedInteger('width')->after('height')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('table_map_icons', function (Blueprint $table) {
            $table->dropColumn('height');
            $table->dropColumn('width');
        });
    }
}
