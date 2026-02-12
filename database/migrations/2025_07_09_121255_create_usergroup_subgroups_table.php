<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsergroupSubgroupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_group_subgroups', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('usergroup_id');
            $table->unsignedInteger('subgroup_id');

            $table->foreign('usergroup_id', 'ugs__usergroup_id')
                ->references('id')
                ->on('user_groups')
                ->onDelete('cascade');
            $table->foreign('subgroup_id', 'ugs__subgroup_id')
                ->references('id')
                ->on('user_groups')
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
        Schema::dropIfExists('user_group_subgroups');
    }
}
