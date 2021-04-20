<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class OptimizeHistoryRequests extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('history', function (Blueprint $table) {
            $table->index(['table_field_id', 'row_id', 'id'], 'history_search');
        });
        Schema::table('user_clouds', function (Blueprint $table) {
            $table->dropColumn('api_key');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('history', function (Blueprint $table) {
            $table->dropIndex('history_search');
        });
        Schema::table('user_clouds', function (Blueprint $table) {
            $table->string('api_key', 1024);
        });
    }
}
