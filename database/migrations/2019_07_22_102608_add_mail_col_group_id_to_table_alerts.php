<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMailColGroupIdToTableAlerts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('table_alerts', function (Blueprint $table) {
            $table->unsignedInteger('mail_col_group_id')->nullable();

            $table->foreign('mail_col_group_id', 'table_alerts__mail_col_group_id')
                ->references('id')
                ->on('table_column_groups')
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
        Schema::table('table_alerts', function (Blueprint $table) {
            $table->dropForeign('table_alerts__mail_col_group_id');
            $table->dropColumn('mail_col_group_id');
        });
    }
}
