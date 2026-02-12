<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableFieldLinkColumnsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('table_field_link_columns', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('table_link_id');
            $table->unsignedInteger('field_id');
            $table->string('field_db', 128);
            $table->tinyInteger('in_popup_display')->default(1);
            $table->tinyInteger('in_inline_display')->default(1);

            $table->foreign('table_link_id', 'tflc__table_link_id')
                ->references('id')
                ->on('table_field_links')
                ->onDelete('cascade');
            $table->foreign('field_id', 'tflc__field_id')
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
        Schema::dropIfExists('table_field_link_columns');
    }
}
