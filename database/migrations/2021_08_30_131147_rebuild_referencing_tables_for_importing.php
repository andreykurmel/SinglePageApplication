<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RebuildReferencingTablesForImporting extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('table_references');

        Schema::create('table_import_references', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 64)->nullable();
            $table->unsignedInteger('table_id');
            $table->unsignedInteger('ref_table_id');
            $table->unsignedInteger('ref_row_group_id')->nullable();

            $table->foreign('table_id')->references('id')->on('tables')->onDelete('cascade');
            $table->foreign('ref_table_id')->references('id')->on('tables')->onDelete('cascade');
            $table->foreign('ref_row_group_id')->references('id')->on('table_row_groups')->onDelete('cascade');
        });

        Schema::create('table_import_reference_corrs', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('import_ref_id');
            $table->unsignedInteger('table_field_id');
            $table->unsignedInteger('ref_field_id');

            $table->foreign('import_ref_id')->references('id')->on('table_import_references')->onDelete('cascade');
            $table->foreign('table_field_id')->references('id')->on('table_fields')->onDelete('cascade');
            $table->foreign('ref_field_id')->references('id')->on('table_fields')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::create('table_references', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 64)->nullable();
            $table->unsignedInteger('table_id');
            $table->unsignedInteger('table_field_id')->nullable();
            $table->unsignedInteger('ref_table_id');
            $table->unsignedInteger('ref_field_id')->nullable();
            $table->unsignedInteger('ref_row_group_id')->nullable();

            $table->unsignedInteger('created_by')->nullable();
            $table->string('created_name')->nullable();
            $table->dateTime('created_on');
            $table->unsignedInteger('modified_by')->nullable();
            $table->string('modified_name')->nullable();
            $table->dateTime('modified_on');

            $table->foreign('table_id')->references('id')->on('tables')->onDelete('cascade');
            $table->foreign('table_field_id')->references('id')->on('table_fields')->onDelete('cascade');
            $table->foreign('ref_table_id')->references('id')->on('tables')->onDelete('cascade');
            $table->foreign('ref_field_id')->references('id')->on('table_fields')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('modified_by')->references('id')->on('users')->onDelete('set null');
        });

        Schema::dropIfExists('table_import_reference_corrs');
        Schema::dropIfExists('table_import_references');
    }
}
