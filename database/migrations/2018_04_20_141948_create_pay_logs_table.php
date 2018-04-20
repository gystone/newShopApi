<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePayLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pay_logs', function (Blueprint $table) {
            $table->increments('id');
            $table->string('openid', 255);
            $table->string('pay_type', 10);
            $table->string('order_no', 50);
            $table->string('pay_ls', 60);
            $table->integer('fee');
            $table->integer('pay_status');
            $table->integer('type');
            $table->string('fk_type', 20);
            $table->string('transaction_id', 100);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pay_logs');
    }
}
