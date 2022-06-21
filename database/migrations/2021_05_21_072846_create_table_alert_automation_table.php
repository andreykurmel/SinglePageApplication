<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableAlertAutomationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('table_alert_automations', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('table_alert_id');
            $table->unsignedInteger('table_ref_cond_id')->nullable();
            $table->unsignedInteger('table_field_id')->nullable();
            $table->string('ufv_source', 32)->nullable();
            $table->string('ufv_input', 128)->nullable();
            $table->unsignedInteger('ufv_inherit_field_id')->nullable();
            $table->tinyInteger('is_active')->default(1);

            $table->foreign('table_alert_id', 'table_alert_automations__table_alert_id')
                ->references('id')
                ->on('table_alerts')
                ->onDelete('cascade');

            $table->foreign('table_ref_cond_id', 'table_alert_automations__table_ref_cond_id')
                ->references('id')
                ->on('table_ref_conditions')
                ->onDelete('cascade');

            $table->foreign('table_field_id', 'table_alert_automations__table_field_id')
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
        Schema::dropIfExists('table_alert_automations');
    }
}
