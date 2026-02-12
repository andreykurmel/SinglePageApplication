<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableRcmapPositionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('table_rcmap_positions', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('table_id');
            $table->unsignedInteger('user_id');
            $table->string('object_type', 32);
            $table->unsignedInteger('object_id');
            $table->float('pos_x');
            $table->float('pos_y');
            $table->tinyInteger('used_only')->default(1);
            $table->tinyInteger('opened')->default(1);
            $table->tinyInteger('changed')->default(0);
            $table->tinyInteger('visible')->default(1);

            $table->foreign('table_id', 'table_rcmap_positions__table_id')
                ->references('id')
                ->on('tables')
                ->onDelete('cascade');
            $table->foreign('user_id', 'table_rcmap_positions__user_id')
                ->references('id')
                ->on('users')
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
        Schema::dropIfExists('table_rcmap_positions');
    }
}
