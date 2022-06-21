<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeUserGroupsToTwoTypes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_groups', function (Blueprint $table) {
            $table->dropForeign('user_groups_table_id_foreign');
            $table->renameColumn('table_id', 'obj_id');
            $table->string('type', 20)->default('table')->after('id');
            $table->index('type', 'user_groups_type_index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_groups', function (Blueprint $table) {
            $table->dropIndex('user_groups_type_index');
            $table->dropColumn('type');
            $table->renameColumn('obj_id', 'table_id');
            $table->foreign('table_id', 'user_groups_table_id_foreign')
                ->references('id')
                ->on('tables')
                ->onDelete('cascade');
        });
    }
}
