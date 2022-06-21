<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeRowGroupsToTypes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('table_row_groups', function (Blueprint $table) {
            $table->string('type')->default('Conditional');
            $table->string('listing_field')->nullable();

            $table->foreign('listing_field', 'table_row_groups__listing_field')
                ->references('field')
                ->on('table_fields')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('table_row_groups', function (Blueprint $table) {
            $table->dropColumn('type');
            $table->dropColumn('listing_field');
        });
    }
}
