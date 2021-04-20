<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMoreDcrsToTablePermission extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('table_permissions', function (Blueprint $table) {
            $table->unsignedInteger('dcr_form_line_radius')->default(10);
            $table->unsignedInteger('dcr_save_email_field_id')->nullable();
            $table->unsignedInteger('dcr_save_addressee_field_id')->nullable();
            $table->unsignedInteger('dcr_save_email_col_group_id')->nullable();
            $table->string('dcr_save_confirm_msg', 255)->nullable();
            $table->string('dcr_save_email_field_static', 255)->nullable();
            $table->string('dcr_save_email_subject', 255)->nullable();
            $table->string('dcr_save_addressee_txt', 255)->nullable();
            $table->string('dcr_save_email_message', 512)->nullable();
            $table->string('dcr_save_email_format', 32)->default('table');

            $table->foreign('dcr_save_email_field_id', 'table_permissions__dcr_save_email_field_id')
                ->references('id')
                ->on('table_fields')
                ->onDelete('set null');
            $table->foreign('dcr_save_addressee_field_id', 'table_permissions__dcr_save_addressee_field_id')
                ->references('id')
                ->on('table_fields')
                ->onDelete('set null');
            $table->foreign('dcr_save_email_col_group_id', 'table_permissions__dcr_save_email_col_group_id')
                ->references('id')
                ->on('table_column_groups')
                ->onDelete('set null');

            $table->unsignedInteger('dcr_upd_email_field_id')->nullable();
            $table->unsignedInteger('dcr_upd_addressee_field_id')->nullable();
            $table->unsignedInteger('dcr_upd_email_col_group_id')->nullable();
            $table->string('dcr_upd_confirm_msg', 255)->nullable();
            $table->string('dcr_upd_email_field_static', 255)->nullable();
            $table->string('dcr_upd_email_subject', 255)->nullable();
            $table->string('dcr_upd_addressee_txt', 255)->nullable();
            $table->string('dcr_upd_email_message', 512)->nullable();
            $table->string('dcr_upd_email_format', 32)->default('table');

            $table->foreign('dcr_upd_email_field_id', 'table_permissions__dcr_upd_email_field_id')
                ->references('id')
                ->on('table_fields')
                ->onDelete('set null');
            $table->foreign('dcr_upd_addressee_field_id', 'table_permissions__dcr_upd_addressee_field_id')
                ->references('id')
                ->on('table_fields')
                ->onDelete('set null');
            $table->foreign('dcr_upd_email_col_group_id', 'table_permissions__dcr_upd_email_col_group_id')
                ->references('id')
                ->on('table_column_groups')
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
        Schema::table('table_permissions', function (Blueprint $table) {
            $table->dropForeign('table_permissions__dcr_save_email_field_id');
            $table->dropForeign('table_permissions__dcr_save_addressee_field_id');
            $table->dropForeign('table_permissions__dcr_save_email_col_group_id');
            $table->dropColumn('dcr_save_email_field_id');
            $table->dropColumn('dcr_save_email_field_static');
            $table->dropColumn('dcr_save_email_col_group_id');
            $table->dropColumn('dcr_save_confirm_msg');
            $table->dropColumn('dcr_save_email_field_static');
            $table->dropColumn('dcr_save_email_subject');
            $table->dropColumn('dcr_save_addressee_txt');
            $table->dropColumn('dcr_save_email_message');
            $table->dropColumn('dcr_save_email_format');
            $table->dropColumn('dcr_form_line_radius');

            $table->dropForeign('table_permissions__dcr_upd_email_field_id');
            $table->dropForeign('table_permissions__dcr_upd_addressee_field_id');
            $table->dropForeign('table_permissions__dcr_upd_email_col_group_id');
            $table->dropColumn('dcr_upd_email_field_id');
            $table->dropColumn('dcr_upd_email_field_static');
            $table->dropColumn('dcr_upd_email_col_group_id');
            $table->dropColumn('dcr_upd_confirm_msg');
            $table->dropColumn('dcr_upd_email_field_static');
            $table->dropColumn('dcr_upd_email_subject');
            $table->dropColumn('dcr_upd_addressee_txt');
            $table->dropColumn('dcr_upd_email_message');
            $table->dropColumn('dcr_upd_email_format');
        });
    }
}
