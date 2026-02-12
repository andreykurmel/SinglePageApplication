<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeCorrespondencesForInheritances extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mysql_correspondence')
            ->table('correspondence_stim_3d', function (Blueprint $table) {
                $table->string('style', 32)->default('vh_tabs');

                $table->dropColumn('inheritance_3d');
                $table->dropColumn('inheritance_type');

                $table->string('table', 64)->nullable()->change();
            });

        Schema::connection('mysql_correspondence')
            ->table('correspondence_fields', function (Blueprint $table) {
                $table->string('link_field_type', 255)->nullable();
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
            ->table('correspondence_stim_3d', function (Blueprint $table) {
                $table->dropColumn('style');

                $table->string('inheritance_3d', 64)->nullable();
                $table->string('inheritance_type', 255)->nullable();
            });

        Schema::connection('mysql_correspondence')
            ->table('correspondence_fields', function (Blueprint $table) {
                $table->dropColumn('link_field_type', 255);
            });
    }
}
