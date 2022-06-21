<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddUserGroupToCommunications extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('table_communications', function (Blueprint $table) {
            $table->unsignedInteger('to_user_group_id')->nullable();

            $table->foreign('to_user_group_id', 'table_communications_to_user_group_id')
                ->references('id')
                ->on('user_groups')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('table_communications', function (Blueprint $table) {
            $table->dropForeign('table_communications_to_user_group_id');
            $table->dropColumn('to_user_group_id');
        });
    }
}
