<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemoveObjectFromCondFormats extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cond_formats', function (Blueprint $table) {
            $table->dropColumn('object');
            $table->string('bkgd_color', 32)->nullable()->change();
            $table->string('color', 32)->nullable()->change();
            $table->string('font', 255)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cond_formats', function (Blueprint $table) {
            $table->string('object', 50)->nullable();
        });
    }
}
