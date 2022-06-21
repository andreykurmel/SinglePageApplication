<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddGroupClauseToRefCondItem extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('table_ref_condition_items', function (Blueprint $table) {
            $table->string('group_clause', 64)->default('A');
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
            $table->dropColumn('group_clause');
        });
    }
}
