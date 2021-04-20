<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddHistoryCheckToTableFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('table_fields', function (Blueprint $table) {
            $table->tinyInteger('show_history')->after('tooltip')->default(0);
            $table->tinyInteger('ddl_add_option')->after('ddl_id')->default(1);
            $table->tinyInteger('ddl_auto_fill')->after('ddl_add_option')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('table_fields', function (Blueprint $table) {
            $table->dropColumn('show_history');
            $table->dropColumn('ddl_add_option');
            $table->dropColumn('ddl_auto_fill');
        });
    }
}
