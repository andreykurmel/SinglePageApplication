<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableFieldLinkToDcrTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('table_field_link_to_dcr', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('table_field_link_id');
            $table->unsignedInteger('table_dcr_id');
            $table->tinyInteger('status')->default(1);
            $table->unsignedInteger('add_limit')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('table_field_link_to_dcr');
    }
}
