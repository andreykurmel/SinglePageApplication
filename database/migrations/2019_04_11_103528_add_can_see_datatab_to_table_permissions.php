<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCanSeeDatatabToTablePermissions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('table_permissions', function (Blueprint $table) {
            $table->tinyInteger('can_see_datatab')->default(0);
            $table->string('datatab_methods', 6)->default('000000');
            $table->tinyInteger('datatab_only_append')->default(1);
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
            $table->dropColumn('can_see_datatab');
            $table->dropColumn('datatab_methods');
            $table->dropColumn('datatab_only_append');
        });
    }
}
