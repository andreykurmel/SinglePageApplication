<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeToAddonsSubscriptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_subscriptions', function (Blueprint $table) {
            $table->string('plan')->nullable();
            $table->unsignedInteger('add_bi')->nullable();
            $table->unsignedInteger('add_map')->nullable();
            $table->unsignedInteger('add_alert')->nullable();

            $table->dropColumn('feature');
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
            $table->dropColumn('plan');
            $table->dropColumn('add_bi');
            $table->dropColumn('add_map');
            $table->dropColumn('add_alert');

            $table->string('feature', 50)->nullable();
        });
    }
}
