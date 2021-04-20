<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class FixRowHashIssue extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mysql_correspondence')
            ->table('correspondence_apps', function (Blueprint $table) {
                $table->string('row_hash', 32)->nullable();
            });

        Schema::connection('mysql_correspondence')
            ->table('correspondence_tables', function (Blueprint $table) {
                $table->string('row_hash', 32)->nullable();
            });

        Schema::connection('mysql_correspondence')
            ->table('correspondence_fields', function (Blueprint $table) {
                $table->string('row_hash', 32)->nullable();
            });

        Schema::table('unit_conversion', function (Blueprint $table) {
            $table->string('row_hash', 32)->nullable();
        });

        Schema::table('units', function (Blueprint $table) {
            $table->string('row_hash', 32)->nullable();
        });

        Schema::table('user_clouds', function (Blueprint $table) {
            $table->string('row_hash', 32)->nullable();
        });

        Schema::table('user_connections', function (Blueprint $table) {
            $table->string('row_hash', 32)->nullable();
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->string('row_hash', 32)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('mysql_correspondence')
            ->table('correspondence_apps', function (Blueprint $table) {
                $table->dropColumn('row_hash');
            });

        Schema::connection('mysql_correspondence')
            ->table('correspondence_tables', function (Blueprint $table) {
                $table->dropColumn('row_hash');
            });

        Schema::connection('mysql_correspondence')
            ->table('correspondence_fields', function (Blueprint $table) {
                $table->dropColumn('row_hash');
            });

        Schema::table('unit_conversion', function (Blueprint $table) {
            $table->dropColumn('row_hash');
        });

        Schema::table('units', function (Blueprint $table) {
            $table->dropColumn('row_hash');
        });

        Schema::table('user_clouds', function (Blueprint $table) {
            $table->dropColumn('row_hash');
        });

        Schema::table('user_connections', function (Blueprint $table) {
            $table->dropColumn('row_hash');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn('row_hash');
        });
    }
}
