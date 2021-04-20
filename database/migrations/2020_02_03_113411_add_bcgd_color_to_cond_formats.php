<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddBcgdColorToCondFormats extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cond_formats', function (Blueprint $table) {
            $table->string('bkgd_color', 20)->nullable();
            $table->unsignedInteger('font_size')->nullable();
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
            $table->dropColumn('bkgd_color');
            $table->dropColumn('font_size');
        });
    }
}
