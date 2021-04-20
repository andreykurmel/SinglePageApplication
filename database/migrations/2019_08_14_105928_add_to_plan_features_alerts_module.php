<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddToPlanFeaturesAlertsModule extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('plan_features', function (Blueprint $table) {
            $table->string('row_hash', 32)->nullable();
        });
        Schema::table('plans_view', function (Blueprint $table) {
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
        Schema::table('plan_features', function (Blueprint $table) {
            $table->dropColumn('row_hash');
        });
        Schema::table('plans_view', function (Blueprint $table) {
            $table->dropColumn('row_hash');
        });
    }
}
