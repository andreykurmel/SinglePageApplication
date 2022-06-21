<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTablesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tables', function (Blueprint $table) {
            $table->increments('id');
            $table->tinyInteger('is_system')->default(0);
            $table->string('db_name');
            $table->string('name');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('rows_per_page')->default(100);
            $table->text('notes')->nullable();
            $table->string('source', 50)->default('scratch');
            $table->unsignedInteger('connection_id')->nullable();

            $table->tinyInteger('single_view_active')->default(0);
            $table->unsignedInteger('single_view_permission_id')->nullable();
            $table->unsignedInteger('single_view_status_id')->nullable();
            $table->unsignedInteger('single_view_password_id')->nullable();
            $table->string('single_view_background_by', 16)->default('color');
            $table->string('single_view_bg_color', 16)->nullable();
            $table->string('single_view_bg_img', 255)->nullable();
            $table->string('single_view_bg_fit', 16)->nullable();
            $table->unsignedInteger('single_view_form_width')->default(800);
            $table->string('single_view_form_color', 16)->nullable();
            $table->unsignedInteger('single_view_form_transparency')->default(0);
            $table->unsignedInteger('single_view_form_line_height')->default(18);
            $table->unsignedInteger('single_view_form_font_size')->default(14);

            $table->unsignedInteger('created_by')->nullable();
            $table->string('created_name')->nullable();
            $table->dateTime('created_on');
            $table->unsignedInteger('modified_by')->nullable();
            $table->string('modified_name')->nullable();
            $table->dateTime('modified_on');

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('single_view_permission_id', 'tables__single_view_permission_id')
                ->references('id')
                ->on('table_permissions')
                ->onDelete('set null');
            $table->foreign('single_view_status_id', 'tables__single_view_status_id')
                ->references('id')
                ->on('table_fields')
                ->onDelete('set null');
            $table->foreign('single_view_password_id', 'tables__single_view_password_id')
                ->references('id')
                ->on('table_fields')
                ->onDelete('set null');

            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('modified_by')->references('id')->on('users')->onDelete('set null');

            $table->index('db_name');
            $table->index('name');
            $table->index('is_system');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tables');
    }
}
