<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddGoogleAddressSettingsToTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tables', function (Blueprint $table) {
            $table->unsignedInteger('address_fld__source_id')->nullable();
            $table->foreign('address_fld__source_id', 'tables_address_fld__source_id')
                ->references('id')
                ->on('table_fields')
                ->onDelete('set null');

            $table->unsignedInteger('address_fld__street_address')->nullable();
            $table->foreign('address_fld__street_address', 'tables_address_fld__street_address')
                ->references('id')
                ->on('table_fields')
                ->onDelete('set null');

            $table->unsignedInteger('address_fld__street')->nullable();
            $table->foreign('address_fld__street', 'tables_address_fld__street')
                ->references('id')
                ->on('table_fields')
                ->onDelete('set null');

            $table->unsignedInteger('address_fld__city')->nullable();
            $table->foreign('address_fld__city', 'tables_address_fld__city')
                ->references('id')
                ->on('table_fields')
                ->onDelete('set null');

            $table->unsignedInteger('address_fld__state')->nullable();
            $table->foreign('address_fld__state', 'tables_address_fld__state')
                ->references('id')
                ->on('table_fields')
                ->onDelete('set null');

            $table->unsignedInteger('address_fld__zipcode')->nullable();
            $table->foreign('address_fld__zipcode', 'tables_address_fld__zipcode')
                ->references('id')
                ->on('table_fields')
                ->onDelete('set null');

            $table->unsignedInteger('address_fld__country')->nullable();
            $table->foreign('address_fld__country', 'tables_address_fld__country')
                ->references('id')
                ->on('table_fields')
                ->onDelete('set null');

            $table->unsignedInteger('address_fld__lat')->nullable();
            $table->foreign('address_fld__lat', 'tables_address_fld__lat')
                ->references('id')
                ->on('table_fields')
                ->onDelete('set null');

            $table->unsignedInteger('address_fld__long')->nullable();
            $table->foreign('address_fld__long', 'tables_address_fld__long')
                ->references('id')
                ->on('table_fields')
                ->onDelete('set null');
        });

        Schema::table('plan_features', function (Blueprint $table) {
            $table->unsignedInteger('can_google_autocomplete')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tables', function (Blueprint $table) {
            $table->dropForeign('tables_address_fld__source_id');
            $table->dropColumn('address_fld__source_id');

            $table->dropForeign('tables_address_fld__street_address');
            $table->dropColumn('address_fld__street_address');

            $table->dropForeign('tables_address_fld__street');
            $table->dropColumn('address_fld__street');

            $table->dropForeign('tables_address_fld__city');
            $table->dropColumn('address_fld__city');

            $table->dropForeign('tables_address_fld__state');
            $table->dropColumn('address_fld__state');

            $table->dropForeign('tables_address_fld__zipcode');
            $table->dropColumn('address_fld__zipcode');

            $table->dropForeign('tables_address_fld__country');
            $table->dropColumn('address_fld__country');

            $table->dropForeign('tables_address_fld__lat');
            $table->dropColumn('address_fld__lat');

            $table->dropForeign('tables_address_fld__long');
            $table->dropColumn('address_fld__long');
        });

        Schema::table('plan_features', function (Blueprint $table) {
            $table->dropColumn('can_google_autocomplete');
        });
    }
}
