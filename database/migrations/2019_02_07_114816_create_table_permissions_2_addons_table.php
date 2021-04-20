<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTablePermissions2AddonsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('table_permissions_2_addons', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('table_permission_id');
            $table->unsignedInteger('addon_id');
            $table->string('type');//'view','edit'
            $table->tinyInteger('lockout_layout')->nullable();
            $table->tinyInteger('add_new')->nullable();
            $table->tinyInteger('hide_settings')->nullable();
            $table->tinyInteger('block_spacing')->nullable();
            $table->tinyInteger('vert_grid_step')->nullable();

            $table->foreign('table_permission_id')
                ->references('id')
                ->on('table_permissions')
                ->onDelete('cascade');

            $table->foreign('addon_id')
                ->references('id')
                ->on('addons')
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
        Schema::dropIfExists('table_permissions_2_addons');
    }
}
