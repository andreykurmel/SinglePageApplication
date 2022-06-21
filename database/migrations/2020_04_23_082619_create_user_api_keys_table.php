<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserApiKeysTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_api_keys', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->string('name', 255)->default('');
            $table->string('type', 32)->default('google');
            $table->string('key', 512);
            $table->string('air_base', 128)->nullable();
            $table->string('air_type', 128)->nullable();
            $table->string('notes', 255)->default('');
            $table->tinyInteger('is_active')->default(0);

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
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
        Schema::dropIfExists('user_api_keys');
    }
}
