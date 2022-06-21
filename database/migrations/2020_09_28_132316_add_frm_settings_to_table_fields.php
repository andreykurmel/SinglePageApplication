<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFrmSettingsToTableFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('table_fields', function (Blueprint $table) {
            $table->tinyInteger('is_uniform_formula')->default(0);
            $table->string('f_format', 255)->nullable();
            $table->string('f_formula', 255)->nullable();
            $table->string('rating_icon', 255)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('table_fields', function (Blueprint $table) {
            $table->dropColumn('is_uniform_formula');
            $table->dropColumn('f_format');
            $table->dropColumn('f_formula');
            $table->dropColumn('rating_icon');
        });
    }
}
