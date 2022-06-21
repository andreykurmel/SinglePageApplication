<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDcrLinkedTableTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dcr_linked_tables', function (Blueprint $table) {
            $table->increments('id');
            $table->tinyInteger('is_active')->default(1);
            $table->unsignedInteger('table_request_id');
            $table->unsignedInteger('linked_table_id');
            $table->unsignedInteger('linked_permission_id')->nullable();
            $table->unsignedInteger('position_field_id')->nullable();
            $table->unsignedInteger('passed_ref_cond_id')->nullable();
            $table->string('header', 128)->nullable();
            $table->string('position', 128)->nullable();
            $table->string('style', 128)->nullable();

            $table->foreign('table_request_id', 'dcr_linked_table_table_request_id')
                ->references('id')
                ->on('table_data_requests')
                ->onDelete('cascade');

            $table->foreign('linked_table_id', 'dcr_linked_table_linked_table_id')
                ->references('id')
                ->on('tables')
                ->onDelete('cascade');

            $table->foreign('linked_permission_id', 'dcr_linked_table_linked_permission_id')
                ->references('id')
                ->on('table_permissions')
                ->onDelete('cascade');

            $table->foreign('position_field_id', 'dcr_linked_table_position_field_id')
                ->references('id')
                ->on('table_fields')
                ->onDelete('cascade');

            $table->foreign('passed_ref_cond_id', 'dcr_linked_table_passed_ref_cond_id')
                ->references('id')
                ->on('table_ref_conditions')
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
        Schema::dropIfExists('dcr_linked_table');
    }
}
