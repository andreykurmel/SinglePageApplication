<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPermissionsToTableViews extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('table_views', function (Blueprint $table) {
            $table->tinyInteger('view_filtering')->nullable();
            $table->unsignedInteger('access_permission_id')->nullable()->after('data');

            $table->foreign('access_permission_id', 'table_views__table_permissions')
                ->references('id')
                ->on('table_permissions')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('table_views', function (Blueprint $table) {
            $table->dropForeign('table_views__table_permissions');
            $table->dropColumn('access_permission_id');
            $table->dropColumn('view_filtering');
        });
    }
}
