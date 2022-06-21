<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserInvitationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_invitations', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->string('email');
            $table->dateTime('date_send')->nullable();
            $table->dateTime('date_accept')->nullable();
            $table->dateTime('date_confirm')->nullable();
            $table->tinyInteger('status')->default(0);//1-sent; 2-invited;
            $table->tinyInteger('rewarded')->default(10);

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->float('invitations_reward')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_invitations');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('invitations_reward');
        });
    }
}
