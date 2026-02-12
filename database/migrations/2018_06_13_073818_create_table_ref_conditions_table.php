<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableRefConditionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('table_ref_conditions', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('table_id');
            $table->unsignedInteger('ref_table_id');
            $table->unsignedInteger('base_refcond_id')->nullable();
            $table->unsignedInteger('incoming_allow')->default(1);
            $table->unsignedInteger('is_system')->default(0);
            $table->tinyInteger('rc_static')->default(0);
            $table->string('name', 50);
            $table->string('notes')->nullable();
            $table->integer('row_order')->default(0);

            $table->unsignedInteger('created_by')->nullable();
            $table->string('created_name')->nullable();
            $table->dateTime('created_on');
            $table->unsignedInteger('modified_by')->nullable();
            $table->string('modified_name')->nullable();
            $table->dateTime('modified_on');

            $table->foreign('table_id', 'table_ref_conditions__table_id')->references('id')->on('tables')->onDelete('cascade');
            $table->foreign('ref_table_id', 'table_ref_conditions__ref_table_id')->references('id')->on('tables')->onDelete('cascade');
            $table->foreign('base_refcond_id', 'table_ref_conditions__base_refcond_id')->references('id')->on('table_ref_conditions')->onDelete('set null');
            $table->foreign('created_by', 'table_ref_conditions__created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('modified_by', 'table_ref_conditions__modified_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('table_ref_conditions');
    }
}
