<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReportAddonTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('table_report_templates', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('table_id');
            $table->unsignedInteger('user_id');
            $table->string('name', 255);
            $table->string('doc_type', 32)->default('ms_word');//ms_word, gdoc
            $table->unsignedInteger('connected_cloud_id')->nullable();
            $table->string('template_source', 32)->default('Upload');
            $table->string('template_file', 512);
            $table->string('cloud_report_folder', 512)->nullable();
            $table->string('static_hash', 64)->nullable();

            $table->foreign('table_id', 'table_report_templates__table_id')
                ->references('id')
                ->on('tables')
                ->onDelete('cascade');
        });

        Schema::create('table_reports', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('table_id');
            $table->unsignedInteger('user_id');
            $table->string('report_name', 255);
            $table->string('report_data_range', 32)->default('0');
            $table->unsignedInteger('report_template_id');
            $table->string('report_file_formula', 512)->nullable();
            $table->unsignedInteger('report_field_id');

            $table->foreign('table_id', 'table_reports__table_id')
                ->references('id')
                ->on('tables')
                ->onDelete('cascade');

            $table->foreign('user_id', 'table_reports__user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->foreign('report_field_id', 'table_reports__report_field_id')
                ->references('id')
                ->on('table_fields')
                ->onDelete('cascade');
        });

        Schema::create('table_report_sources', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('table_report_id');
            $table->string('name', 255);
            $table->unsignedInteger('ref_link_id')->nullable();
            $table->string('description', 255)->nullable();

            $table->foreign('table_report_id', 'table_report_sources__table_report_id')
                ->references('id')
                ->on('table_reports')
                ->onDelete('cascade');
        });

        Schema::create('table_report_source_variables', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('table_report_source_id');
            $table->string('variable', 255);
            $table->string('variable_type', 64);//['field','rows','bi']
            $table->unsignedInteger('var_object_id')->nullable();
            $table->text('additional_attributes')->nullable();

            $table->foreign('table_report_source_id', 'trsv__table_report_source_id')
                ->references('id')
                ->on('table_report_sources')
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
        Schema::dropIfExists('table_report_source_variables');
        Schema::dropIfExists('table_report_sources');
        Schema::dropIfExists('table_reports');
        Schema::dropIfExists('table_report_templates');
    }
}
