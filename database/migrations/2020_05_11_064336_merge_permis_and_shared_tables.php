<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MergePermisAndSharedTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_groups_2_table_permissions', function (Blueprint $table) {
            $table->unsignedInteger('table_permission_id')->nullable()->change();
            $table->unsignedInteger('table_id')->nullable();
            $table->tinyInteger('is_app')->default(0);
            $table->tinyInteger('is_hidden')->default(0);
            $table->renameColumn('active', 'is_active');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_groups_2_table_permissions', function (Blueprint $table) {
            $table->dropColumn('table_id');
            $table->dropColumn('is_app');
            $table->dropColumn('is_hidden');
            $table->renameColumn('is_active', 'active');
        });
    }
}
