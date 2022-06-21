<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIframeAppPathToTabldaApps extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mysql_correspondence')->table('correspondence_apps', function (Blueprint $table) {
            $table->string('iframe_app_path', 512)->after('app_path');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('mysql_correspondence')->table('correspondence_apps', function (Blueprint $table) {
            $table->dropColumn('iframe_app_path');
        });
    }
}
