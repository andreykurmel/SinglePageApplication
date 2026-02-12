<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDescAndFilesToDdlItems extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ddl_items', function (Blueprint $table) {
            $table->string('description', 512)->nullable();
            $table->string('image_path', 255)->nullable();
            $table->string('show_option', 255)->nullable();
        });

        Schema::table('ddl_references', function (Blueprint $table) {
            $table->unsignedInteger('descr_field_id')->nullable();
            $table->unsignedInteger('image_field_id')->nullable();
            $table->unsignedInteger('color_field_id')->nullable();
            $table->unsignedInteger('max_selections_field_id')->nullable();
            $table->tinyInteger('has_individ_images')->default(1);
            $table->tinyInteger('has_individ_colors')->default(1);
            $table->tinyInteger('has_individ_max_selections')->default(1);

            $table->foreign('descr_field_id', 'ddl_references__descr_field')
                ->references('id')
                ->on('table_fields')
                ->onDelete('set null');

            $table->foreign('image_field_id', 'ddl_references__image_field')
                ->references('id')
                ->on('table_fields')
                ->onDelete('set null');

            $table->foreign('color_field_id', 'ddl_references__color_field')
                ->references('id')
                ->on('table_fields')
                ->onDelete('set null');

            $table->foreign('max_selections_field_id', 'ddl_references__max_selections_field')
                ->references('id')
                ->on('table_fields')
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
        Schema::table('ddl_items', function (Blueprint $table) {
            $table->dropColumn('description');
            $table->dropColumn('image_path');
            $table->dropColumn('show_option');
        });

        Schema::table('ddl_references', function (Blueprint $table) {
            $table->dropIndex('ddl_references__descr_field');
            $table->dropIndex('ddl_references__image_field');
            $table->dropIndex('ddl_references__color_field');
            $table->dropIndex('ddl_references__max_selections_field');
            $table->dropColumn('descr_field_id');
            $table->dropColumn('image_field_id');
            $table->dropColumn('color_field_id');
            $table->dropColumn('max_selections_field_id');
            $table->dropColumn('has_individ_images');
            $table->dropColumn('has_individ_colors');
            $table->dropColumn('has_individ_max_selections');
        });
    }
}
