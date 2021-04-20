<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddShownPopupToCondFormats extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cond_formats', function (Blueprint $table) {
            $table->tinyInteger('show_in_form')->default(1);
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
            $table->dropColumn('show_in_form');
        });
    }
}
