<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableDcrTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('table_data_requests', function (Blueprint $table) {
            //Main
            $table->increments('id');
            $table->unsignedInteger('table_id');
            $table->tinyInteger('active')->default(0);
            $table->string('name', 64)->nullable();
            $table->tinyInteger('is_template')->default(0);
            $table->string('link_hash', 255)->nullable();
            $table->string('dcr_hash', 128)->nullable();
            $table->string('pass', 128)->nullable();
            $table->string('qr_link', 255)->nullable();
            $table->string('row_request')->default('-1')->change();

            $table->foreign('table_id', 'table_data_requests_table_id')
                ->references('id')
                ->on('tables')
                ->onDelete('cascade');



            //Design/Overall tab
            $table->string('dcr_sec_background_by', 16)->default('color');
            $table->string('dcr_sec_scroll_style', 16)->default('scroll');
            $table->tinyInteger('dcr_sec_line_top')->default(1);
            $table->tinyInteger('dcr_sec_line_bot')->default(1);
            $table->string('dcr_sec_line_color', 16)->nullable();
            $table->unsignedInteger('dcr_sec_line_thick')->default(1);
            $table->string('dcr_sec_bg_top', 16)->nullable();
            $table->string('dcr_sec_bg_bot', 16)->nullable();
            $table->string('dcr_sec_bg_img', 255)->nullable();
            $table->string('dcr_sec_bg_img_fit', 16)->default('Width');



            //Design/Title tab
            $table->string('dcr_title', 64)->nullable();
            $table->integer('dcr_title_width')->nullable();
            $table->integer('dcr_title_height')->nullable();
            $table->string('dcr_title_font_type', 64)->nullable();
            $table->integer('dcr_title_font_size')->nullable();
            $table->string('dcr_title_font_color', 16)->nullable();
            $table->string('dcr_title_font_style', 128)->nullable();
            $table->string('dcr_title_bg_img', 255)->nullable();
            $table->string('dcr_title_bg_fit', 16)->nullable();
            $table->string('dcr_title_bg_color', 16)->nullable();
            $table->string('dcr_title_background_by', 16)->default('color');



            //Design/Form tab
            $table->string('dcr_form_line_type', 16)->default('line');
            $table->tinyInteger('dcr_form_line_top')->default(1);
            $table->tinyInteger('dcr_form_line_bot')->default(1);
            $table->tinyInteger('dcr_form_line_thick')->default(1);
            $table->tinyInteger('dcr_form_line_radius')->default(10);
            $table->string('dcr_form_line_color', 16)->nullable();
            $table->string('dcr_form_bg_color', 16)->nullable();
            $table->unsignedInteger('dcr_form_transparency')->default(0);
            $table->string('dcr_form_message', 512)->nullable();
            $table->string('dcr_form_message_font', 64)->nullable();
            $table->integer('dcr_form_message_size')->nullable();
            $table->string('dcr_form_message_color', 16)->nullable();
            $table->string('dcr_form_message_style', 128)->nullable();
            $table->integer('dcr_form_width')->nullable();
            $table->tinyInteger('dcr_form_shadow')->default(0);
            $table->string('dcr_form_shadow_color', 16)->nullable();
            $table->string('dcr_form_shadow_dir', 16)->default('BR');
            $table->integer('dcr_form_line_height')->default('14');
            $table->integer('dcr_form_font_size')->default('12');



            //Action & Status tab
            $table->tinyInteger('one_per_submission')->default(0);
            $table->unsignedInteger('dcr_record_status_id')->nullable();
            $table->unsignedInteger('dcr_record_url_field_id')->nullable();
            $table->tinyInteger('dcr_record_allow_unfinished')->nullable();
            $table->unsignedInteger('dcr_record_visibility_id')->nullable();
            $table->unsignedInteger('dcr_record_editability_id')->nullable();
            $table->unsignedInteger('dcr_record_visibility_def')->nullable();
            $table->unsignedInteger('dcr_record_editability_def')->nullable();
            $table->unsignedInteger('dcr_record_save_visibility_def')->nullable();
            $table->unsignedInteger('dcr_record_save_editability_def')->nullable();
            $table->tinyInteger('stored_row_protection')->nullable();
            $table->unsignedInteger('stored_row_pass_id')->nullable();

            $table->foreign('dcr_record_status_id', 'table_data_requests__dcr_record_status_id')
                ->references('id')
                ->on('table_fields')
                ->onDelete('set null');
            $table->foreign('dcr_record_url_field_id', 'table_data_requests__dcr_record_url_field_id')
                ->references('id')
                ->on('table_fields')
                ->onDelete('set null');
            $table->foreign('dcr_record_visibility_id', 'table_data_requests__dcr_record_visibility_id')
                ->references('id')
                ->on('table_fields')
                ->onDelete('set null');
            $table->foreign('dcr_record_editability_id', 'table_data_requests__dcr_record_editability_id')
                ->references('id')
                ->on('table_fields')
                ->onDelete('set null');
            $table->foreign('stored_row_pass_id', 'table_data_requests__stored_row_pass_id')
                ->references('id')
                ->on('table_fields')
                ->onDelete('set null');



            //Notifications/Submission tab
            $table->string('dcr_confirm_msg', 255)->nullable();
            $table->string('dcr_unique_msg', 255)->nullable();
            $table->unsignedInteger('dcr_email_field_id')->nullable();
            $table->unsignedInteger('dcr_cc_email_field_id')->nullable();
            $table->unsignedInteger('dcr_bcc_email_field_id')->nullable();
            $table->string('dcr_email_field_static', 255)->nullable();
            $table->string('dcr_cc_email_field_static', 255)->nullable();
            $table->string('dcr_bcc_email_field_static', 255)->nullable();
            $table->string('dcr_email_subject', 255)->nullable();
            $table->string('dcr_email_message', 512)->nullable();
            $table->string('dcr_email_format', 32)->default('table');
            $table->unsignedInteger('dcr_email_col_group_id')->nullable();
            $table->unsignedInteger('dcr_addressee_field_id')->nullable();
            $table->string('dcr_addressee_txt', 255)->nullable();

            $table->foreign('dcr_addressee_field_id', 'table_data_requests__dcr_addressee_field_id')
                ->references('id')
                ->on('table_fields')
                ->onDelete('set null');

            $table->foreign('dcr_email_field_id', 'table_data_requests__dcr_email_field_id')
                ->references('id')
                ->on('table_fields')
                ->onDelete('set null');

            $table->foreign('dcr_email_col_group_id', 'table_data_requests__dcr_email_col_group_id')
                ->references('id')
                ->on('table_column_groups')
                ->onDelete('set null');



            //Notifications/Saving tab
            $table->unsignedInteger('dcr_save_email_field_id')->nullable();
            $table->unsignedInteger('dcr_save_cc_email_field_id')->nullable();
            $table->unsignedInteger('dcr_save_bcc_email_field_id')->nullable();
            $table->unsignedInteger('dcr_save_addressee_field_id')->nullable();
            $table->unsignedInteger('dcr_save_email_col_group_id')->nullable();
            $table->string('dcr_save_confirm_msg', 255)->nullable();
            $table->string('dcr_save_unique_msg', 255)->nullable();
            $table->string('dcr_save_email_field_static', 255)->nullable();
            $table->string('dcr_save_cc_email_field_static', 255)->nullable();
            $table->string('dcr_save_bcc_email_field_static', 255)->nullable();
            $table->string('dcr_save_email_subject', 255)->nullable();
            $table->string('dcr_save_addressee_txt', 255)->nullable();
            $table->string('dcr_save_email_message', 512)->nullable();
            $table->string('dcr_save_email_format', 32)->default('table');

            $table->foreign('dcr_save_email_field_id', 'table_data_requests__dcr_save_email_field_id')
                ->references('id')
                ->on('table_fields')
                ->onDelete('set null');

            $table->foreign('dcr_save_addressee_field_id', 'table_data_requests__dcr_save_addressee_field_id')
                ->references('id')
                ->on('table_fields')
                ->onDelete('set null');

            $table->foreign('dcr_save_email_col_group_id', 'table_data_requests__dcr_save_email_col_group_id')
                ->references('id')
                ->on('table_column_groups')
                ->onDelete('set null');



            //Notifications/Updating tab
            $table->unsignedInteger('dcr_upd_email_field_id')->nullable();
            $table->unsignedInteger('dcr_upd_cc_email_field_id')->nullable();
            $table->unsignedInteger('dcr_upd_bcc_email_field_id')->nullable();
            $table->unsignedInteger('dcr_upd_addressee_field_id')->nullable();
            $table->unsignedInteger('dcr_upd_email_col_group_id')->nullable();
            $table->string('dcr_upd_confirm_msg', 255)->nullable();
            $table->string('dcr_upd_unique_msg', 255)->nullable();
            $table->string('dcr_upd_email_field_static', 255)->nullable();
            $table->string('dcr_upd_cc_email_field_static', 255)->nullable();
            $table->string('dcr_upd_bcc_email_field_static', 255)->nullable();
            $table->string('dcr_upd_email_subject', 255)->nullable();
            $table->string('dcr_upd_addressee_txt', 255)->nullable();
            $table->string('dcr_upd_email_message', 512)->nullable();
            $table->string('dcr_upd_email_format', 32)->default('table');

            $table->foreign('dcr_upd_email_field_id', 'table_data_requests__dcr_upd_email_field_id')
                ->references('id')
                ->on('table_fields')
                ->onDelete('set null');

            $table->foreign('dcr_upd_addressee_field_id', 'table_data_requests__dcr_upd_addressee_field_id')
                ->references('id')
                ->on('table_fields')
                ->onDelete('set null');

            $table->foreign('dcr_upd_email_col_group_id', 'table_data_requests__dcr_upd_email_col_group_id')
                ->references('id')
                ->on('table_column_groups')
                ->onDelete('set null');
        });



        Schema::create('table_data_requests_2_table_column_groups', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('table_data_requests_id');
            $table->unsignedInteger('table_column_group_id');
            $table->tinyInteger('view')->default(0);
            $table->tinyInteger('edit')->default(0);

            $table->foreign('table_data_requests_id', 'tdr_2_tcg_table_data_requests_id')
                ->references('id')
                ->on('table_data_requests')
                ->onDelete('cascade');

            $table->foreign('table_column_group_id', 'tdr_2_tcg_table_column_group_id')
                ->references('id')
                ->on('table_column_groups')
                ->onDelete('cascade');
        });



        Schema::create('table_data_requests_def_fields', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('table_data_requests_id');
            $table->unsignedInteger('table_field_id');
            $table->string('default', 255);

            $table->foreign('table_data_requests_id', 'tdr_def_fields_table_data_requests_id')
                ->references('id')
                ->on('table_data_requests')
                ->onDelete('cascade');

            $table->foreign('table_field_id', 'tdr_def_fields_table_field_id')
                ->references('id')
                ->on('table_fields')
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
        Schema::dropIfExists('table_data_requests_2_table_column_groups');
        Schema::dropIfExists('table_data_requests_def_fields');
        Schema::dropIfExists('table_data_requests');
    }
}
