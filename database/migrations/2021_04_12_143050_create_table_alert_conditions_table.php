<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableAlertConditionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('table_alert_conditions', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('table_alert_id');
            $table->unsignedInteger('table_field_id');
            $table->string('condition', 16)->nullable();
            $table->string('logic', 16)->nullable();
            $table->string('new_value', 255)->nullable();
            $table->tinyInteger('is_active')->default(1);

            $table->foreign('table_alert_id', 'table_alert_conditions__table_alert_id')
                ->references('id')
                ->on('table_alerts')
                ->onDelete('cascade');

            $table->foreign('table_field_id', 'table_alert_conditions__table_field_id')
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
        Schema::dropIfExists('table_alert_conditions');
    }
}
