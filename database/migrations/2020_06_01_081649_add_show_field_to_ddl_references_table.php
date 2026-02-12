<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddShowFieldToDdlReferencesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ddl_references', function (Blueprint $table) {
            $table->dropColumn('is_distinctive');
            $table->unsignedInteger('show_field_id')->nullable();

            $table->foreign('show_field_id', 'ddl_references__show_field')
                ->references('id')
                ->on('table_fields')
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
        Schema::table('ddl_references', function (Blueprint $table) {
            $table->unsignedInteger('is_distinctive')->default('1');
            $table->dropForeign('ddl_references__show_field');
            $table->dropColumn('show_field_id');
        });
    }
}
