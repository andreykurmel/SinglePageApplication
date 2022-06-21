<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemoveWebFromFieldsAndHeaders extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('table_fields', function (Blueprint $table) {
            $table->dropColumn('web');
            $table->dropColumn('global_web');
        });
        Schema::table('user_headers', function (Blueprint $table) {
            $table->dropColumn('web');
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
            $table->tinyInteger('web')->default(1);
            $table->tinyInteger('global_web')->default(1);
        });
        Schema::table('user_headers', function (Blueprint $table) {
            $table->tinyInteger('web')->default(1);
        });
    }
}
