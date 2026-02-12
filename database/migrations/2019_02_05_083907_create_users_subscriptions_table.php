<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersSubscriptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_subscriptions', function (Blueprint $table) {
            $table->increments('id');
            $table->tinyInteger('active');
            $table->unsignedInteger('user_id');
            $table->string('plan_code');
            $table->unsignedInteger('left_days')->default(0);
            $table->unsignedInteger('total_days')->default(1);
            $table->float('cost')->default(0);
            $table->string('notes')->nullable();

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
            //user can have active subscription and inactive for further chose
            $table->unique(['active', 'user_id'], 'user_sub_active_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_subscriptions');
    }
}
