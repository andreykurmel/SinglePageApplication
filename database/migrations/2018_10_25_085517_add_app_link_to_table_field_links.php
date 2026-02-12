<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAppLinkToTableFieldLinks extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('table_field_links', function (Blueprint $table) {
            $table->unsignedInteger('table_app_id')->nullable();

            $table->foreign('table_app_id', 'table_field_links_table_app_id')
                ->references('id')
                ->on('table_apps')
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
        Schema::table('table_field_links', function (Blueprint $table) {
            $table->dropForeign('table_field_links_table_app_id');
            $table->dropColumn('table_app_id');
        });
    }
}
