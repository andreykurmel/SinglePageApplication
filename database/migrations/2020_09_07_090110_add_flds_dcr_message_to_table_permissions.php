<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFldsDcrMessageToTablePermissions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('table_permissions', function (Blueprint $table) {
            $table->string('dcr_message_font', 64)->nullable();
            $table->integer('dcr_message_size')->nullable();
            $table->string('dcr_message_color', 16)->nullable();
            $table->unsignedInteger('dcr_addressee_field_id')->nullable();
            $table->tinyInteger('dcr_form_shadow')->default(0);

            $table->foreign('dcr_addressee_field_id', 'table_permissions__dcr_addressee_field_id')
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
            $table->dropForeign('table_permissions__dcr_addressee_field_id');

            $table->dropColumn('dcr_addressee_field_id');
            $table->dropColumn('dcr_form_shadow');
            $table->dropColumn('dcr_message_font');
            $table->dropColumn('dcr_message_size');
            $table->dropColumn('dcr_message_color');
        });
    }
}
