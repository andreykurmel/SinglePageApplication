<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTablePermissionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('table_permissions', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_group_id')->nullable();
            $table->unsignedInteger('table_id');
            $table->string('name', 50)->nullable();
            $table->string('notes')->nullable();
            $table->tinyInteger('can_add')->default(0);
            $table->tinyInteger('can_delete')->default(0);
            $table->string('can_download', 6)->default('000000');
            $table->tinyInteger('can_see_history')->default(0);
            $table->tinyInteger('can_reference')->default(0);

            $table->foreign('user_group_id', 'table_permissions_user_group_id')
                ->references('id')
                ->on('user_groups')
                ->onDelete('cascade');

            $table->foreign('table_id', 'table_permissions_table_id')
                ->references('id')
                ->on('tables')
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
        Schema::dropIfExists('table_permissions');
    }
}
