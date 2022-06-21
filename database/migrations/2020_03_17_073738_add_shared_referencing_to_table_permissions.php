<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSharedReferencingToTablePermissions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('table_permissions', function (Blueprint $table) {
            $table->tinyInteger('referencing_shared')->default(0)->after('can_reference');
        });

        Schema::dropIfExists('folder_permissions_2_tables');
        Schema::dropIfExists('folder_permissions');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('table_permissions', function (Blueprint $table) {
            $table->dropColumn('referencing_shared');
        });
    }
}
