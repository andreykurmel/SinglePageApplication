<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeUserCards extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_cards', function (Blueprint $table) {
            $table->unsignedInteger('stripe_exp_month')->nullable();
            $table->unsignedInteger('stripe_exp_year')->nullable();
            $table->string('stripe_card_name')->nullable();
            $table->string('stripe_card_zip')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_cards', function (Blueprint $table) {
            $table->dropColumn('stripe_exp_month');
            $table->dropColumn('stripe_exp_year');
            $table->dropColumn('stripe_card_name');
            $table->dropColumn('stripe_card_zip');
        });
    }
}
