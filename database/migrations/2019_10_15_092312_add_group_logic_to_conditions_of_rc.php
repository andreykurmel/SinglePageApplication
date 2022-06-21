<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddGroupLogicToConditionsOfRc extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('table_ref_condition_items', function (Blueprint $table) {
            $table->string('group_logic', 32)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('table_ref_condition_items', function (Blueprint $table) {
            $table->dropColumn('group_logic');
        });
    }
}
