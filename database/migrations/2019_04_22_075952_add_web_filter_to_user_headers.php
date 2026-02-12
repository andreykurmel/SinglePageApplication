<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddWebFilterToUserHeaders extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_headers', function (Blueprint $table) {
            $table->tinyInteger('web')->default(1);
            $table->tinyInteger('filter')->default(0);
            $table->tinyInteger('popup_header')->default(0);
            $table->tinyInteger('popup_header_val')->default(0);
        });
        
        Schema::table('table_fields', function (Blueprint $table) {
            $table->dropColumn('web');
            $table->dropColumn('filter');
            $table->dropColumn('popup_header');
            $table->dropColumn('popup_header_val');

            $table->tinyInteger('global_web')->default(1);
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
            $table->tinyInteger('filter')->default(0);
            $table->tinyInteger('popup_header')->default(0);
            $table->tinyInteger('popup_header_val')->default(0);

            $table->dropColumn('global_web');
        });

        Schema::table('user_headers', function (Blueprint $table) {
            $table->dropColumn('web');
            $table->dropColumn('filter');
            $table->dropColumn('popup_header');
            $table->dropColumn('popup_header_val');
        });
    }
}
