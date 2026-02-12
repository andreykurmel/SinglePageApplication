<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableAisTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('table_ais', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('table_id');
            $table->unsignedInteger('user_id')->nullable();
            $table->string('name', 128);
            $table->unsignedInteger('openai_key_id');
            $table->string('related_table_ids', 512);
            $table->string('ai_data_range', 32)->default('-2');
            $table->string('bg_color', 16)->nullable();
            $table->string('bg_me_color', 16)->nullable();
            $table->string('bg_gpt_color', 16)->nullable();
            $table->string('txt_color', 16)->nullable();
            $table->string('font_family', 32)->nullable();
            $table->string('font_style', 64)->nullable();
            $table->unsignedInteger('font_size')->nullable();
            $table->string('description', 255)->nullable();
            $table->tinyInteger('with_table_data')->default(0);
            $table->tinyInteger('with_outside_data')->default(0);
            $table->tinyInteger('is_right_panel')->default(0);

            $table->foreign('table_id', 'table_ais__table_id')
                ->references('id')
                ->on('tables')
                ->onDelete('cascade');
            $table->foreign('openai_key_id', 'table_ais__openai_key_id')
                ->references('id')
                ->on('user_api_keys')
                ->onDelete('cascade');
        });

        Schema::create('table_ai_rights', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('table_ai_id');
            $table->unsignedInteger('table_permission_id');
            $table->tinyInteger('can_edit')->default(0);

            $table->foreign('table_ai_id', 'table_ai_rights__table_ai_id')
                ->references('id')
                ->on('table_ais')
                ->onDelete('cascade');

            $table->foreign('table_permission_id', 'table_ai_rights__table_permission_id')
                ->references('id')
                ->on('table_permissions')
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
        Schema::dropIfExists('table_ai_rights');
        Schema::dropIfExists('table_ais');
    }
}
