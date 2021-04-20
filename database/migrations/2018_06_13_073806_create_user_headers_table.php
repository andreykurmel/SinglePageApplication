<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserHeadersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_headers', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('table_field_id');
            $table->unsignedInteger('user_id');
            $table->string('unit_display', 50)->nullable();
            $table->unsignedInteger('width')->default(100);
            $table->unsignedInteger('min_width')->default(0);
            $table->unsignedInteger('max_width')->default(0);
            $table->unsignedInteger('order')->default(0);
            $table->tinyInteger('is_showed')->default(1);
            $table->string('notes')->nullable();

            $table->unsignedInteger('created_by')->nullable();
            $table->string('created_name')->nullable();
            $table->dateTime('created_on');
            $table->unsignedInteger('modified_by')->nullable();
            $table->string('modified_name')->nullable();
            $table->dateTime('modified_on');

            $table->foreign('table_field_id')->references('id')->on('table_fields')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('modified_by')->references('id')->on('users')->onDelete('set null');

            $table->index('order');
            $table->unique(['user_id', 'table_field_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_headers');
    }
}
