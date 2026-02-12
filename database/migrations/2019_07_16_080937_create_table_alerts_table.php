<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableAlertsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('table_alerts', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('table_id');
            $table->unsignedInteger('user_id')->nullable();
            $table->string('name', 128);
            $table->string('execution_delay', 32)->nullable();
            $table->unsignedInteger('on_added')->nullable();
            $table->unsignedInteger('on_updated')->nullable();
            $table->unsignedInteger('on_deleted')->nullable();
            $table->unsignedInteger('on_added_row_group_id')->nullable();
            $table->unsignedInteger('on_updated_row_group_id')->nullable();
            $table->unsignedInteger('on_deleted_row_group_id')->nullable();
            $table->unsignedInteger('ask_anr_confirmation')->default(0);
            $table->unsignedInteger('automation_email_addon_id')->nullable();
            $table->string('description', 2048)->nullable();
            $table->string('mail_subject', 255)->nullable();
            $table->string('mail_addressee', 255)->nullable();
            $table->string('mail_message', 512)->nullable();
            $table->string('recipients', 512)->nullable();
            $table->tinyInteger('is_active')->nullable();

            $table->unsignedInteger('on_snapshot')->nullable();
            $table->dateTime('snapshot_onetime_datetime')->nullable();
            $table->string('snapshot_timezone', 64)->nullable();
            $table->string('snapshot_type', 16)->default('recurring');
            $table->string('snapshot_frequency', 16)->default('daily');
            $table->string('snapshot_hourly_freq', 16)->default('0');
            $table->string('snapshot_day_freq', 255)->default('[]');
            $table->string('snapshot_month_freq', 16)->default('first');
            $table->unsignedInteger('snapshot_month_day')->default(1);
            $table->date('snapshot_month_date')->nullable();
            $table->time('snapshot_time')->default('00:00:00');

            $table->tinyInteger('enabled_email')->nullable();
            $table->tinyInteger('enabled_sms')->nullable();
            $table->tinyInteger('enabled_ufv')->nullable();
            $table->tinyInteger('enabled_anr')->nullable();
            $table->tinyInteger('enabled_sending')->nullable();
            $table->tinyInteger('enabled_snapshot')->nullable();

            $table->foreign('table_id', 'table_alerts__table_id')
                ->references('id')
                ->on('tables')
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
        Schema::dropIfExists('table_alerts');
    }
}
