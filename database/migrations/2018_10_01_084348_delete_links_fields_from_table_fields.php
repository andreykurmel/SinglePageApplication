<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DeleteLinksFieldsFromTableFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('table_fields', function (Blueprint $table) {
            $table->dropForeign('listing_field_foreign');
            $table->dropColumn('link_type');
            $table->dropColumn('table_ref_condition_id');
            $table->dropColumn('listing_field_id');
            $table->dropColumn('address_field_id');
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
            $table->string('link_type', 20)->nullable();
            $table->unsignedInteger('table_ref_condition_id')->nullable();
            $table->unsignedInteger('listing_field_id')->nullable();
            $table->unsignedInteger('address_field_id')->nullable();

            $table->foreign('listing_field_id', 'listing_field_foreign')
                ->references('id')
                ->on('table_fields')
                ->onDelete('set null');
        });
    }
}
