<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAlertNotifLinkedTablesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dcr_notif_linked_tables', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('dcr_id');
            $table->unsignedInteger('link_id');
            $table->string('related_format', 32)->nullable();
            $table->unsignedInteger('related_col_group_id')->nullable();
            $table->tinyInteger('is_active')->default(0);
            $table->string('type', 32);
            $table->string('description', 255)->nullable();

            $table->foreign('dcr_id', 'dnlt__dcr_id')
                ->references('id')
                ->on('table_data_requests')
                ->onDelete('cascade');

            $table->foreign('link_id', 'dnlt__link_id')
                ->references('id')
                ->on('table_field_links')
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
    }
}
