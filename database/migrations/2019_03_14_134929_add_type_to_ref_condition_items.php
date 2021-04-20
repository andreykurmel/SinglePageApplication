<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTypeToRefConditionItems extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('table_ref_condition_items', function (Blueprint $table) {
            $table->string('item_type', 20)->default('P2S');
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
            $table->dropColumn('item_type', 20);
        });
    }
}
