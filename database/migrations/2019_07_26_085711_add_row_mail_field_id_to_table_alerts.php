<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddRowMailFieldIdToTableAlerts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('table_alerts', function (Blueprint $table) {
            $table->string('cc_recipients', 512)->nullable();
            $table->string('bcc_recipients', 512)->nullable();
            $table->unsignedInteger('mail_delay_hour')->nullable();
            $table->unsignedInteger('mail_delay_min')->nullable();
            $table->unsignedInteger('mail_delay_sec')->nullable();
            $table->unsignedInteger('row_mail_field_id')->nullable();
            $table->unsignedInteger('cc_row_mail_field_id')->nullable();
            $table->unsignedInteger('bcc_row_mail_field_id')->nullable();

            $table->unsignedInteger('row_sms_field_id')->nullable();
            $table->string('sms_recipients', 512)->nullable();
            $table->string('sms_body', 512)->nullable();
            $table->unsignedInteger('sms_delay_hour')->nullable();
            $table->unsignedInteger('sms_delay_min')->nullable();
            $table->unsignedInteger('sms_delay_sec')->nullable();

            $table->unsignedInteger('click_field_id')->nullable();
            $table->string('click_new_value', 255)->nullable();
            $table->string('click_success_message', 255)->nullable();
            $table->string('click_introduction', 255)->nullable();
            $table->tinyInteger('notif_email_add_tabledata')->nullable();
            $table->tinyInteger('notif_email_add_clicklink')->nullable();

            $table->foreign('row_mail_field_id', 'table_alerts__row_mail_field_id')
                ->references('id')
                ->on('table_fields')
                ->onDelete('set null');
            $table->foreign('cc_row_mail_field_id', 'table_alerts__cc_row_mail_field_id')
                ->references('id')
                ->on('table_fields')
                ->onDelete('set null');
            $table->foreign('bcc_row_mail_field_id', 'table_alerts__bcc_row_mail_field_id')
                ->references('id')
                ->on('table_fields')
                ->onDelete('set null');

            $table->foreign('click_field_id', 'table_alerts__click_field_id')
                ->references('id')
                ->on('table_fields')
                ->onDelete('set null');

            $table->foreign('row_sms_field_id', 'table_alerts__row_sms_field_id')
                ->references('id')
                ->on('table_fields')
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
        Schema::table('table_alerts', function (Blueprint $table) {
            $table->dropForeign('table_alerts__row_mail_field_id');
            $table->dropForeign('table_alerts__cc_row_mail_field_id');
            $table->dropForeign('table_alerts__bcc_row_mail_field_id');
            $table->dropColumn('cc_recipients');
            $table->dropColumn('bcc_recipients');
            $table->dropColumn('row_mail_field_id');
            $table->dropColumn('cc_row_mail_field_id');
            $table->dropColumn('bcc_row_mail_field_id');
            $table->dropColumn('mail_delay_hour');
            $table->dropColumn('mail_delay_min');
            $table->dropColumn('mail_delay_sec');

            $table->dropForeign('table_alerts__row_sms_field_id');
            $table->dropColumn('row_sms_field_id');
            $table->dropColumn('sms_recipients');
            $table->dropColumn('sms_body');
            $table->dropColumn('sms_delay_hour');
            $table->dropColumn('sms_delay_min');
            $table->dropColumn('sms_delay_sec');
        });
    }
}
