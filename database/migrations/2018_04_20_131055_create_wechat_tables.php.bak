<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWechatTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wechat_articles', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title', 255);
            $table->string('author', 255);
            $table->longText('content');
            $table->string('thumb_media_id', 255);
            $table->string('digest', 255);
            $table->string('source_url', 255);
            $table->boolean('show_cover');
            $table->string('media_id', 255);
            $table->string('url', 255);
        });

        Schema::create('wechat_images', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 255);
            $table->string('media_id', 255);
            $table->string('url', 255);
            $table->string('path', 255);
        });

        Schema::create('wechat_keywords', function (Blueprint $table) {
            $table->increments('id');
            $table->string('key_text', 255);
            $table->integer('text_id');
            $table->enum('msg_type', ['text', 'news']);
            $table->integer('news_id');
        });

        Schema::create('wechat_kf', function (Blueprint $table) {
            $table->increments('id');
            $table->string('kf_account', 255);
            $table->string('kf_headimgurl', 255);
            $table->string('kf_nick', 255);
        });

        Schema::create('wechat_menu', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('pid')->default(0);
            $table->integer('order')->default(0);
            $table->string('title', 255);
            $table->string('type', 50);
            $table->string('menu_key', 255);
            $table->string('menu_url', 255);
            $table->enum('select_type', ['0', '1'])->default(0);
        });

        Schema::create('wechat_news', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('pid')->default(0);
            $table->integer('order')->default(0);
            $table->string('title', 255);
            $table->text('description');
            $table->string('image', 255);
            $table->string('url', 255);
        });

        Schema::create('wechat_text', function (Blueprint $table) {
            $table->increments('id');
            $table->text('content');
        });

        Schema::create('wechat_users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('openid', 255);
            $table->string('nickname', 255);
            $table->boolean('sex')->default(1);
            $table->string('unionid', 255);
            $table->string('headiimgurl', 255);
            $table->string('city', 255);
            $table->string('province', 255);
            $table->string('country', 255);
            $table->timestamp('subscribe_time')->default(now());
            $table->timestamp('unsubscribe_time')->default(now());
            $table->enum('status', ['subscribe', 'unsubscribe', 'nonesubscribe'])->default('subscribe');
            $table->enum('types', ['0'])->default('0');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('wechat_articles');
        Schema::dropIfExists('wechat_images');
        Schema::dropIfExists('wechat_keywords');
        Schema::dropIfExists('wechat_kf');
        Schema::dropIfExists('wechat_menu');
        Schema::dropIfExists('wechat_news');
        Schema::dropIfExists('wechat_text');
        Schema::dropIfExists('wechat_users');
    }
}
