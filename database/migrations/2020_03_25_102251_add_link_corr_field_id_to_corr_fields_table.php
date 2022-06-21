<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddLinkCorrFieldIdToCorrFieldsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mysql_correspondence')
            ->table('correspondence_fields', function (Blueprint $table) {
                $table->string('link_table_db')->nullable();
                $table->string('link_field_db')->nullable();
            });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('mysql_correspondence')
            ->table('correspondence_fields', function (Blueprint $table) {
                $table->dropColumn('link_table_db');
                $table->dropColumn('link_field_db');
            });
    }
}
