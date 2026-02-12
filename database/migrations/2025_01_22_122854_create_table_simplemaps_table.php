<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableSimplemapsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('table_simplemaps', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('table_id');
            $table->unsignedInteger('user_id');
            $table->string('name', 255);
            $table->string('map', 32);
            $table->unsignedInteger('level_fld_id');
            $table->string('multirec_style', 32)->default('tabs');
            $table->unsignedInteger('multirec_fld_id')->nullable();
            $table->tinyInteger('smp_active')->default(1);
            $table->string('tb_smp_data_range', 16)->default('0');

            $table->string('smp_header_color', 16)->default('#DDD');
            $table->unsignedInteger('smp_card_width')->default(300);
            $table->unsignedInteger('smp_card_height')->nullable();
            $table->float('smp_card_max_height')->nullable();
            $table->unsignedInteger('smp_picture_field')->nullable();
            $table->unsignedInteger('smp_picture_width')->nullable();
            $table->string('smp_picture_position', 16)->default('right');
            $table->unsignedInteger('smp_value_fld_id')->nullable();
            $table->tinyInteger('smp_value_ddl_color')->default(0);
            $table->unsignedInteger('smp_color_fld_id')->nullable();
            $table->unsignedInteger('smp_theme_pop_link_id')->nullable();
            $table->string('smp_theme_pop_style', 32)->default('simple_pop');
            $table->unsignedInteger('smp_active_status_fld_id')->nullable();
            $table->unsignedInteger('smp_on_hover_fld_id')->nullable();
            $table->unsignedInteger('smp_legend_size')->default(12);
            $table->string('smp_legend_orientation', 32)->default('vertical');
            $table->unsignedInteger('smp_legend_pos_x')->default(0);
            $table->unsignedInteger('smp_legend_pos_y')->default(0);
            $table->string('locations_data_range', 16)->default('0');
            $table->unsignedInteger('locations_table_id')->nullable();
            $table->unsignedInteger('locations_name_fld_id')->nullable();
            $table->unsignedInteger('locations_lat_fld_id')->nullable();
            $table->unsignedInteger('locations_long_fld_id')->nullable();
            $table->unsignedInteger('locations_descr_fld_id')->nullable();
            $table->unsignedInteger('locations_icon_shape_fld_id')->nullable();
            $table->unsignedInteger('locations_icon_color_fld_id')->nullable();

            $table->foreign('table_id', 'table_simplemaps__table_id')
                ->references('id')
                ->on('tables')
                ->onDelete('cascade');
            $table->foreign('user_id', 'table_simplemaps__user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
            $table->foreign('level_fld_id', 'table_simplemaps__level_fld_id')
                ->references('id')
                ->on('table_fields')
                ->onDelete('cascade');
        });

        Schema::create('table_simplemaps_2_table_fields', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('table_simplemap_id');
            $table->unsignedInteger('table_field_id');
            $table->tinyInteger('is_header_show')->default(0);
            $table->tinyInteger('is_header_value')->default(0);
            $table->tinyInteger('table_show_name')->default(1);
            $table->tinyInteger('table_show_value')->default(1);
            $table->string('picture_style', 16)->default('scroll');
            $table->tinyInteger('cell_border')->default(1);
            $table->string('picture_fit', 16)->default('fill');
            $table->string('width_of_table_popup', 16)->default('full');
            $table->tinyInteger('is_start_table_popup')->default(0);
            $table->tinyInteger('is_table_field_in_popup')->default(0);
            $table->tinyInteger('is_hdr_lvl_one_row')->default(0);

            $table->foreign('table_field_id', 'table_simplemaps2tf__table_field_id')
                ->references('id')
                ->on('table_fields')
                ->onDelete('cascade');

            $table->foreign('table_simplemap_id', 'table_simplemaps2tf__table_simplemap_setting_id')
                ->references('id')
                ->on('table_simplemaps')
                ->onDelete('cascade');
        });

        Schema::create('table_simplemap_rights', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('table_simplemap_id');
            $table->unsignedInteger('table_permission_id');
            $table->tinyInteger('can_edit')->default(0);

            $table->foreign('table_simplemap_id', 'tsr__table_simplemap_id')
                ->references('id')
                ->on('table_simplemaps')
                ->onDelete('cascade');

            $table->foreign('table_permission_id', 'tsr__table_permission_id')
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
        Schema::dropIfExists('table_simplemap_rights');
        Schema::dropIfExists('table_simplemaps_2_table_fields');
        Schema::dropIfExists('table_simplemaps');
        Schema::dropIfExists('table_simplemaps');
    }
}
