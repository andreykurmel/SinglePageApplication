<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddApplyTargetRowGroupIdToDdlReferences extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ddl_references', function (Blueprint $table) {
            $table->unsignedInteger('apply_target_row_group_id')->nullable();
            $table->unsignedInteger('only_one')->default(0);

            $table->foreign('apply_target_row_group_id', 'ddl_references__apply_target_row_group_id')
                ->references('id')
                ->on('table_row_groups')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ddl_references', function (Blueprint $table) {
            $table->dropForeign('ddl_references__apply_target_row_group_id');
            $table->dropColumn('apply_target_row_group_id');
            $table->dropColumn('only_one');
        });
    }
}
