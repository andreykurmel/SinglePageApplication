<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCorrespondenceAppTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mysql_correspondence')
            ->create('correspondence_apps', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('user_id')->nullable();
                $table->tinyInteger('is_active')->default(1);
                $table->tinyInteger('is_public')->default(1);
                $table->string('name');
                $table->string('host')->nullable();
                $table->string('login')->nullable();
                $table->string('pass')->nullable();
                $table->string('db');
                $table->string('notes', 512)->nullable();
            });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('mysql_correspondence')->dropIfExists('correspondence_apps');
    }
}
