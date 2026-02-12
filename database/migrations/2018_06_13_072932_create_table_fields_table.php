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
            $table->string('prev_input_type', 50)->nullable();
            $table->string('input_type', 50)->default('Input');
            $table->unsignedInteger('prev_ddl_id')->nullable();
            $table->unsignedInteger('ddl_id')->nullable();
            $table->string('f_type', 50)->default('String');
            $table->string('f_size', 20)->default(255);
            $table->string('f_default')->nullable();
            $table->tinyInteger('f_required')->default(0);
            $table->unsignedInteger('fetch_source_id')->nullable();
            $table->unsignedInteger('fetch_by_row_cloud_id')->nullable();
            $table->unsignedInteger('fetch_one_cloud_id')->nullable();
            $table->tinyInteger('fetch_uploading')->default(0);
            $table->string('link_type', 20)->nullable();
            $table->unsignedInteger('mirror_rc_id')->nullable();
            $table->unsignedInteger('mirror_field_id')->nullable();
            $table->string('mirror_part', 16)->default('show');
            $table->tinyInteger('mirror_one_value')->default(0);
            $table->tinyInteger('mirror_editable')->default(0);
            $table->string('mirror_edit_component', 128)->default('Input');
            $table->string('shared_ddlref_ids', 128)->nullable();

            $table->tinyInteger('is_inherited_tree')->default(0);
            $table->tinyInteger('has_copy_prefix')->default(0);
            $table->string('copy_prefix_value', 128)->nullable();
            $table->tinyInteger('has_copy_suffix')->default(0);
            $table->string('copy_suffix_value', 128)->nullable();
            $table->tinyInteger('has_datetime_suffix')->default(0);

            $table->unsignedInteger('created_by')->nullable();
            $table->string('created_name')->nullable();
            $table->dateTime('created_on');
            $table->unsignedInteger('modified_by')->nullable();
            $table->string('modified_name')->nullable();
            $table->dateTime('modified_on');
            $table->string('row_hash', 32)->nullable();

            $table->foreign('table_id')->references('id')->on('tables')->onDelete('cascade');
            $table->foreign('unit_ddl_id')->references('id')->on('ddl')->onDelete('set null');
            $table->foreign('ddl_id')->references('id')->on('ddl')->onDelete('set null');
            $table->foreign('fetch_source_id')->references('id')->on('table_fields')->onDelete('set null');
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
