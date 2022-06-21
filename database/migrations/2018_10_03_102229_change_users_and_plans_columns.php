<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeUsersAndPlansColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->unsignedInteger('plan_feature_id')->nullable();
            $table->string('code')->default('basic');

            $table->unique('code', 'plans_code_unique');
        });
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedInteger('plan_feature_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->dropColumn('plan_feature_id');
            $table->dropColumn('code');
        });
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('plan_feature_id');
        });
    }
}
