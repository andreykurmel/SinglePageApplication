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
        if (! Schema::connection('mysql_correspondence')->hasTable('correspondence_apps')) {
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
                    $table->string('app_path', 512)->nullable();
                    $table->string('subdomain', 64)->nullable();
                    $table->string('icon_full_path', 512)->nullable();
                    $table->string('row_hash', 32)->nullable();
                    $table->string('code', 64)->nullable();
                    $table->string('type', 64)->nullable();
                    $table->string('controller', 255)->nullable();
                    $table->tinyInteger('open_as_popup')->default(0);
                });
        }
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
