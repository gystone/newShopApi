<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWechatBroadcastRecordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wechat_broadcast_records', function (Blueprint $table) {
            $table->increments('id');
            $table->text('tos')->nullable();
            $table->enum('types', ['text', 'image', 'news', 'voice', 'video']);
            $table->string('message');
            $table->boolean('is_cron')->default(0);
            $table->timestamp('send_time');
            $table->string('msg_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('broadcast_records');
    }
}
