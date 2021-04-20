<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddUserIdToFolderTrees extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('folder_trees', function (Blueprint $table) {
            $table->unsignedInteger('user_id')->nullable();
            $table->dropColumn('level');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('folder_trees', function (Blueprint $table) {
            $table->dropColumn('user_id');
            $table->unsignedInteger('level')->default(0);
        });
    }
}
