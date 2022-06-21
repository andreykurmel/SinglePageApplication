<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUfvTablesForAutmoatins extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('alert_ufv_table_fields');
        Schema::dropIfExists('alert_ufv_tables');



        Schema::create('alert_ufv_tables', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('table_alert_id');
            $table->string('name', 64)->nullable();
            $table->unsignedInteger('table_id')->nullable();
            $table->unsignedInteger('table_ref_cond_id')->nullable();
            $table->tinyInteger('is_active')->default(1);

            $table->foreign('table_alert_id', 'alert_ufv__table_alert_id')
                ->references('id')
                ->on('table_alerts')
                ->onDelete('cascade');

            $table->foreign('table_id', 'alert_ufv__table_id')
                ->references('id')
                ->on('tables')
                ->onDelete('cascade');

            $table->foreign('table_ref_cond_id', 'alert_ufv__table_ref_cond_id')
                ->references('id')
                ->on('table_ref_conditions')
                ->onDelete('cascade');
        });



        Schema::create('alert_ufv_table_fields', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('ufv_table_id');
            $table->unsignedInteger('table_field_id')->nullable();
            $table->string('source', 32)->nullable();
            $table->string('input', 128)->nullable();
            $table->string('show_input', 128)->nullable();
            $table->unsignedInteger('inherit_field_id')->nullable();

            $table->foreign('ufv_table_id', 'alert_ufv_field__ufv_table_id')
                ->references('id')
                ->on('alert_ufv_tables')
                ->onDelete('cascade');

            $table->foreign('table_field_id', 'alert_ufv_field__table_field_id')
                ->references('id')
                ->on('table_fields')
                ->onDelete('cascade');

            $table->foreign('inherit_field_id', 'alert_ufv_field__inherit_field_id')
                ->references('id')
                ->on('table_fields')
                ->onDelete('cascade');
        });



        Schema::dropIfExists('table_alert_automations');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('alert_ufv_table_fields');
        Schema::dropIfExists('alert_ufv_tables');



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
}
