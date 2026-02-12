<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableEmailAddonSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('table_email_addon_settings', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('table_id');
            $table->string('name', 128);
            $table->string('description', 255)->nullable();
            $table->unsignedInteger('email_active')->default(0);
            $table->string('server_type', 32)->default('google');
            $table->string('smtp_key_mode', 32)->default('account');
            $table->string('google_email', 255)->nullable();
            $table->string('google_app_pass', 255)->nullable();
            $table->string('sendgrid_api_key', 512)->nullable();
            $table->unsignedInteger('acc_sendgrid_key_id')->nullable();
            $table->unsignedInteger('acc_google_key_id')->nullable();
            $table->unsignedInteger('preview_listing_id')->nullable();
            $table->string('sender_name', 255)->nullable();
            $table->string('sender_email', 255)->nullable();
            $table->tinyInteger('sender_email_isdif')->nullable();
            $table->unsignedInteger('sender_email_fld_id')->nullable();
            $table->string('sender_reply_to', 255)->nullable();
            $table->tinyInteger('sender_reply_to_isdif')->nullable();
            $table->unsignedInteger('sender_reply_to_fld_id')->nullable();
            $table->unsignedInteger('recipient_field_id')->nullable();
            $table->unsignedInteger('cc_recipient_field_id')->nullable();
            $table->unsignedInteger('bcc_recipient_field_id')->nullable();
            $table->string('recipient_email', 255)->nullable();
            $table->string('cc_recipient_email', 255)->nullable();
            $table->string('bcc_recipient_email', 255)->nullable();
            $table->string('email_subject', 255)->nullable();
            $table->text('email_body')->nullable();
            $table->string('email_send_time', 16)->default('now');
            $table->dateTime('email_delay_time')->nullable();
            $table->unsignedInteger('email_delay_record_fld_id')->nullable();
            $table->string('email_link_width_type', 32)->default('full');
            $table->unsignedInteger('email_link_width_size')->default(100);
            $table->string('email_link_align', 32)->default('left');
            $table->string('email_link_viewtype', 32)->default('table');
            $table->string('hash', 64)->nullable();
            $table->string('email_background_header', 32)->nullable();
            $table->string('email_background_body', 32)->nullable();
            $table->unsignedInteger('allow_resending')->nullable();
            $table->unsignedInteger('field_id_attachments')->nullable();
            $table->unsignedInteger('limit_row_group_id')->nullable();
            $table->unsignedInteger('prepared_emails')->nullable();
            $table->unsignedInteger('sent_emails')->nullable();

            $table->foreign('table_id', 'teas__table_id')
                ->references('id')
                ->on('tables')
                ->onDelete('cascade');

            $table->foreign('acc_sendgrid_key_id', 'teas__acc_sendgrid_key_id')
                ->references('id')
                ->on('user_api_keys')
                ->onDelete('set null');

            $table->foreign('acc_google_key_id', 'teas__acc_google_key_id')
                ->references('id')
                ->on('user_email_accounts')
                ->onDelete('set null');

            $table->foreign('sender_email_fld_id', 'teas__sender_email_fld_id')
                ->references('id')
                ->on('table_fields')
                ->onDelete('set null');

            $table->foreign('sender_reply_to_fld_id', 'teas__sender_reply_to_fld_id')
                ->references('id')
                ->on('table_fields')
                ->onDelete('set null');

            $table->foreign('recipient_field_id', 'teas__recipient_field_id')
                ->references('id')
                ->on('table_fields')
                ->onDelete('set null');

            $table->foreign('field_id_attachments', 'teas__field_id_attachments')
                ->references('id')
                ->on('table_fields')
                ->onDelete('set null');

            $table->foreign('limit_row_group_id', 'teas__limit_row_group_id')
                ->references('id')
                ->on('table_row_groups')
                ->onDelete('set null');
        });

        Schema::table('table_alerts', function (Blueprint $table) {
            $table->foreign('on_added_row_group_id', 'table_alerts__on_added_row_group_id')
                ->references('id')
                ->on('table_row_groups')
                ->onDelete('cascade');
            $table->foreign('on_updated_row_group_id', 'table_alerts__on_updated_row_group_id')
                ->references('id')
                ->on('table_row_groups')
                ->onDelete('cascade');
            $table->foreign('on_deleted_row_group_id', 'table_alerts__on_deleted_row_group_id')
                ->references('id')
                ->on('table_row_groups')
                ->onDelete('cascade');
            $table->foreign('automation_email_addon_id', 'table_alerts__automation_email_addon_id')
                ->references('id')
                ->on('table_email_addon_settings')
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
        Schema::dropIfExists('table_email_addon_settings');
    }
}
