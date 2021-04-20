<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddRequestFieldsToTablePermissions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('table_permissions', function (Blueprint $table) {
            $table->string('link_hash')->nullable();
            $table->string('pass')->nullable();
            $table->integer('row_request')->default(-1);
            $table->tinyInteger('is_request')->default(0);
            $table->index('is_request', 'table_permissions_is_request_index');
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
            $table->dropIndex('table_permissions_is_request_index');
            $table->dropColumn('is_request');
            $table->dropColumn('row_request');
            $table->dropColumn('link_hash');
            $table->dropColumn('pass');
        });
    }
}
