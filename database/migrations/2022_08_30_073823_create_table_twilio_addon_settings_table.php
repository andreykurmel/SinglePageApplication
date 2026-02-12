<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableTwilioAddonSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('table_twilio_addon_settings', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('table_id');
            $table->string('name', 128);
            $table->string('description', 255)->nullable();
            $table->unsignedInteger('twilio_active')->default(0);
            $table->unsignedInteger('acc_twilio_key_id')->nullable();
            $table->unsignedInteger('recipient_field_id')->nullable();
            $table->string('recipient_phones', 255)->nullable();
            $table->string('sms_body', 255)->nullable();
            $table->string('sms_send_time', 16)->default('now');
            $table->dateTime('sms_delay_time')->nullable();
            $table->unsignedInteger('sms_delay_record_fld_id')->nullable();
            $table->string('hash', 64)->nullable();
            $table->string('preview_background_header', 32)->nullable();
            $table->string('preview_background_body', 32)->nullable();
            $table->unsignedInteger('allow_resending')->nullable();
            $table->unsignedInteger('limit_row_group_id')->nullable();
            $table->unsignedInteger('prepared_sms')->nullable();
            $table->unsignedInteger('sent_sms')->nullable();

            $table->foreign('table_id', 'twas__table_id')
                ->references('id')
                ->on('tables')
                ->onDelete('cascade');

            $table->foreign('acc_twilio_key_id', 'twas__acc_twilio_key_id')
                ->references('id')
                ->on('user_api_keys')
                ->onDelete('set null');

            $table->foreign('recipient_field_id', 'twas__recipient_field_id')
                ->references('id')
                ->on('table_fields')
                ->onDelete('set null');

            $table->foreign('sms_delay_record_fld_id', 'twas__sms_delay_record_fld_id')
                ->references('id')
                ->on('table_fields')
                ->onDelete('set null');

            $table->foreign('limit_row_group_id', 'twas__limit_row_group_id')
                ->references('id')
                ->on('table_row_groups')
                ->onDelete('set null');
        });

        Schema::create('table_twilio_addon_history', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('table_twilio_addon_id');
            $table->unsignedInteger('row_id');
            $table->string('msg_type')->default('sms');
            $table->dateTime('send_date');
            $table->string('preview_from', 255);
            $table->string('preview_to', 255);
            $table->string('preview_body', 255);
            $table->text('preview_row');

            $table->foreign('table_twilio_addon_id', 'twah__table_twilio_addon_id')
                ->references('id')
                ->on('table_twilio_addon_settings')
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
        Schema::dropIfExists('table_twilio_addon_settings');
        Schema::dropIfExists('table_twilio_addon_history');
    }
}
