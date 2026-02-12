<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDataRangeToTableViewsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('table_views', function (Blueprint $table) {
            $table->unsignedInteger('row_group_id')->nullable();
            $table->unsignedInteger('col_group_id')->nullable();
            $table->tinyInteger('can_sorting')->default(0);
            $table->tinyInteger('can_filter')->default(0);
            $table->tinyInteger('can_hide')->default(0);
            $table->tinyInteger('can_show_srv')->default(0);

            $table->foreign('row_group_id', 'table_views__row_group_id')
                ->references('id')
                ->on('table_row_groups')
                ->onDelete('cascade');

            $table->foreign('col_group_id', 'table_views__col_group_id')
                ->references('id')
                ->on('table_column_groups')
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
        Schema::table('table_views', function (Blueprint $table) {
            $table->dropForeign('table_views__row_group_id');
            $table->dropForeign('table_views__col_group_id');
            $table->dropColumn('row_group_id');
            $table->dropColumn('col_group_id');
            $table->dropColumn('can_sorting');
            $table->dropColumn('can_filter');
            $table->dropColumn('can_hide');
        });
    }
}
