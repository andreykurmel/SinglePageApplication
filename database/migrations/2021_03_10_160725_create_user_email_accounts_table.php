<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserEmailAccountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_email_accounts', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->string('email', 255);
            $table->string('app_pass', 255);

            $table->foreign('user_id', 'user_email_accounts__user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
        });

        Schema::table('tables', function (Blueprint $table) {
            $table->unsignedInteger('account_api_key_id')->nullable();

            $table->foreign('account_api_key_id', 'tables__account_api_key_id')
                ->references('id')
                ->on('user_api_keys')
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
        Schema::dropIfExists('user_email_accounts');

        Schema::table('tables', function (Blueprint $table) {
            $table->dropForeign('tables__account_api_key_id');
            $table->dropColumn('account_api_key_id');
        });
    }
}
