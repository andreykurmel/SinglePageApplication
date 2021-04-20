<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddVisibleSharedTablePermissions2CondFormats extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('table_permissions_2_cond_formats', function (Blueprint $table) {
            $table->unsignedInteger('visible_shared')->default(1);
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
            $table->dropColumn('visible_shared');
        });
    }
}
