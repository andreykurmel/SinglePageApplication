<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPathToCorrespTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mysql_correspondence')
            ->table('correspondence_apps', function (Blueprint $table) {
                $table->string('app_path', 512)->nullable();
                $table->string('subdomain', 64)->nullable();
                $table->string('icon_full_path', 512)->nullable();
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
            ->table('correspondence_apps', function (Blueprint $table) {
                $table->dropColumn('app_path');
                $table->dropColumn('subdomain');
                $table->dropColumn('icon_full_path');
            });
    }
}
