<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeRowHeightForTableFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('table_fields', function (Blueprint $table) {
            $table->dropColumn('row_height');
            $table->tinyInteger('verttb_row_height')->default(1);
            $table->tinyInteger('verttb_cell_height')->default(1);
            $table->tinyInteger('verttb_he_auto')->default(1);
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
            $table->dropColumn('verttb_cell_height');
            $table->dropColumn('verttb_row_height');
            $table->dropColumn('verttb_he_auto');
            $table->integer('row_height')->default(1);
        });
    }
}
