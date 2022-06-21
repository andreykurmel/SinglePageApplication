<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMapFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('table_fields', function (Blueprint $table) {
            $table->tinyInteger('lat_field_id')->nullable();
            $table->tinyInteger('long_field_id')->nullable();
            $table->tinyInteger('addr_field_id')->nullable();
            $table->tinyInteger('info_box')->nullable();
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
            $table->dropColumn('lat_field_id');
            $table->dropColumn('long_field_id');
            $table->dropColumn('addr_field_id');
            $table->dropColumn('info_box');
        });
    }
}
