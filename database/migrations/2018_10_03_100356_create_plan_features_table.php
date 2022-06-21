<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePlanFeaturesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('plan_features', function (Blueprint $table) {
            $table->increments('id');
            $table->enum('type', ['user','plan']);
            $table->unsignedInteger('object_id');
            $table->string('q_tables', 20)->nullable();
            $table->string('row_table', 20)->nullable();
            $table->unsignedInteger('data_storage_backup')->nullable();
            $table->unsignedInteger('data_build')->nullable();
            $table->unsignedInteger('data_csv')->nullable();
            $table->unsignedInteger('data_mysql')->nullable();
            $table->unsignedInteger('data_remote')->nullable();
            $table->unsignedInteger('data_ref')->nullable();
            $table->unsignedInteger('unit_conversions')->nullable();
            $table->unsignedInteger('group_rows')->nullable();
            $table->unsignedInteger('group_columns')->nullable();
            $table->unsignedInteger('group_refs')->nullable();
            $table->unsignedInteger('link_type_record')->nullable();
            $table->unsignedInteger('link_type_web')->nullable();
            $table->unsignedInteger('link_type_app')->nullable();
            $table->unsignedInteger('ddl_ref')->nullable();
            $table->unsignedInteger('permission_col_view')->nullable();
            $table->unsignedInteger('permission_col_edit')->nullable();
            $table->unsignedInteger('permission_row_view')->nullable();
            $table->unsignedInteger('permission_row_edit')->nullable();
            $table->unsignedInteger('permission_row_add')->nullable();
            $table->unsignedInteger('permission_row_del')->nullable();
            $table->unsignedInteger('permission_views')->nullable();
            $table->unsignedInteger('permission_cond_format')->nullable();
            $table->unsignedInteger('dwn_print')->nullable();
            $table->unsignedInteger('dwn_csv')->nullable();
            $table->unsignedInteger('dwn_pdf')->nullable();
            $table->unsignedInteger('dwn_xls')->nullable();
            $table->unsignedInteger('dwn_json')->nullable();
            $table->unsignedInteger('dwn_xml')->nullable();

            $table->unsignedInteger('created_by')->nullable();
            $table->string('created_name')->nullable();
            $table->dateTime('created_on');
            $table->unsignedInteger('modified_by')->nullable();
            $table->string('modified_name')->nullable();
            $table->dateTime('modified_on');


            $table->foreign('created_by', 'plan_features_created_foreign')
                ->references('id')
                ->on('users')
                ->onDelete('set null');

            $table->foreign('modified_by', 'plan_features_modified_foreign')
                ->references('id')
                ->on('users')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('plan_features');
    }
}
