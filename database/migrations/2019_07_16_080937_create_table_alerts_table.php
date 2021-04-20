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
            $table->string('name', 128);
            $table->unsignedInteger('on_added')->nullable();
            $table->unsignedInteger('on_updated')->nullable();
            $table->unsignedInteger('on_deleted')->nullable();
            $table->string('mail_subject', 255)->nullable();
            $table->string('mail_addressee', 255)->nullable();
            $table->string('mail_message', 512)->nullable();
            $table->string('recipients', 512)->nullable();
            $table->tinyInteger('is_active')->nullable();

            $table->foreign('table_id', 'table_alerts__table_id')
                ->references('id')
                ->on('tables')
                ->onDelete('cascade');

            $table->foreign('table_field_id', 'table_alerts__table_field_id')
                ->references('id')
                ->on('table_fields')
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
