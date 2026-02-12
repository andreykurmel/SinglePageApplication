<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableGroupingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('table_groupings', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('table_id');
            $table->string('name', 128);
            $table->string('description', 255)->nullable();
            $table->tinyInteger('rg_active')->default(1);
            $table->string('rg_data_range', 16)->default('0');
            $table->string('rg_alignment', 16)->default('left');
            $table->unsignedInteger('rg_colgroup_id')->nullable();

            $table->foreign('table_id', 'table_groupings__table_id')
                ->references('id')
                ->on('tables')
                ->onDelete('cascade');
        });

        Schema::create('table_grouping_fields', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('grouping_id');
            $table->unsignedInteger('field_id');
            $table->tinyInteger('field_name_visible')->default(1);
            $table->string('sorting', 16)->nullable();
            $table->string('color', 16)->nullable();
            $table->unsignedInteger('indent')->default(20);
            $table->string('default_state', 16)->default('expanded');
            $table->tinyInteger('rg_active')->default(1);
            $table->unsignedInteger('row_order')->default(1);

            $table->foreign('grouping_id', 'table_grouping_fields__grouping_id')
                ->references('id')
                ->on('table_groupings')
                ->onDelete('cascade');
            $table->foreign('field_id', 'table_grouping_fields__field_id')
                ->references('id')
                ->on('table_fields')
                ->onDelete('cascade');
        });

        Schema::create('table_grouping_rights', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('table_grouping_id');
            $table->unsignedInteger('table_permission_id');
            $table->tinyInteger('can_edit')->default(0);

            $table->foreign('table_grouping_id', 'tgror__table_grouping_id')
                ->references('id')
                ->on('table_groupings')
                ->onDelete('cascade');

            $table->foreign('table_permission_id', 'tgror__table_permission_id')
                ->references('id')
                ->on('table_permissions')
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
        Schema::dropIfExists('table_grouping_rights');
        Schema::dropIfExists('table_groupings');
    }
}
