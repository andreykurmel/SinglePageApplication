<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeUserToManyCards extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('stripe_card_id');
            $table->dropColumn('stripe_card_last');
            $table->unsignedInteger('selected_card')->nullable();
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
            $table->string('stripe_card_id')->nullable();
            $table->string('stripe_card_last')->nullable();
            $table->dropColumn('selected_card');
        });
    }
}
