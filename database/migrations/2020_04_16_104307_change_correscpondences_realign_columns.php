<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeCorrescpondencesRealignColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mysql_correspondence')
            ->table('correspondence_stim_3d', function (Blueprint $table) {
                $table->dropColumn('table');
                $table->string('db_table')->nullable();
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
            ->table('correspondence_stim_3d', function (Blueprint $table) {
                $table->string('table', 64)->nullable();
                $table->dropColumn('db_table');
            });
    }
}
