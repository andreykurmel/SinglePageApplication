<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFieldsForMapLinksToTableFieldLinks extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('table_field_links', function (Blueprint $table) {
            $table->unsignedInteger('link_field_lat')->nullable();
            $table->unsignedInteger('link_field_lng')->nullable();
            $table->unsignedInteger('link_field_address')->nullable();
            $table->string('web_prefix', 255)->nullable();
            $table->unsignedInteger('hide_empty_web')->nullable();

            $table->foreign('link_field_lat', 'table_field_links__lat')
                ->references('id')
                ->on('table_fields')
                ->onDelete('set null');

            $table->foreign('link_field_lat', 'table_field_links__lng')
                ->references('id')
                ->on('table_fields')
                ->onDelete('set null');

            $table->foreign('link_field_lat', 'table_field_links__address')
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
        Schema::table('table_field_links', function (Blueprint $table) {
            $table->dropForeign('table_field_links__lat');
            $table->dropForeign('table_field_links__lng');
            $table->dropForeign('table_field_links__address');

            $table->dropColumn('link_field_lat');
            $table->dropColumn('link_field_lng');
            $table->dropColumn('link_field_address');
            $table->dropColumn('web_prefix');
            $table->dropColumn('hide_empty_web');
        });
    }
}
