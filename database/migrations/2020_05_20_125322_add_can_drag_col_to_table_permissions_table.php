<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCanDragColToTablePermissionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('table_permissions', function (Blueprint $table) {
            $table->tinyInteger('can_drag_columns')->default(0);
            $table->tinyInteger('can_change_primaryview')->default(0);
            $table->string('can_download', 7)->default('0000000')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('table_permissions', function (Blueprint $table) {
            $table->dropColumn('can_drag_columns');
            $table->dropColumn('can_change_primaryview');
            $table->string('can_download', 6)->default('000000')->change();
        });
    }
}
