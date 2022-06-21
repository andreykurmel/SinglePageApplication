<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddInfoHeaderToTableFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('table_fields', function (Blueprint $table) {
            $table->tinyInteger('is_info_header_field')->nullable();
            $table->renameColumn('lat_field_id', 'is_lat_field')->change();
            $table->renameColumn('long_field_id', 'is_long_field')->change();
            $table->renameColumn('addr_field_id', 'is_addr_field')->change();
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
            $table->dropColumn('is_info_header_field');
            $table->renameColumn('is_lat_field', 'lat_field_id')->change();
            $table->renameColumn('is_long_field', 'long_field_id')->change();
            $table->renameColumn('is_addr_field', 'addr_field_id')->change();
        });
    }
}
