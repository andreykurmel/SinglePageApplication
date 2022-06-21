<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddEnforcedThemeToTablePermissions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('table_permissions', function (Blueprint $table) {
            $table->unsignedInteger('enforced_theme')->default(0);
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
            $table->dropColumn('enforced_theme');
        });
    }
}
