<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDdlReferencesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ddl_references', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('ddl_id');
            $table->unsignedInteger('table_ref_condition_id');
            $table->unsignedInteger('target_field_id');
            $table->tinyInteger('is_distinctive')->default(1);
            $table->string('notes')->nullable();

            $table->unsignedInteger('created_by')->nullable();
            $table->string('created_name')->nullable();
            $table->dateTime('created_on');
            $table->unsignedInteger('modified_by')->nullable();
            $table->string('modified_name')->nullable();
            $table->dateTime('modified_on');

            $table->foreign('ddl_id')->references('id')->on('ddl')->onDelete('cascade');
            $table->foreign('table_ref_condition_id')->references('id')->on('table_ref_conditions')->onDelete('cascade');
            $table->foreign('target_field_id')->references('id')->on('table_fields')->onDelete('cascade');
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
        Schema::dropIfExists('ddl_references');
    }
}
