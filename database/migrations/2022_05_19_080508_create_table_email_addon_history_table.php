<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableEmailAddonHistoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('table_email_addon_history', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('table_email_addon_id');
            $table->unsignedInteger('row_id');
            $table->dateTime('send_date');
            $table->string('preview_from');
            $table->string('preview_to');
            $table->string('preview_cc')->nullable();
            $table->string('preview_bcc')->nullable();
            $table->string('preview_reply')->nullable();
            $table->string('preview_subject')->nullable();
            $table->text('preview_body')->nullable();
            $table->text('preview_tablda_row');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('table_email_addon_history');
    }
}
