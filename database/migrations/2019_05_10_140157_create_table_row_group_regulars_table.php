<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableRowGroupRegularsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('table_row_group_regulars', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('table_row_group_id');
            $table->string('field_value');

            $table->foreign('table_row_group_id', 'table_row_group_regulars__table_row_group_id')
                ->references('id')
                ->on('table_row_groups')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('table_row_group_regulars');
    }
}
