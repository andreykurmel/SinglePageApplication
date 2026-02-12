<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DelUserGroupsIdFromTablePermissions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('table_permissions', function (Blueprint $table) {
            $table->dropForeign('table_permissions_user_group_id');
            $table->dropColumn('user_group_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('table_permissions', function (Blueprint $table) {
            $table->unsignedInteger('user_group_id')->nullable();

            $table->foreign('user_group_id', 'table_permissions_user_group_id')
                ->references('id')
                ->on('user_groups')
                ->onDelete('cascade');
        });
    }
}
