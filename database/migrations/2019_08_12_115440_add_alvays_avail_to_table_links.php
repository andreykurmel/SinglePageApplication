<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAlvaysAvailToTableLinks extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('table_field_links', function (Blueprint $table) {
            $table->tinyInteger('always_available')->nullable();
            $table->tinyInteger('editability_rced_fields')->default(0);
            $table->tinyInteger('lnk_dcr_permission_id')->nullable();
            $table->tinyInteger('lnk_srv_permission_id')->nullable();
            $table->tinyInteger('lnk_mrv_permission_id')->nullable();
            $table->tinyInteger('link_export_json_drill')->default(1);
            $table->unsignedInteger('json_import_field_id')->nullable();
            $table->unsignedInteger('json_export_field_id')->nullable();
            $table->unsignedInteger('eri_parser_file_id')->nullable();
            $table->unsignedInteger('eri_writer_file_id')->nullable();
            $table->unsignedInteger('eri_parser_link_id')->nullable();
            $table->string('eri_writer_filename_fields', 255)->nullable();
            $table->tinyInteger('eri_writer_filename_year')->default(0);
            $table->tinyInteger('eri_writer_filename_time')->default(0);
            $table->tinyInteger('eri_remove_prev_records')->default(0);
            $table->string('da_loading_type', 32)->nullable();
            $table->unsignedInteger('da_loading_gemini_key_id')->nullable();
            $table->unsignedInteger('da_loading_image_field_id')->nullable();
            $table->unsignedInteger('da_loading_output_table_id')->nullable();
            $table->tinyInteger('da_loading_remove_prev_rec')->default(0);
            $table->unsignedInteger('mto_dal_pdf_doc_field_id')->nullable();
            $table->unsignedInteger('mto_dal_pdf_output_table_id')->nullable();
            $table->tinyInteger('mto_dal_pdf_remove_prev_rec')->default(0);
            $table->unsignedInteger('mto_geom_doc_field_id')->nullable();
            $table->unsignedInteger('mto_geom_output_table_id')->nullable();
            $table->tinyInteger('mto_geom_remove_prev_rec')->default(0);
            $table->unsignedInteger('ai_extract_output_table_id')->nullable();
            $table->tinyInteger('ai_extract_remove_prev_rec')->default(0);
            $table->unsignedInteger('ai_extract_doc_field_id')->nullable();
            $table->unsignedInteger('ai_extract_ai_id')->nullable();
            $table->tinyInteger('json_export_filename_table')->default(1);
            $table->tinyInteger('json_export_filename_link')->default(1);
            $table->string('json_export_filename_fields', 255)->nullable();
            $table->tinyInteger('json_export_filename_year')->default(1);
            $table->tinyInteger('json_export_filename_time')->default(1);
            $table->tinyInteger('json_auto_export')->default(0);
            $table->tinyInteger('json_auto_remove_after_export')->default(0);
            $table->text('link_export_drilled_fields')->nullable();
            $table->unsignedInteger('smart_select_source_field_id')->nullable();
            $table->unsignedInteger('smart_select_target_field_id')->nullable();
            $table->string('smart_select_data_range', 16)->default('0');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('table_field_links', function (Blueprint $table) {
            $table->dropColumn('always_available');
            $table->dropColumn('editability_rced_fields');
            $table->dropColumn('lnk_dcr_permission_id');
            $table->dropColumn('lnk_srv_permission_id');
            $table->dropColumn('lnk_mrv_permission_id');
            $table->dropColumn('link_export_json_drill');
            $table->dropColumn('json_import_field_id');
            $table->dropColumn('json_export_field_id');
            $table->dropColumn('eri_parser_file_id');
            $table->dropColumn('eri_writer_file_id');
            $table->dropColumn('eri_parser_link_id');
            $table->dropColumn('eri_writer_filename_fields');
            $table->dropColumn('eri_writer_filename_year');
            $table->dropColumn('eri_writer_filename_time');
            $table->dropColumn('eri_remove_prev_records');
            $table->dropColumn('da_loading_type');
            $table->dropColumn('da_loading_gemini_key_id');
            $table->dropColumn('da_loading_image_field_id');
            $table->dropColumn('da_loading_output_table_id');
            $table->dropColumn('da_loading_remove_prev_rec');
            $table->dropColumn('mto_dal_pdf_doc_field_id');
            $table->dropColumn('mto_dal_pdf_output_table_id');
            $table->dropColumn('mto_dal_pdf_remove_prev_rec');
            $table->dropColumn('mto_geom_doc_field_id');
            $table->dropColumn('mto_geom_output_table_id');
            $table->dropColumn('mto_geom_remove_prev_rec');
            $table->dropColumn('ai_extract_output_table_id');
            $table->dropColumn('ai_extract_remove_prev_rec');
            $table->dropColumn('ai_extract_doc_field_id');
            $table->dropColumn('ai_extract_ai_id');
            $table->dropColumn('json_export_filename_table');
            $table->dropColumn('json_export_filename_link');
            $table->dropColumn('json_export_filename_fields');
            $table->dropColumn('json_export_filename_year');
            $table->dropColumn('json_export_filename_time');
            $table->dropColumn('json_auto_export');
            $table->dropColumn('json_auto_remove_after_export');
            $table->dropColumn('link_export_drilled_fields');
        });
    }
}
