<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddRowCopyToRegularRowGrouopItem extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('table_row_group_regulars', function (Blueprint $table) {
            $table->string('list_field', 50)->default('id');
            $table->text('row_json')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('table_row_group_regulars', function (Blueprint $table) {
            $table->dropColumn('list_field');
            $table->dropColumn('row_json');
        });
    }
}
