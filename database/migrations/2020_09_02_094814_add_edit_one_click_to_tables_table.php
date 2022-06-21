<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddEditOneClickToTablesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tables', function (Blueprint $table) {
            $table->unsignedInteger('edit_one_click')->default(0);
            $table->unsignedInteger('vert_tb_bgcolor')->nullable();
            $table->unsignedInteger('vert_tb_floating')->nullable();
            $table->unsignedInteger('vert_tb_hdrwidth')->default(30);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tables', function (Blueprint $table) {
            $table->dropColumn('edit_one_click');
            $table->dropColumn('vert_tb_bgcolor');
            $table->dropColumn('vert_tb_floating');
            $table->dropColumn('vert_tb_hdrwidth');
        });
    }
}
