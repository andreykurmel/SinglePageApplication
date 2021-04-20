<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableRowGroupConditionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('table_row_group_conditions', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('table_row_group_id');
            $table->string('logic_operator', 20)->nullable();
            $table->unsignedInteger('table_field_id');
            $table->string('compared_operator', 20);
            $table->string('compared_value', 50)->nullable();

            $table->unsignedInteger('created_by')->nullable();
            $table->string('created_name')->nullable();
            $table->dateTime('created_on');
            $table->unsignedInteger('modified_by')->nullable();
            $table->string('modified_name')->nullable();
            $table->dateTime('modified_on');

            $table->foreign('table_row_group_id')->references('id')->on('table_row_groups')->onDelete('cascade');
            $table->foreign('table_field_id')->references('id')->on('table_fields')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('modified_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('table_row_group_conditions');
    }
}
