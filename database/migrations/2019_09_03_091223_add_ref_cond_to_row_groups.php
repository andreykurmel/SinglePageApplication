<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddRefCondToRowGroups extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('table_row_groups', function (Blueprint $table) {
            $table->unsignedInteger('row_ref_condition_id')->nullable();

            $table->foreign('row_ref_condition_id', 'table_row_groups__row_ref_condition_id')
                ->references('id')
                ->on('table_ref_conditions')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('table_row_groups', function (Blueprint $table) {
            $table->dropForeign('table_row_groups__row_ref_condition_id');
            $table->dropColumn('row_ref_condition_id');
        });
    }
}
