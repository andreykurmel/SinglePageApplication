<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNewToFolderViews extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('folder_views', function (Blueprint $table) {
            $table->tinyInteger('is_active')->default(1);
            $table->string('user_link', 50)->nullable();
            $table->tinyInteger('is_locked')->default(0);
            $table->string('lock_pass', 50)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
