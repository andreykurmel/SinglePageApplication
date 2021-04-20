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
            $table->unsignedInteger('add_record_limit')->nullable();
            $table->unsignedInteger('already_added_records')->nullable();
            $table->string('link_preview_fields', 255)->nullable();
            $table->unsignedInteger('link_preview_show_flds')->default(1);

            $table->unsignedInteger('payment_method_fld_id')->nullable();
            $table->unsignedInteger('payment_paypal_keys_id')->nullable();
            $table->unsignedInteger('payment_stripe_keys_id')->nullable();
            $table->unsignedInteger('payment_amount_fld_id')->nullable();
            $table->unsignedInteger('payment_history_payee_fld_id')->nullable();
            $table->unsignedInteger('payment_history_amount_fld_id')->nullable();
            $table->unsignedInteger('payment_history_date_fld_id')->nullable();
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
            $table->dropColumn('add_record_limit');
            $table->dropColumn('already_added_records');
            $table->dropColumn('link_preview_fields');
            $table->dropColumn('link_preview_show_flds');

            $table->dropColumn('payment_method_fld_id');
            $table->dropColumn('payment_paypal_keys_id');
            $table->dropColumn('payment_stripe_keys_id');
            $table->dropColumn('payment_amount_fld_id');
            $table->dropColumn('payment_history_payee_fld_id');
            $table->dropColumn('payment_history_amount_fld_id');
            $table->dropColumn('payment_history_date_fld_id');
        });
    }
}
