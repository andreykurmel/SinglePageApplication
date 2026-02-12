<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStimAppViewFeedbackResultsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mysql_correspondence')
            ->create('stim_app_view_feedback_results', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('stim_view_feedback_id');
                $table->unsignedInteger('by_user_id')->nullable();
                $table->string('user_signature', 255);
                $table->dateTime('received_date');
                $table->string('notes', 1024);
                $table->string('received_attachments', 255)->nullable();
                $table->string('row_hash', 64)->nullable();
                $table->timestamps();

                $table->foreign('stim_view_feedback_id', 'stim_app_view_feedback_results__feedback_id')
                    ->references('id')
                    ->on('stim_app_view_feedbacks')
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
        Schema::dropIfExists('stim_app_view_feedback_results');
    }
}
