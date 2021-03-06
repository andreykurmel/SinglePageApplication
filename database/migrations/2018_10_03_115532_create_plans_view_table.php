<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePlansViewTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('plans_view', function (Blueprint $table) {
            $table->increments('id');
            $table->string('who_can_view', 32)->nullable();
            $table->string('code', 25);
            $table->string('category1', 128)->nullable();
            $table->string('category2', 128)->nullable();
            $table->string('category3', 128)->nullable();
            $table->string('sub_feat', 128)->nullable();
            $table->string('feature', 128);
            $table->string('plan_basic', 25)->nullable();
            $table->string('plan_standard', 25)->nullable();
            $table->string('plan_advanced', 25)->nullable();
            $table->string('plan_enterprise', 25)->nullable();
            $table->string('desc', 255)->nullable();

            $table->unsignedInteger('created_by')->nullable();
            $table->string('created_name')->nullable();
            $table->dateTime('created_on');
            $table->unsignedInteger('modified_by')->nullable();
            $table->string('modified_name')->nullable();
            $table->dateTime('modified_on');

            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('modified_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('plans_view');
    }
}
