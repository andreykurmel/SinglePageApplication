<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSubmissionEmailFieldIdToTablePermissions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('table_permissions', function (Blueprint $table) {
            $table->unsignedInteger('dcr_email_field_id')->nullable();

            $table->foreign('dcr_email_field_id', 'table_permissions__dcr_email_field_id')
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
            $table->dropForeign('table_permissions__dcr_email_field_id');
            $table->dropColumn('dcr_email_field_id');
        });
    }
}
