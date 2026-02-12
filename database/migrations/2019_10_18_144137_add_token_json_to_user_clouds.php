<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTokenJsonToUserClouds extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_clouds', function (Blueprint $table) {
            $table->text('token_json')->nullable();
            $table->string('msg_to_user', 1024)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_clouds', function (Blueprint $table) {
            $table->dropColumn('token_json');
            $table->dropColumn('msg_to_user');
        });
    }
}
