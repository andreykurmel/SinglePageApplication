<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableAlertRightsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('table_alert_rights', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('table_alert_id');
            $table->unsignedInteger('table_permission_id');
            $table->tinyInteger('can_edit')->default(0);
            $table->tinyInteger('can_activate')->default(0);

            $table->foreign('table_alert_id')
                ->references('id')
                ->on('table_alerts')
                ->onDelete('cascade');

            $table->foreign('table_permission_id')
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
        Schema::dropIfExists('table_alert_rights');
    }
}
