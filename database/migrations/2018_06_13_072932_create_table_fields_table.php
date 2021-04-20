<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableFieldsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('table_fields', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('table_id');
            $table->string('field');
            $table->string('name');
            $table->tinyInteger('web')->default(1);
            $table->tinyInteger('filter')->default(0);
            $table->string('unit', 50)->nullable();
            $table->unsignedInteger('unit_ddl_id')->nullable();
            $table->tinyInteger('header_unit_ddl')->default(0);
            $table->string('input_type', 50)->default('Input');
            $table->unsignedInteger('ddl_id')->nullable();
            $table->string('f_type', 50)->default('String');
            $table->string('f_size', 20)->default(255);
            $table->string('f_default')->nullable();
            $table->tinyInteger('f_required')->default(0);
            $table->string('link_type', 20)->nullable();
            $table->unsignedInteger('table_ref_condition_id')->nullable();

            $table->unsignedInteger('created_by')->nullable();
            $table->string('created_name')->nullable();
            $table->dateTime('created_on');
            $table->unsignedInteger('modified_by')->nullable();
            $table->string('modified_name')->nullable();
            $table->dateTime('modified_on');

            $table->foreign('table_id')->references('id')->on('tables')->onDelete('cascade');
            $table->foreign('unit_ddl_id')->references('id')->on('ddl')->onDelete('set null');
            $table->foreign('ddl_id')->references('id')->on('ddl')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('modified_by')->references('id')->on('users')->onDelete('set null');

            $table->index('field');
            $table->index('web');
            $table->index('filter');

            $table->unique(['table_id', 'field']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('table_fields');
    }
}
