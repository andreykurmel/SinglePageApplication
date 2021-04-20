<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddLinesToTablePermissionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('table_permissions', function (Blueprint $table) {
            $table->tinyInteger('dcr_title_line_bot')->default(1);
            $table->string('dcr_line_color', 16)->nullable();
        });

        Schema::table('table_fields', function (Blueprint $table) {
            $table->string('placeholder_content', 64)->nullable();
            $table->tinyInteger('placeholder_only_form')->default(0);
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
            $table->dropColumn('dcr_title_line_bot');
            $table->dropColumn('dcr_line_color');
        });

        Schema::table('table_fields', function (Blueprint $table) {
            $table->dropColumn('placeholder_content');
            $table->dropColumn('placeholder_only_form');
        });
    }
}
