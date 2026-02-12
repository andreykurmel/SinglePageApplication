<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeTableViewRightsCol extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('table_view_rights', function (Blueprint $table) {
            $table->dropForeign('table_view_rights_user_group_id_foreign');
            $table->dropColumn('user_group_id');

            $table->unsignedInteger('table_permission_id');

            $table->foreign('table_permission_id', 'table_view_rights_table_permission_id')
                ->references('id')
                ->on('table_permissions')
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
        Schema::table('table_view_rights', function (Blueprint $table) {
            $table->dropForeign('table_view_rights_table_permission_id');
            $table->dropColumn('table_permission_id');

            $table->unsignedInteger('user_group_id');

            $table->foreign('user_group_id', 'table_view_rights_user_group_id_foreign')
                ->references('id')
                ->on('user_groups')
                ->onDelete('cascade');
        });
    }
}
