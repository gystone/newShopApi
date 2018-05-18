<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWechatRepliesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wechat_replies', function (Blueprint $table) {
            $table->increments('id');
            $table->string('rule_name', 255);
            $table->text('keywords');
            $table->text('contents');
            $table->boolean('is_reply_all')->default(1);
            $table->boolean('is_open')->default(1);
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
        Schema::dropIfExists('wechat_replies');
    }
}
