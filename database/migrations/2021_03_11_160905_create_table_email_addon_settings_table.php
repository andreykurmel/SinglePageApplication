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
            $table->string('server_type', 32)->default('google');
            $table->string('smtp_key_mode', 32)->default('account');
            $table->string('google_email', 255)->nullable();
            $table->string('google_app_pass', 255)->nullable();
            $table->string('sendgrid_api_key', 512)->nullable();
            $table->unsignedInteger('acc_sendgrid_key_id')->nullable();
            $table->unsignedInteger('acc_google_key_id')->nullable();
            $table->string('sender_name', 255)->nullable();
            $table->string('sender_email', 255)->nullable();
            $table->tinyInteger('sender_email_isdif')->nullable();
            $table->unsignedInteger('sender_email_fld_id')->nullable();
            $table->string('sender_reply_to', 255)->nullable();
            $table->tinyInteger('sender_reply_to_isdif')->nullable();
            $table->unsignedInteger('sender_reply_to_fld_id')->nullable();
            $table->unsignedInteger('recipient_field_id')->nullable();
            $table->string('recipient_email', 255)->nullable();
            $table->string('email_subject', 255)->nullable();
            $table->text('email_body')->nullable();
            $table->string('hash', 64)->nullable();
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

            $table->foreign('limit_row_group_id', 'teas__limit_row_group_id')
                ->references('id')
                ->on('table_row_groups')
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
