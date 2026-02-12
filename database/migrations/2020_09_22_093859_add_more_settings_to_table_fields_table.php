<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMoreSettingsToTableFieldsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tables', function (Blueprint $table) {
            $table->tinyInteger('unit_conv_by_user')->default(0);
            $table->tinyInteger('unit_conv_by_system')->default(1);
            $table->tinyInteger('unit_conv_by_lib')->default(1);

            $table->text('import_web_scrap_save')->nullable();
            $table->text('import_gsheet_save')->nullable();
            $table->text('import_airtable_save')->nullable();
            $table->text('import_transpose_save')->nullable();
            $table->text('import_jira_save')->nullable();
            $table->text('import_salesforce_save')->nullable();
            $table->text('import_csv_save')->nullable();
            $table->text('import_mysql_save')->nullable();
            $table->text('import_paste_save')->nullable();
            $table->text('import_table_ocr_save')->nullable();
            $table->text('import_last_cols_corresp')->nullable();
            $table->dateTime('import_last_jira_action')->nullable();
            $table->dateTime('import_last_salesforce_action')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tables', function (Blueprint $table) {
            $table->dropColumn('unit_conv_by_user');
            $table->dropColumn('unit_conv_by_system');
            $table->dropColumn('unit_conv_by_lib');

            $table->dropColumn('import_web_scrap_save');
            $table->dropColumn('import_gsheet_save');
            $table->dropColumn('import_airtable_save');
            $table->dropColumn('import_transpose_save');
            $table->dropColumn('import_jira_save');
            $table->dropColumn('import_salesforce_save');
            $table->dropColumn('import_csv_save');
            $table->dropColumn('import_mysql_save');
            $table->dropColumn('import_paste_save');
            $table->dropColumn('import_table_ocr_save');
            $table->dropColumn('import_last_cols_corresp');
            $table->dropColumn('import_last_jira_action');
            $table->dropColumn('import_last_salesforce_action');
        });
    }
}
