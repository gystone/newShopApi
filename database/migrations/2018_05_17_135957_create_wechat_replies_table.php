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
            $table->string('keyword', 255);
            $table->enum('type', ['text', 'image', 'voice', 'video', 'news']);
            $table->text('content');
            $table->enum('is_equal', ['equal', 'contain'])->default('equal');
            $table->boolean('is_open');
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
