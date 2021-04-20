<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddUnfinishedSettsToTablePermissionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('table_permissions', function (Blueprint $table) {
            $table->unsignedInteger('dcr_record_url_field_id')->nullable();
            $table->unsignedInteger('dcr_record_status_id')->nullable();
            $table->unsignedInteger('dcr_record_allow_unfinished')->nullable();
            $table->unsignedInteger('dcr_record_visibility_id')->nullable();
            $table->unsignedInteger('dcr_record_visibility_def')->nullable();
            $table->unsignedInteger('dcr_record_editability_id')->nullable();
            $table->unsignedInteger('dcr_record_editability_def')->nullable();

            $table->foreign('dcr_record_status_id', 'table_permissions__dcr_record_status_id')
                ->references('id')
                ->on('table_fields')
                ->onDelete('set null');
            $table->foreign('dcr_record_url_field_id', 'table_permissions__dcr_record_url_field_id')
                ->references('id')
                ->on('table_fields')
                ->onDelete('set null');
            $table->foreign('dcr_record_visibility_id', 'table_permissions__dcr_record_visibility_id')
                ->references('id')
                ->on('table_fields')
                ->onDelete('set null');
            $table->foreign('dcr_record_editability_id', 'table_permissions__dcr_record_editability_id')
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
        Schema::table('table_permissions', function (Blueprint $table) {
            $table->dropForeign('table_permissions__dcr_record_status_id');
            $table->dropForeign('table_permissions__dcr_record_url_field_id');
            $table->dropForeign('table_permissions__dcr_record_visibility_id');
            $table->dropForeign('table_permissions__dcr_record_editability_id');

            $table->dropColumn('dcr_record_status_id');
            $table->dropColumn('dcr_record_url_field_id');
            $table->dropColumn('dcr_record_status_id');
            $table->dropColumn('dcr_record_allow_unfinished');
            $table->dropColumn('dcr_record_visibility_id');
            $table->dropColumn('dcr_record_visibility_def');
            $table->dropColumn('dcr_record_editability_id');
            $table->dropColumn('dcr_record_editability_def');
        });
    }
}
