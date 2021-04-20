<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDefaultsToWidthsInUserHeaders extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_headers', function (Blueprint $table) {
            $table->unsignedInteger('min_width')->default(10)->change();
            $table->unsignedInteger('max_width')->default(500)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_headers', function (Blueprint $table) {
            $table->unsignedInteger('min_width')->default(0)->change();
            $table->unsignedInteger('max_width')->default(0)->change();
        });
    }
}
