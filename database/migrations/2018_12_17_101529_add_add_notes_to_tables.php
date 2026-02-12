<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAddNotesToTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tables', function (Blueprint $table) {
            $table->text('add_notes')->nullable()->after('notes');
            $table->string('table_engine', 32)->default('default');
            $table->tinyInteger('auto_enable_virtual_scroll')->default(1);
            $table->unsignedInteger('auto_enable_virtual_scroll_when')->default(1500);
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
            $table->dropColumn('add_notes');
            $table->dropColumn('table_engine');
            $table->dropColumn('auto_enable_virtual_scroll');
            $table->dropColumn('auto_enable_virtual_scroll_when');
        });
    }
}
