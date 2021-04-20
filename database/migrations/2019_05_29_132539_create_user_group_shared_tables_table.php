<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserGroupSharedTablesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_group_shared_tables', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_group_id');
            $table->unsignedInteger('table_id');
            $table->tinyInteger('is_app')->default(0);
            $table->tinyInteger('is_active')->default(1);

            $table->foreign('user_group_id', 'user_group_shared_tables__user_group_id')
                ->references('id')
                ->on('user_groups')
                ->onDelete('cascade');

            $table->foreign('table_id', 'user_group_shared_tables__table_id')
                ->references('id')
                ->on('tables')
                ->onDelete('cascade');

            $table->unique(['user_group_id', 'table_id'], 'user_group_shared_tables__unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_group_shared_tables');
    }
}
