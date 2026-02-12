<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddRowHashToUserSubscriptions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_subscriptions', function (Blueprint $table) {
            $table->string('row_hash', 32)->nullable();
        });
        Schema::table('tables', function (Blueprint $table) {
            $table->string('row_hash', 32)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_subscriptions', function (Blueprint $table) {
            $table->dropColumn('row_hash');
        });
        Schema::table('tables', function (Blueprint $table) {
            $table->string('row_hash', 32)->nullable();
        });
    }
}
