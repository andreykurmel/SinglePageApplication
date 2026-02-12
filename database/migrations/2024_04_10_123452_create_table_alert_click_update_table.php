<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableAlertClickUpdateTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('table_alerts', function (Blueprint $table) {
            $table->dropForeign('table_alerts__click_field_id');
            $table->dropColumn('click_field_id');
            $table->dropColumn('click_new_value');
        });

        Schema::create('table_alert_click_updates', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('table_alert_id');
            $table->unsignedInteger('table_field_id');
            $table->string('new_value', 255)->nullable();

            $table->foreign('table_alert_id', 'table_alert_click_updates__table_alert_id')
                ->references('id')
                ->on('table_alerts')
                ->onDelete('cascade');

            $table->foreign('table_field_id', 'table_alert_click_updates__table_field_id')
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
        Schema::dropIfExists('dcr_notif_linked_tables');

        Schema::table('table_alerts', function (Blueprint $table) {
            $table->unsignedInteger('click_field_id')->nullable();
            $table->string('click_new_value', 255)->nullable();

            $table->foreign('click_field_id', 'table_alerts__click_field_id')
                ->references('id')
                ->on('table_fields')
                ->onDelete('set null');
        });
    }
}
