<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddOnChangeAppIdToCorrespondenceTablesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mysql_correspondence')
            ->table('correspondence_tables', function (Blueprint $table) {
                $table->unsignedInteger('on_change_app_id')->nullable();
            });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('mysql_correspondence')
            ->table('correspondence_tables', function (Blueprint $table) {
                $table->dropColumn('on_change_app_id');
            });
    }
}
