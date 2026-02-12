<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableTwilioRightsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('table_twilio_rights', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('table_twilio_id');
            $table->unsignedInteger('table_permission_id');
            $table->tinyInteger('can_edit')->default(0);

            $table->foreign('table_twilio_id', 'ttr__table_twilio_id')
                ->references('id')
                ->on('table_twilio_addon_settings')
                ->onDelete('cascade');

            $table->foreign('table_permission_id', 'ttr__table_permission_id')
                ->references('id')
                ->on('table_permissions')
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
        Schema::dropIfExists('table_twilio_rights');
    }
}
