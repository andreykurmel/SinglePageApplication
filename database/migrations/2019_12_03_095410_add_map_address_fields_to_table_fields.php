<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMapAddressFieldsToTableFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('table_fields', function (Blueprint $table) {
            $table->renameColumn('is_addr_field', 'map_find_street_field')->change();
            $table->unsignedInteger('map_find_city_field')->nullable();
            $table->unsignedInteger('map_find_state_field')->nullable();
            $table->unsignedInteger('map_find_county_field')->nullable();
            $table->unsignedInteger('map_find_zip_field')->nullable();
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
            $table->renameColumn('map_find_street_field', 'is_addr_field')->change();
            $table->dropColumn('map_find_city_field');
            $table->dropColumn('map_find_state_field');
            $table->dropColumn('map_find_county_field');
            $table->dropColumn('map_find_zip_field');
        });
    }
}
