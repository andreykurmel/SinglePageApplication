<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMenutreeOrderToTablesAndFolders extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tables', function (Blueprint $table) {
            $table->integer('menutree_order')->default(0);
        });
        Schema::table('folders', function (Blueprint $table) {
            $table->integer('menutree_order')->default(0);
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
            $table->dropColumn('menutree_order');
        });
        Schema::table('folders', function (Blueprint $table) {
            $table->dropColumn('menutree_order');
        });
    }
}
