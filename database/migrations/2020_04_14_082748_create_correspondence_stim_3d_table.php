<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCorrespondenceStim3dTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mysql_correspondence')
            ->create('correspondence_stim_3d', function (Blueprint $table) {
                $table->increments('id');
                $table->string('row_hash', 32)->nullable();
                $table->integer('row_order')->default(0);

                $table->string('avail_to_user', 32)->nullable();
                $table->string('top_tab', 32)->nullable();
                $table->string('select', 32)->nullable();
                $table->string('accordion', 32)->nullable();
                $table->string('horizontal_lvl1', 32)->nullable();
                $table->string('vertical_lvl1', 32)->nullable();
                $table->string('horizontal_lvl2', 32)->nullable();
                $table->string('vertical_lvl2', 32)->nullable();
                $table->string('table', 64);
                $table->string('options', 255)->nullable();
                $table->string('model_3d', 32)->nullable();
                $table->string('inheritance_3d', 64)->nullable();
                $table->string('inheritance_type', 255)->nullable();
                $table->string('type_tablda', 32)->nullable();
                $table->string('stimvis_status', 32)->nullable();
                $table->unsignedInteger('stimvis_table_id')->nullable();
                $table->unsignedInteger('stimvis_field_id')->nullable();
                $table->string('stimvis_operator', 32)->nullable();
                $table->string('stimvis_value', 128)->nullable();
            });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('mysql_correspondence')
            ->dropIfExists('correspondence_stim_3d');
    }
}
