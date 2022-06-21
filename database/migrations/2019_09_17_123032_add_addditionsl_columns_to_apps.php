<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAddditionslColumnsToApps extends Migration
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
                $table->string('code', 64)->nullable();
                $table->string('type', 64)->nullable();
                $table->string('controller', 255)->nullable();
                $table->tinyInteger('open_as_popup')->default(0);
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
                $table->dropColumn('code');
                $table->dropColumn('type');
                $table->dropColumn('controller');
                $table->dropColumn('open_as_popup');
            });
    }
}
