<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCanCreateViewToTablePermissions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('table_permissions', function (Blueprint $table) {
            $table->tinyInteger('can_create_view')->default(0)->after('can_reference');
            $table->tinyInteger('can_create_condformat')->default(0)->after('can_create_view');
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
            $table->dropColumn('can_create_view');
            $table->dropColumn('can_create_condformat');
        });
    }
}
