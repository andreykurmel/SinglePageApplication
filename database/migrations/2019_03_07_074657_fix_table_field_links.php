<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class FixTableFieldLinks extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('table_field_links', function (Blueprint $table) {
            $table->dropForeign('address_field_foreign');
            $table->dropColumn('address_field_id');

            $table->string('address_field', 512)->nullable();
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
            $table->dropColumn('address_field');

            $table->unsignedInteger('address_field_id')->nullable();
            $table->foreign('address_field_id', 'address_field_foreign')
                ->references('id')
                ->on('table_fields')
                ->onDelete('set null');
        });
    }
}
