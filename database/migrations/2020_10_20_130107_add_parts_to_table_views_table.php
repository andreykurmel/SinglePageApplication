<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPartsToTableViewsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('table_views', function (Blueprint $table) {
            $table->string('parts_avail', 255)->nullable();
            $table->string('parts_default', 32)->nullable();
            $table->unsignedInteger('is_system')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('table_views', function (Blueprint $table) {
            $table->dropColumn('parts_avail');
            $table->dropColumn('parts_default');
            $table->dropColumn('is_system');
        });
    }
}
