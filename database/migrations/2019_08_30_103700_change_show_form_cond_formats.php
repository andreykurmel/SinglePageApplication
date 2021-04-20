<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeShowFormCondFormats extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cond_formats', function (Blueprint $table) {
            $table->dropColumn('show_in_form');
            $table->tinyInteger('show_form_data')->default(1);
            $table->tinyInteger('show_table_data')->default(1);
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
            $table->tinyInteger('show_in_form')->default(1);
            $table->dropColumn('show_form_data');
            $table->dropColumn('show_table_data');
        });
    }
}
