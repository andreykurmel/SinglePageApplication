<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCorrespondenceTableTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (! Schema::connection('mysql_correspondence')->hasTable('correspondence_tables')) {
            Schema::connection('mysql_correspondence')
                ->create('correspondence_tables', function (Blueprint $table) {
                    $table->increments('id');
                    $table->unsignedInteger('user_id')->nullable();
                    $table->unsignedInteger('correspondence_app_id')->nullable();
                    $table->string('app_table')->nullable();
                    $table->string('data_table')->nullable();
                    $table->string('notes', 512)->nullable();
                    $table->string('row_hash', 32)->nullable();

                    $table->foreign('correspondence_app_id', 'corr_table__correspondence_app_id')
                        ->references('id')
                        ->on('correspondence_apps')
                        ->onDelete('cascade');
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
        Schema::connection('mysql_correspondence')->dropIfExists('correspondence_tables');
    }
}
