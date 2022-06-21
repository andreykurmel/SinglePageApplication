<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ImproveUserGroupIndividuals extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_groups_2_users', function (Blueprint $table) {
            $table->tinyInteger('cached_from_conditions')->default(0);
            $table->index('cached_from_conditions');
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
            $table->dropIndex('cached_from_conditions');
            $table->dropColumn('cached_from_conditions');
        });
    }
}
