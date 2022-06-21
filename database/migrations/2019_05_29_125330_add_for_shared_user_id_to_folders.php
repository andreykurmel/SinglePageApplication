<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddForSharedUserIdToFolders extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('folders', function (Blueprint $table) {
            $table->unsignedInteger('is_folder_link')->nullable();
            $table->unsignedInteger('for_shared_user_id')->nullable();

            $table->foreign('for_shared_user_id', 'folders__for_shared_user_id')
                ->references('id')
                ->on('users')
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
        Schema::table('folders', function (Blueprint $table) {
            $table->dropForeign('folders__for_shared_user_id');
            $table->dropColumn('for_shared_user_id');
            $table->dropColumn('is_folder_link');
        });
    }
}
