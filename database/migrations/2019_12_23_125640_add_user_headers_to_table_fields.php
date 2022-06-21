<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddUserHeadersToTableFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('table_fields', function (Blueprint $table) {
            $table->string('unit_display', 50)->nullable();
            $table->string('filter_type', 16)->default('value');
            $table->string('col_align', 32)->default('center');
            $table->string('notes')->nullable();
            $table->unsignedInteger('width')->default(100);
            $table->unsignedInteger('min_width')->default(10);
            $table->unsignedInteger('max_width')->default(500);
            $table->unsignedInteger('order')->default(0);
            $table->unsignedInteger('is_showed')->default(1);
            $table->unsignedInteger('web')->default(1);
            $table->unsignedInteger('filter')->default(0);
            $table->unsignedInteger('popup_header')->default(0);
            $table->unsignedInteger('is_floating')->default(0);
            $table->unsignedInteger('show_zeros')->default(1);
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
            $table->dropColumn('unit_display');
            $table->dropColumn('filter_type');
            $table->dropColumn('col_align');
            $table->dropColumn('notes');
            $table->dropColumn('width');
            $table->dropColumn('min_width');
            $table->dropColumn('max_width');
            $table->dropColumn('order');
            $table->dropColumn('is_showed');
            $table->dropColumn('web');
            $table->dropColumn('filter');
            $table->dropColumn('popup_header');
            $table->dropColumn('is_floating');
            $table->dropColumn('unit_display');
            $table->dropColumn('show_zeros');
        });
    }
}
