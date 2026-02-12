<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIsStartTablePopupToTableFieldsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('table_fields', function (Blueprint $table) {
            $table->tinyInteger('fld_popup_shown')->default(1);
            $table->tinyInteger('fld_display_name')->default(1);
            $table->tinyInteger('fld_display_value')->default(1);
            $table->tinyInteger('fld_display_border')->default(1);
            $table->string('fld_display_header_type', 32)->default('default');
            $table->string('width_of_table_popup', 16)->default('full');
            $table->tinyInteger('is_start_table_popup')->default(0);
            $table->tinyInteger('is_topbot_in_popup')->default(0);
            $table->tinyInteger('markerjs_annotations')->default(0);
            $table->tinyInteger('markerjs_cropro')->default(0);
            $table->unsignedInteger('twilio_google_acc_id')->nullable();
            $table->unsignedInteger('twilio_sendgrid_acc_id')->nullable();
            $table->unsignedInteger('twilio_sms_acc_id')->nullable();
            $table->unsignedInteger('twilio_voice_acc_id')->nullable();
            $table->string('twilio_sender_name', 64)->nullable();
            $table->string('twilio_sender_email', 64)->nullable();
            $table->string('markerjs_savetype', 16)->default('replace');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('table_fields', function (Blueprint $table) {
            $table->dropColumn('fld_popup_shown');
            $table->dropColumn('fld_display_name');
            $table->dropColumn('fld_display_value');
            $table->dropColumn('fld_display_border');
            $table->dropColumn('is_start_table_popup');
            $table->dropColumn('width_of_table_popup');
            $table->dropColumn('is_topbot_in_popup');
        });
    }
}
