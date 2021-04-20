<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class PaymentsToTransactions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn('status');
            $table->dropColumn('grace_period');

            $table->string('type', 128)->nullable();
            $table->string('from', 64)->nullable();
            $table->string('from_details', 64)->nullable();
            $table->string('to', 64)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn('type');
            $table->dropColumn('from');
            $table->dropColumn('from_details');
            $table->dropColumn('to');

            $table->string('status', 50)->nullable();
            $table->float('grace_period')->nullable();
        });
    }
}
