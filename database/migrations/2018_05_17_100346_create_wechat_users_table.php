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
            $table->string('nickname', 255);
            $table->boolean('sex')->default(1);
            $table->string('unionid', 255)->nullable();
            $table->string('headimgurl', 255);
            $table->string('city', 255);
            $table->string('province', 255);
            $table->string('country', 255);
            $table->string('remark', 30)->nullable();
            $table->string('tagid_list', 255)->nullable();
            $table->timestamp('subscribe_time')->default(now());
            $table->timestamp('unsubscribe_time')->default(now());
            $table->enum('subscribe_scene', [
                'ADD_SCENE_SEARCH', 'ADD_SCENE_ACCOUNT_MIGRATION', 'ADD_SCENE_PROFILE_CARD',
                'ADD_SCENE_QR_CODE', 'ADD_SCENEPROFILE LINK', 'ADD_SCENE_PROFILE_ITEM',
                'ADD_SCENE_PAID', 'ADD_SCENE_OTHERS'
            ]);
            $table->enum('status', ['subscribe', 'unsubscribe', 'nonesubscribe'])->default('subscribe');
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
