<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTransparentToTablePermissions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('table_permissions', function (Blueprint $table) {
            $table->unsignedInteger('dcr_form_transparency')->default(0);
            $table->string('dcr_form_shadow_dir', 16)->default('BR');
            $table->string('dcr_email_field_static', 255)->nullable();
            $table->string('dcr_sec_scroll_style', 16)->default('scroll');
            $table->string('dcr_sec_background_by', 16)->default('color');
            $table->string('dcr_title_background_by', 16)->default('color');
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
            $table->dropColumn('dcr_form_transparency');
            $table->dropColumn('dcr_form_shadow_dir');
            $table->dropColumn('dcr_email_field_static');
            $table->dropColumn('dcr_sec_scroll_style');
            $table->dropColumn('dcr_sec_background_by');
            $table->dropColumn('dcr_title_background_by');
        });
    }
}
