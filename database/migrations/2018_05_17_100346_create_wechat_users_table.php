<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWechatUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wechat_users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('openid', 255);
            $table->string('nickname', 255)->nullable();
            $table->boolean('sex')->default(1);
            $table->string('unionid', 255)->nullable();
            $table->string('headimgurl', 255)->nullable();
            $table->string('city', 255)->nullable();
            $table->string('province', 255)->nullable();
            $table->string('country', 255)->nullable();
            $table->string('remark', 30)->nullable();
            $table->timestamp('subscribe_time')->default(now());
            $table->timestamp('unsubscribe_time')->default(now());
            $table->enum('status', ['subscribe', 'unsubscribe', 'nonesubscribe'])->default('nonesubscribe');
            $table->boolean('is_blacklist')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('wechat_users');
    }
}
