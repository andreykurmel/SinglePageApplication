<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableFieldLinksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('table_field_links', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('table_field_id');
            $table->tinyInteger('lnk_header')->default(0);
            $table->string('name', 128)->default('Link');
            $table->string('link_type', 20);
            $table->string('link_display', 32)->nullable();
            $table->string('link_pos', 16)->default('before');
            $table->string('icon', 20);
            $table->string('tooltip')->nullable();
            $table->integer('row_order')->default(0);
            $table->unsignedInteger('table_ref_condition_id')->nullable();
            $table->unsignedInteger('listing_field_id')->nullable();
            $table->float('listing_rows_width')->default(250);
            $table->float('listing_rows_min_width')->default(70);
            $table->unsignedInteger('address_field_id')->nullable();
            $table->unsignedInteger('share_mrv_id')->nullable();
            $table->unsignedInteger('share_url_field_id')->nullable();
            $table->unsignedInteger('share_custom_field_id')->nullable();
            $table->unsignedInteger('share_can_custom')->nullable();
            $table->unsignedInteger('share_custom_hash')->nullable();
            $table->unsignedInteger('share_web_link_id')->nullable();
            $table->unsignedInteger('share_record_link_id')->nullable();
            $table->unsignedInteger('share_is_web')->nullable();

            $table->unsignedInteger('created_by')->nullable();
            $table->string('created_name')->nullable();
            $table->dateTime('created_on');
            $table->unsignedInteger('modified_by')->nullable();
            $table->string('modified_name')->nullable();
            $table->dateTime('modified_on');


            $table->foreign('table_field_id', 'table_field_foreign')
                ->references('id')
                ->on('table_fields')
                ->onDelete('cascade');

            $table->foreign('table_ref_condition_id', 'table_ref_condition_foreign')
                ->references('id')
                ->on('table_ref_conditions')
                ->onDelete('set null');

            $table->foreign('listing_field_id', 'listing_field_foreign')
                ->references('id')
                ->on('table_fields')
                ->onDelete('set null');

            $table->foreign('address_field_id', 'address_field_foreign')
                ->references('id')
                ->on('table_fields')
                ->onDelete('set null');


            $table->foreign('created_by', 'created_foreign')
                ->references('id')
                ->on('users')
                ->onDelete('set null');

            $table->foreign('modified_by', 'modified_foreign')
                ->references('id')
                ->on('users')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('table_field_links');
    }
}
