<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddShowSumTiLinks extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('table_field_links', function (Blueprint $table) {
            $table->tinyInteger('show_sum')->default(0);
            $table->tinyInteger('floating_action')->default(0);
            $table->string('table_def_align', 32)->default('start');
            $table->tinyInteger('table_fit_width')->default(0);
            $table->unsignedInteger('add_record_limit')->nullable();
            $table->unsignedInteger('already_added_records')->nullable();
            $table->string('link_preview_fields', 255)->nullable();
            $table->string('email_addon_fields', 255)->nullable();
            $table->unsignedInteger('link_preview_show_flds')->default(1);
            $table->unsignedInteger('history_fld_id')->nullable();
            $table->unsignedInteger('linked_report_id')->nullable();
            $table->string('inline_style', 16)->default('regular');
            $table->unsignedInteger('inline_in_vert_table')->nullable();
            $table->unsignedInteger('inline_is_opened')->nullable();
            $table->string('inline_width', 16)->default('full');
            $table->unsignedInteger('inline_hide_tab')->nullable();
            $table->unsignedInteger('inline_hide_boundary')->nullable();
            $table->unsignedInteger('inline_hide_padding')->nullable();
            $table->unsignedInteger('max_height_in_vert_table')->default(400);
            $table->unsignedInteger('can_row_add')->default(1);
            $table->unsignedInteger('can_row_delete')->default(1);

            $table->unsignedInteger('payment_method_fld_id')->nullable();
            $table->unsignedInteger('payment_paypal_keys_id')->nullable();
            $table->unsignedInteger('payment_stripe_keys_id')->nullable();
            $table->unsignedInteger('payment_description_fld_id')->nullable();
            $table->unsignedInteger('payment_amount_fld_id')->nullable();
            $table->unsignedInteger('payment_customer_fld_id')->nullable();
            $table->unsignedInteger('payment_history_payee_fld_id')->nullable();
            $table->unsignedInteger('payment_history_amount_fld_id')->nullable();
            $table->unsignedInteger('payment_history_date_fld_id')->nullable();

            $table->unsignedInteger('board_view_height')->default(100);
            $table->float('board_title_width')->default(0.30);
            $table->float('board_image_width')->default(0.30);
            $table->unsignedInteger('board_image_height')->default(30);
            $table->unsignedInteger('board_image_fld_id')->nullable();
            $table->string('board_display_position', 16)->nullable();
            $table->string('board_display_view', 16)->default('scroll');
            $table->string('board_display_fit', 16)->default('fill');

            $table->string('avail_addons', 255)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('table_field_links', function (Blueprint $table) {
            $table->dropColumn('show_sum');
            $table->dropColumn('floating_action');
            $table->dropColumn('table_def_align');
            $table->dropColumn('table_fit_width');
            $table->dropColumn('add_record_limit');
            $table->dropColumn('already_added_records');
            $table->dropColumn('link_preview_fields');
            $table->dropColumn('link_preview_show_flds');
            $table->dropColumn('email_addon_fields');
            $table->dropColumn('history_fld_id');
            $table->dropColumn('linked_report_id');
            $table->dropColumn('inline_in_vert_table');
            $table->dropColumn('inline_is_opened');
            $table->dropColumn('inline_hide_tab');
            $table->dropColumn('inline_hide_boundary');
            $table->dropColumn('inline_hide_padding');
            $table->dropColumn('max_height_in_vert_table');
            $table->dropColumn('can_row_add');
            $table->dropColumn('can_row_delete');

            $table->dropColumn('payment_method_fld_id');
            $table->dropColumn('payment_paypal_keys_id');
            $table->dropColumn('payment_stripe_keys_id');
            $table->dropColumn('payment_description_fld_id');
            $table->dropColumn('payment_amount_fld_id');
            $table->dropColumn('payment_customer_fld_id');
            $table->dropColumn('payment_history_payee_fld_id');
            $table->dropColumn('payment_history_amount_fld_id');
            $table->dropColumn('payment_history_date_fld_id');

            $table->dropColumn('board_view_height');
            $table->dropColumn('board_title_width');
            $table->dropColumn('board_image_width');
            $table->dropColumn('board_image_height');
            $table->dropColumn('board_image_fld_id');
            $table->dropColumn('board_display_position');
            $table->dropColumn('board_display_view');
            $table->dropColumn('board_display_fit');

            $table->dropColumn('avail_addons');
        });
    }
}
