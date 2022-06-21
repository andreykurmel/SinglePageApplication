<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDragRowsToPlanFeatures extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('plan_features', function (Blueprint $table) {
            $table->unsignedInteger('drag_rows')->nullable()->after('ddl_ref');
        });

        Schema::table('table_permissions', function (Blueprint $table) {
            $table->tinyInteger('can_drag_rows')->default(0);
        });



        Schema::connection('mysql_correspondence')
            ->table('correspondence_apps', function (Blueprint $table) {
                $table->integer('row_order')->default(0);
            });

        Schema::connection('mysql_correspondence')
            ->table('correspondence_tables', function (Blueprint $table) {
                $table->integer('row_order')->default(0);
            });

        Schema::connection('mysql_correspondence')
            ->table('correspondence_fields', function (Blueprint $table) {
                $table->integer('row_order')->default(0);
            });

        Schema::table('unit_conversion', function (Blueprint $table) {
            $table->integer('row_order')->default(0);
        });

        Schema::table('units', function (Blueprint $table) {
            $table->integer('row_order')->default(0);
        });

        Schema::table('user_clouds', function (Blueprint $table) {
            $table->integer('row_order')->default(0);
        });

        Schema::table('user_connections', function (Blueprint $table) {
            $table->integer('row_order')->default(0);
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->integer('row_order')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('plan_features', function (Blueprint $table) {
            $table->dropColumn('drag_rows');
        });

        Schema::table('table_permissions', function (Blueprint $table) {
            $table->dropColumn('can_drag_rows');
        });



        Schema::connection('mysql_correspondence')
            ->table('correspondence_apps', function (Blueprint $table) {
                $table->dropColumn('row_order');
            });

        Schema::connection('mysql_correspondence')
            ->table('correspondence_tables', function (Blueprint $table) {
                $table->dropColumn('row_order');
            });

        Schema::connection('mysql_correspondence')
            ->table('correspondence_fields', function (Blueprint $table) {
                $table->dropColumn('row_order');
            });

        Schema::table('unit_conversion', function (Blueprint $table) {
            $table->dropColumn('row_order');
        });

        Schema::table('units', function (Blueprint $table) {
            $table->dropColumn('row_order');
        });

        Schema::table('user_clouds', function (Blueprint $table) {
            $table->dropColumn('row_order');
        });

        Schema::table('user_connections', function (Blueprint $table) {
            $table->dropColumn('row_order');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn('row_order');
        });
    }
}
