<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeStaticPages extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('static_pages', function (Blueprint $table) {
            $table->unsignedInteger('parent_id')->nullable();
            $table->tinyInteger('is_folder')->default(0);

            $table->foreign('parent_id', 'static_pages__parent_id')
                ->references('id')
                ->on('static_pages')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('static_pages', function (Blueprint $table) {
            $table->dropForeign('static_pages__parent_id');
            $table->dropColumn('parent_id');
            $table->dropColumn('is_folder');
        });
    }
}
