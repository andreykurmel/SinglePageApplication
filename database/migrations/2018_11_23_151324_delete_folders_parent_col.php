<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DeleteFoldersParentCol extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('folders', function (Blueprint $table) {
            $table->dropForeign('folders_parent_id_foreign');
            $table->dropColumn('parent_id');
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
            $table->unsignedInteger('parent_id')->nullable();

            $table->foreign('parent_id', 'folders_parent_id_foreign')
                ->references('id')
                ->on('folders')
                ->onDelete('cascade');
        });
    }
}
