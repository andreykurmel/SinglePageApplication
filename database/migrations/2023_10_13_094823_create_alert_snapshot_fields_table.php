<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAlertSnapshotFieldsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('table_alerts', function (Blueprint $table) {
            $table->string('snp_name', 255)->nullable();
            $table->unsignedInteger('snp_field_id_name')->nullable();
            $table->unsignedInteger('snp_field_id_time')->nullable();
            $table->unsignedInteger('snp_src_table_id')->nullable();
            $table->unsignedInteger('snp_row_group_id')->nullable();
            $table->string('snp_data_range', 16)->default('0');

            $table->foreign('snp_field_id_name', 'ta__snp_field_id_name')
                ->references('id')
                ->on('table_fields')
                ->onDelete('cascade');

            $table->foreign('snp_field_id_time', 'ta__snp_field_id_time')
                ->references('id')
                ->on('table_fields')
                ->onDelete('cascade');

            $table->foreign('snp_src_table_id', 'ta__snp_src_table_id')
                ->references('id')
                ->on('tables')
                ->onDelete('cascade');
        });

        Schema::create('table_alert_snapshot_fields', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('table_alert_id');
            $table->unsignedInteger('current_field_id');
            $table->unsignedInteger('source_field_id');

            $table->foreign('table_alert_id', 'asf__table_alert_id')
                ->references('id')
                ->on('table_alerts')
                ->onDelete('cascade');

            $table->foreign('current_field_id', 'asf__current_field_id')
                ->references('id')
                ->on('table_fields')
                ->onDelete('cascade');

            $table->foreign('source_field_id', 'asf__source_field_id')
                ->references('id')
                ->on('table_fields')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('table_kanban_rights');

        Schema::table('table_alerts', function (Blueprint $table) {
            $table->dropForeign('ta__snp_field_id_name');
            $table->dropForeign('ta__snp_field_id_time');
            $table->dropForeign('ta__snp_src_table_id');

            $table->dropColumn('snp_name');
            $table->dropColumn('snp_field_id_name');
            $table->dropColumn('snp_field_id_time');
            $table->dropColumn('snp_src_table_id');
            $table->dropColumn('snp_row_group_id');
            $table->dropColumn('snp_data_range');
        });
    }
}
