<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAlwaysOnToTablePermissions2CondFormats extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('table_permissions_2_cond_formats', function (Blueprint $table) {
            $table->unsignedInteger('always_on')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('table_permissions_2_cond_formats', function (Blueprint $table) {
            $table->dropColumn('always_on');
        });
    }
}
