<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeUsers2AddonsToSubscriptions extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('users_2_addons');

        Schema::create('user_subscriptions_2_addons', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('addon_id');
            $table->unsignedInteger('user_subscription_id');

            $table->foreign('addon_id')
                ->references('id')
                ->on('addons')
                ->onDelete('cascade');

            $table->foreign('user_subscription_id')
                ->references('id')
                ->on('user_subscriptions')
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
        Schema::dropIfExists('user_subscriptions_2_addons');

        Schema::create('users_2_addons', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('addon_id');
            $table->unsignedInteger('user_id');

            $table->unsignedInteger('created_by')->nullable();
            $table->string('created_name')->nullable();
            $table->dateTime('created_on');
            $table->unsignedInteger('modified_by')->nullable();
            $table->string('modified_name')->nullable();
            $table->dateTime('modified_on');

            $table->foreign('addon_id')->references('id')->on('addons')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('modified_by')->references('id')->on('users')->onDelete('set null');
        });
    }
}
