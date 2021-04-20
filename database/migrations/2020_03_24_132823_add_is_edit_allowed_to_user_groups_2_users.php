<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIsEditAllowedToUserGroups2Users extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_groups_2_users', function (Blueprint $table) {
            $table->tinyInteger('is_edit_added')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_groups_2_users', function (Blueprint $table) {
            $table->dropColumn('is_edit_added');
        });
    }
}
