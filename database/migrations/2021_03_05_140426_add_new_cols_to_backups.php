<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNewColsToBackups extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('table_backups', function (Blueprint $table) {
            $table->string('timezone', 64)->nullable();
            $table->unsignedInteger('bkp_email_field_id')->nullable();
            $table->unsignedInteger('bkp_addressee_field_id')->nullable();
            $table->string('bkp_email_field_static', 255)->nullable();
            $table->string('bkp_email_subject', 255)->nullable();
            $table->string('bkp_addressee_txt', 255)->nullable();
            $table->string('bkp_email_message', 512)->nullable();

            $table->foreign('bkp_email_field_id', 'table_backups__bkp_email_field_id')
                ->references('id')
                ->on('table_fields')
                ->onDelete('set null');
            $table->foreign('bkp_addressee_field_id', 'table_backups__bkp_addressee_field_id')
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
        Schema::table('table_backups', function (Blueprint $table) {
            $table->dropForeign('table_backups__bkp_email_field_id');
            $table->dropForeign('table_backups__bkp_addressee_field_id');

            $table->dropColumn('bkp_email_field_id');
            $table->dropColumn('bkp_addressee_field_id');
            $table->dropColumn('bkp_email_field_static');
            $table->dropColumn('bkp_email_subject');
            $table->dropColumn('bkp_addressee_txt');
            $table->dropColumn('bkp_email_message');
            $table->dropColumn('timezone');
        });
    }
}
