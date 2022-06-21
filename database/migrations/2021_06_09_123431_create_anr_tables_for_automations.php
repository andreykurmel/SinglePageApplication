<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAnrTablesForAutomations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('alert_anr_tables', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('table_alert_id');
            $table->string('name', 64)->nullable();
            $table->unsignedInteger('table_id')->nullable();
            $table->unsignedInteger('qty')->default(1);
            $table->tinyInteger('is_active')->default(1);
            //for approve functions
            $table->unsignedInteger('approve_user')->nullable();
            $table->tinyInteger('need_approve')->default(0);
            $table->text('triggered_row')->nullable();
            //for temp fields
            $table->tinyInteger('temp_is_active')->default(0);
            $table->string('temp_name', 64)->nullable();
            $table->unsignedInteger('temp_table_id')->nullable();
            $table->unsignedInteger('temp_qty')->nullable();

            $table->foreign('table_alert_id', 'alert_anr__table_alert_id')
                ->references('id')
                ->on('table_alerts')
                ->onDelete('cascade');

            $table->foreign('table_id', 'alert_anr__table_id')
                ->references('id')
                ->on('tables')
                ->onDelete('cascade');
        });



        Schema::create('alert_anr_table_fields', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('anr_table_id');
            $table->unsignedInteger('table_field_id')->nullable();
            $table->string('source', 32)->nullable();
            $table->string('input', 128)->nullable();
            $table->string('show_input', 128)->nullable();
            $table->unsignedInteger('inherit_field_id')->nullable();
            //for temp fields
            $table->unsignedInteger('temp_table_field_id')->nullable();
            $table->string('temp_source', 32)->nullable();
            $table->string('temp_input', 128)->nullable();
            $table->string('show_temp_input', 128)->nullable();
            $table->unsignedInteger('temp_inherit_field_id')->nullable();

            $table->foreign('anr_table_id', 'alert_anr_field__anr_table_id')
                ->references('id')
                ->on('alert_anr_tables')
                ->onDelete('cascade');

            $table->foreign('table_field_id', 'alert_anr_field__table_field_id')
                ->references('id')
                ->on('table_fields')
                ->onDelete('cascade');

            $table->foreign('inherit_field_id', 'alert_anr_field__inherit_field_id')
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
        Schema::dropIfExists('alert_anr_table_fields');
        Schema::dropIfExists('alert_anr_tables');
    }
}
