<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFloatingColumnsToTableFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('table_fields', function (Blueprint $table) {
            $table->string('ddl_style', 16)->default('ddl');
        });

        Schema::table('user_headers', function (Blueprint $table) {
            $table->tinyInteger('is_floating')->default(0);
            $table->string('filter_type', 16)->default('value');
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
            $table->dropColumn('ddl_style');
        });

        Schema::table('user_headers', function (Blueprint $table) {
            $table->dropColumn('is_floating');
            $table->dropColumn('filter_type');
        });
    }
}
