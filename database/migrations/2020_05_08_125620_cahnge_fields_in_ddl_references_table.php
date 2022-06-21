<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CahngeFieldsInDdlReferencesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ddl', function (Blueprint $table) {
            $table->string('items_pos', 16)->default('before');
            $table->string('datas_sort', 16)->nullable();
        });
        Schema::table('ddl_references', function (Blueprint $table) {
            $table->dropForeign('ddl_references__descr_field');
            $table->dropColumn('descr_field_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ddl', function (Blueprint $table) {
            $table->dropColumn('items_pos');
            $table->dropColumn('datas_sort');
        });
        Schema::table('ddl_references', function (Blueprint $table) {
            $table->unsignedInteger('descr_field_id')->nullable();
            $table->foreign('descr_field_id', 'ddl_references__descr_field')
                ->references('id')
                ->on('table_fields')
                ->onDelete('set null');
        });
    }
}
