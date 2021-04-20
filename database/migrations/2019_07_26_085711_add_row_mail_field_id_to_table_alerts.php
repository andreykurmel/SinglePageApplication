<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddRowMailFieldIdToTableAlerts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('table_alerts', function (Blueprint $table) {
            $table->unsignedInteger('row_mail_field_id')->nullable();

            $table->foreign('row_mail_field_id', 'table_alerts__row_mail_field_id')
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
        Schema::table('table_alerts', function (Blueprint $table) {
            $table->dropForeign('table_alerts__row_mail_field_id');
            $table->dropColumn('row_mail_field_id');
        });
    }
}
