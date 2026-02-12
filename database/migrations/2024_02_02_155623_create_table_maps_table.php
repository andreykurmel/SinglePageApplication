<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableMapsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('table_maps', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('table_id');
            $table->string('name', 128);
            $table->string('map_data_range', 32)->default('0');
            $table->unsignedInteger('map_active')->default(1);
            $table->string('description', 255)->nullable();

            $table->unsignedInteger('map_multiinfo')->nullable();
            $table->unsignedInteger('map_icon_field_id')->nullable();
            $table->string('map_icon_style', 16)->default('dist');
            $table->unsignedInteger('map_position_refid')->nullable();
            $table->unsignedInteger('map_popup_hdr_id')->nullable();
            $table->unsignedInteger('map_popup_width')->default(450);
            $table->unsignedInteger('map_popup_height')->default(300);
            $table->string('map_popup_header_color', 16)->default('#DDD');
            $table->string('map_picture_style', 16)->default('scroll');
            $table->string('map_picture_position', 16)->default('right');
            $table->unsignedInteger('map_picture_field')->nullable();
            $table->float('map_picture_width')->default('30');
            $table->float('map_picture_min_width')->default('30');

            $table->foreign('table_id', 'table_maps__table_id')
                ->references('id')
                ->on('tables')
                ->onDelete('cascade');
        });

        Schema::create('table_map_field_settings', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('table_map_id');
            $table->unsignedInteger('table_field_id');
            $table->tinyInteger('is_lat_field')->nullable();
            $table->tinyInteger('is_long_field')->nullable();
            $table->tinyInteger('info_box')->nullable();
            $table->tinyInteger('is_info_header_field')->nullable();
            $table->tinyInteger('is_info_header_value')->nullable();

            $table->foreign('table_field_id', 'table_map_fields__table_field_id')
                ->references('id')
                ->on('table_fields')
                ->onDelete('cascade');

            $table->foreign('table_map_id', 'table_map_fields__table_map_id')
                ->references('id')
                ->on('table_maps')
                ->onDelete('cascade');
        });
        
        Schema::create('table_map_rights', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('table_map_id');
            $table->unsignedInteger('table_permission_id');
            $table->tinyInteger('can_edit')->default(0);

            $table->foreign('table_map_id', 'tmr__table_map_id')
                ->references('id')
                ->on('table_maps')
                ->onDelete('cascade');

            $table->foreign('table_permission_id', 'tmr__table_permission_id')
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
        Schema::dropIfExists('table_map_field_settings');
        Schema::dropIfExists('table_map_rights');
        Schema::dropIfExists('table_maps');
    }
}
