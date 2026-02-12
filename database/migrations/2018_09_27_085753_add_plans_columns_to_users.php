<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPlansColumnsToUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedInteger('plan_id')->default(1);
            $table->float('credit')->default(0);
            $table->text('functions')->nullable();
            $table->string('company')->nullable();
            $table->string('team')->nullable();
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
            $table->dropColumn('plan_id');
            $table->dropColumn('functions');
            $table->dropColumn('company');
            $table->dropColumn('team');
        });
    }
}
