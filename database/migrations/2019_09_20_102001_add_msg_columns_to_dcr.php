<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMsgColumnsToDcr extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('table_permissions', function (Blueprint $table) {
            $table->string('dcr_email_subject', 255)->nullable();
            $table->string('dcr_email_message', 512)->nullable();
            $table->string('dcr_email_format', 32)->default('table');
            $table->unsignedInteger('dcr_email_col_group_id')->nullable();

            $table->foreign('dcr_email_col_group_id', 'table_permissions__dcr_email_col_group_id')
                ->references('id')
                ->on('table_column_groups')
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
            $table->dropForeign('table_permissions__dcr_email_col_group_id');
            $table->dropColumn('dcr_email_subject');
            $table->dropColumn('dcr_email_message');
            $table->dropColumn('dcr_email_format');
            $table->dropColumn('dcr_email_col_group_id');
        });
    }
}
