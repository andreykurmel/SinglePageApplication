<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAddonReqToTablesAndSubscription extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tables', function (Blueprint $table) {
            $table->unsignedInteger('add_request')->nullable();
            $table->unsignedInteger('add_kanban')->nullable();
            $table->unsignedInteger('add_gantt')->nullable();
            $table->unsignedInteger('add_email')->nullable();
            $table->unsignedInteger('add_calendar')->nullable();
            $table->unsignedInteger('add_twilio')->nullable();
            $table->unsignedInteger('add_tournament')->nullable();
            $table->unsignedInteger('add_simplemap')->nullable();
            $table->unsignedInteger('add_grouping')->nullable();
            $table->unsignedInteger('add_report')->nullable();
            $table->unsignedInteger('add_ai')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tables', function (Blueprint $table) {
            $table->dropColumn('add_request');
            $table->dropColumn('add_kanban');
            $table->dropColumn('add_gantt');
            $table->dropColumn('add_email');
            $table->dropColumn('add_calendar');
            $table->dropColumn('add_twilio');
            $table->dropColumn('add_tournament');
            $table->dropColumn('add_simplemap');
            $table->dropColumn('add_grouping');
            $table->dropColumn('add_report');
            $table->dropColumn('add_ai');
        });
    }
}
