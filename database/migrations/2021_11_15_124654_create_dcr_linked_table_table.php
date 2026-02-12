<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDcrLinkedTableTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dcr_linked_tables', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 255)->default('Linked');
            $table->tinyInteger('is_active')->default(1);
            $table->unsignedInteger('table_request_id');
            $table->unsignedInteger('linked_table_id');
            $table->unsignedInteger('linked_permission_id')->nullable();
            $table->unsignedInteger('position_field_id')->nullable();
            $table->unsignedInteger('passed_ref_cond_id')->nullable();
            $table->string('header', 128)->nullable();
            $table->string('position', 128)->nullable();
            $table->string('style', 128)->nullable();
            $table->string('default_display', 32)->default('Table');
            $table->string('placement_tab_name', 128)->nullable();
            $table->unsignedInteger('placement_tab_order')->nullable();
            $table->unsignedInteger('max_nbr_rcds_embd')->nullable();
            $table->unsignedInteger('max_height_inline_embd')->default(0);
            $table->tinyInteger('embd_table')->default(1);
            $table->tinyInteger('embd_listing')->default(1);
            $table->tinyInteger('embd_board')->default(1);
            $table->tinyInteger('embd_stats')->nullable();
            $table->tinyInteger('embd_fit_width')->nullable();
            $table->string('embd_table_align', 32)->default('start');
            $table->tinyInteger('embd_float_actions')->nullable();
            $table->unsignedInteger('listing_field_id')->nullable();
            $table->float('listing_rows_width')->default(250);
            $table->float('listing_rows_min_width')->default(70);
            $table->unsignedInteger('board_view_height')->default(100);
            $table->float('board_title_width')->default(0.30);
            $table->float('board_image_width')->default(0.30);
            $table->unsignedInteger('board_image_height')->default(30);
            $table->unsignedInteger('board_image_fld_id')->nullable();
            $table->string('board_display_position', 16)->nullable();
            $table->string('board_display_view', 16)->default('scroll');
            $table->string('board_display_fit', 16)->default('fill');

            $table->tinyInteger('ctlg_is_active')->default(0);
            $table->unsignedInteger('ctlg_columns_number')->default(3);
            $table->string('ctlg_data_range', 16)->default('0');
            $table->unsignedInteger('ctlg_table_id')->nullable();
            $table->unsignedInteger('ctlg_distinct_field_id')->nullable();
            $table->unsignedInteger('ctlg_parent_link_field_id')->nullable();
            $table->unsignedInteger('ctlg_parent_quantity_field_id')->nullable();
            $table->string('ctlg_visible_field_ids', 255)->nullable();
            $table->string('ctlg_filter_field_ids', 255)->nullable();
            $table->string('ctlg_display_option', 16)->default('inline');
            $table->unsignedInteger('ctlg_board_view_height')->default(100);
            $table->float('ctlg_board_title_width')->default(0.30);
            $table->float('ctlg_board_image_width')->default(0.30);
            $table->unsignedInteger('ctlg_board_image_height')->default(30);
            $table->unsignedInteger('ctlg_board_image_fld_id')->nullable();
            $table->string('ctlg_board_display_position', 16)->nullable();
            $table->string('ctlg_board_display_view', 16)->default('scroll');
            $table->string('ctlg_board_display_fit', 16)->default('fill');

            $table->foreign('table_request_id', 'dcr_linked_table_table_request_id')
                ->references('id')
                ->on('table_data_requests')
                ->onDelete('cascade');

            $table->foreign('linked_table_id', 'dcr_linked_table_linked_table_id')
                ->references('id')
                ->on('tables')
                ->onDelete('cascade');

            $table->foreign('ctlg_table_id', 'dcr_linked_table_ctlg_table_id')
                ->references('id')
                ->on('tables')
                ->onDelete('set null');

            $table->foreign('ctlg_distinct_field_id', 'dcr_linked_table_ctlg_distinct_field_id')
                ->references('id')
                ->on('table_fields')
                ->onDelete('set null');

            $table->foreign('ctlg_parent_link_field_id', 'dcr_linked_table_ctlg_parent_link_field_id')
                ->references('id')
                ->on('table_fields')
                ->onDelete('set null');

            $table->foreign('ctlg_parent_quantity_field_id', 'dcr_linked_table_ctlg_parent_quantity_field_id')
                ->references('id')
                ->on('table_fields')
                ->onDelete('set null');

            $table->foreign('ctlg_board_image_fld_id', 'dcr_linked_table_ctlg_board_image_fld_id')
                ->references('id')
                ->on('table_fields')
                ->onDelete('set null');

            $table->foreign('linked_permission_id', 'dcr_linked_table_linked_permission_id')
                ->references('id')
                ->on('table_permissions')
                ->onDelete('cascade');

            $table->foreign('position_field_id', 'dcr_linked_table_position_field_id')
                ->references('id')
                ->on('table_fields')
                ->onDelete('cascade');

            $table->foreign('passed_ref_cond_id', 'dcr_linked_table_passed_ref_cond_id')
                ->references('id')
                ->on('table_ref_conditions')
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
        Schema::dropIfExists('dcr_linked_table');
    }
}
