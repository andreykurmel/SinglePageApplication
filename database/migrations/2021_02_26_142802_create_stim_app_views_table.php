<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStimAppViewsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::connection('mysql_correspondence')
            ->create('stim_app_views', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('user_id');
                $table->string('v_tab', 32);
                $table->string('v_select', 32);
                $table->string('source_string', 255);
                $table->unsignedInteger('master_row_id');
                $table->string('name', 64);
                $table->string('hash', 64);
                $table->string('side_top', 16)->default('');
                $table->string('side_left', 16)->default('');
                $table->string('side_right', 16)->default('');
                $table->tinyInteger('is_active')->default(0);
                $table->tinyInteger('is_locked')->default(0);
                $table->string('lock_pass', 64)->nullable();
            });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('mysql_correspondence')->dropIfExists('stim_app_views');
    }
}
