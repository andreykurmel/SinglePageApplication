<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStimAppViewFeedbacksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mysql_correspondence')
            ->create('stim_app_view_feedbacks', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('stim_view_id');
                $table->string('part_uuid', 64);
                $table->string('purpose', 128);
                $table->string('email_to', 255)->nullable();
                $table->string('email_cc', 255)->nullable();
                $table->string('email_bcc', 255)->nullable();
                $table->string('email_subject', 512)->nullable();
                $table->text('email_body')->nullable();
                $table->dateTime('send_date')->nullable();
                $table->string('request_pass', 64)->nullable();
                $table->timestamps();

                $table->foreign('stim_view_id', 'stim_app_view_feedbacks__stim_view_id')
                    ->references('id')
                    ->on('stim_app_views')
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
        Schema::dropIfExists('stim_app_view_feedbacks');
    }
}
