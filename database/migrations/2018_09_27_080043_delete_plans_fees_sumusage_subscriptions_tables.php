<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DeletePlansFeesSumusageSubscriptionsTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('plans');
        Schema::dropIfExists('fees');
        Schema::dropIfExists('sum_usages');
        Schema::dropIfExists('user_subscriptions');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::create('sum_usages', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('table_id');
            $table->string('type', 50)->nullable();
            $table->float('size')->nullable();
            $table->float('file_size')->nullable();
            $table->integer('num_rows')->nullable();
            $table->integer('num_columns')->nullable();
            $table->integer('num_collaborators')->nullable();

            $table->unsignedInteger('created_by')->nullable();
            $table->string('created_name')->nullable();
            $table->dateTime('created_on');
            $table->unsignedInteger('modified_by')->nullable();
            $table->string('modified_name')->nullable();
            $table->dateTime('modified_on');

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('table_id')->references('id')->on('tables')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('modified_by')->references('id')->on('users')->onDelete('set null');
        });

        Schema::create('fees', function (Blueprint $table) {
            $table->increments('id');
            $table->string('plan')->nullable();
            $table->string('adv_feature')->nullable();
            $table->float('per_month')->nullable();
            $table->float('per_year')->nullable();
            $table->string('notes', 255)->nullable();

            $table->unsignedInteger('created_by')->nullable();
            $table->string('created_name')->nullable();
            $table->dateTime('created_on');
            $table->unsignedInteger('modified_by')->nullable();
            $table->string('modified_name')->nullable();
            $table->dateTime('modified_on');

            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('modified_by')->references('id')->on('users')->onDelete('set null');

            $table->index('plan');
            $table->index('adv_feature');
        });

        Schema::create('plans', function (Blueprint $table) {
            $table->increments('id');
            $table->string('category1')->nullable();
            $table->string('category2')->nullable();
            $table->string('feature')->nullable();
            $table->string('basic')->nullable();
            $table->string('advanced')->nullable();
            $table->string('desc')->nullable();

            $table->unsignedInteger('created_by')->nullable();
            $table->string('created_name')->nullable();
            $table->dateTime('created_on');
            $table->unsignedInteger('modified_by')->nullable();
            $table->string('modified_name')->nullable();
            $table->dateTime('modified_on');

            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('modified_by')->references('id')->on('users')->onDelete('set null');

            $table->index('feature');
            $table->index('basic');
            $table->index('advanced');
        });

        Schema::create('user_subscriptions', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->string('plan')->nullable();
            $table->unsignedInteger('add_bi')->nullable();
            $table->unsignedInteger('add_map')->nullable();
            $table->unsignedInteger('add_alert')->nullable();
            $table->float('cost', 16, 8);
            $table->string('notes', 150)->nullable();
            $table->string('renew')->nullable();
            $table->string('status')->nullable();

            $table->unsignedInteger('created_by')->nullable();
            $table->string('created_name')->nullable();
            $table->dateTime('created_on');
            $table->unsignedInteger('modified_by')->nullable();
            $table->string('modified_name')->nullable();
            $table->dateTime('modified_on');

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('modified_by')->references('id')->on('users')->onDelete('set null');
        });
    }
}
