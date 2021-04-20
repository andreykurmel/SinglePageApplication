<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class PrepareStripeRecurrent extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('stripe_user_id')->nullable();
            $table->string('stripe_card_id')->nullable();
            $table->string('stripe_card_last')->nullable();
            $table->string('paypal_card_id')->nullable();
            $table->string('paypal_card_last')->nullable();
            $table->string('pay_method')->default('stripe');
            $table->tinyInteger('use_credit')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('stripe_user_id');
            $table->dropColumn('stripe_card_id');
            $table->dropColumn('stripe_card_last');
            $table->dropColumn('paypal_card_id');
            $table->dropColumn('paypal_card_last');
            $table->dropColumn('pay_method');
            $table->dropColumn('use_credit');
        });
    }
}
