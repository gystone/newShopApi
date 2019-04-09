<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::any('wechat', 'WechatController@serve');
Route::any('test', 'WechatController@test');

Route::group([
    'namespace' => 'Api'
], function () {
    //测试登录
    Route::post('test_login', 'LoginMiniController@testLogin');

    // 认证
    Route::post('login', 'LoginController@login');;

    //上传图片
    Route::post('upload_image', 'ImageController@store');

    // JWT-Auth
    Route::group([
        'middleware' => 'jwt-auth'
    ], function ($router) {
        $router->delete('logout', 'LoginController@logout');
        $router->post('refresh', 'LoginController@refresh');
        // 认证后才能访问的路由


        Route::group(['prefix' => 'user'], function () {
            //用户地址列表
            Route::get('addresses', 'UserAddressesController@index')->name('user_addresses.index');
            //添加地址
            Route::post('addresses', 'UserAddressesController@store')->name('user_addresses.store');
            //地址详情
            Route::get('addresses/{userAddress}', 'UserAddressesController@show');
            //修改地址
            Route::patch('addresses/{userAddress}', 'UserAddressesController@update');
            //删除地址
            Route::delete('addresses/{userAddress}', 'UserAddressesController@destroy');
        });


        //商品
        Route::group(['prefix'=>'product'],function(){
            //列表
            Route::get('list','ProductsController@index');
            //详情
            Route::get('show/{product}','ProductsController@show');
            //收藏
            Route::post('favorite/{product}','ProductsController@favor');
            //取消收藏
            Route::delete('favorite/{product}', 'ProductsController@disfavor');
            //收藏列表
            Route::get('favorite','ProductsController@favorites');
        });

        //购物车
        Route::group(['prefix'=>'cart'],function(){
            //添加
            Route::post('add','CartController@add');
            //列表
            Route::get('list', 'CartController@index');
            //移除购物车商品
            Route::delete('remove/{productSku}','CartController@remove');

        });


    });
});


Route::group([
    'prefix' => 'admin',
    'namespace' => 'Admin'
], function () {
    // 认证
    Route::post('login', 'LoginController@login');
    //上传图片
    Route::post('upload_image', 'ImageController@store');
    // JWT-Auth
    Route::group([
        'middleware' => 'jwt-admin-auth'
    ], function ($router) {
        $router->delete('logout', 'LoginController@logout');
        $router->post('refresh', 'LoginController@refresh');

        $router->get('info', 'LoginController@info');
        // 认证后才能访问的路由

        // 设置
        $router->group([
            'prefix' => 'setting'
        ], function ($router) {
            $router->get('wechat', 'SettingController@getWechat');
            $router->post('wechat', 'SettingController@setWechat');
            $router->get('site', 'SettingController@getSite');
            $router->post('site', 'SettingController@setSite');
        });

        // 管理员
        $router->get('user', 'UserController@index');
        $router->get('user/{user}', 'UserController@show');
        $router->post('user', 'UserController@store');
        $router->patch('user/{user}', 'UserController@update');
        $router->delete('user/{user}', 'UserController@destroy');
        $router->post('user/update_info', 'UserController@updateInfo');
        $router->post('upload_avatar', 'UserController@uploadAvatar');

        // 角色
        $router->get('role', 'RoleController@index');
        $router->get('role/{role}', 'RoleController@show');
        $router->post('role', 'RoleController@store');
        $router->patch('role/{role}', 'RoleController@update');
        $router->delete('role/{role}', 'RoleController@destroy');

        $router->group([
            'prefix' => 'wechat',
            'namespace' => 'Wechat',
            'middleware' => 'wechat.config'
        ], function ($router) {
            $router->get('material_sync', 'MaterialController@materialSync');
            $router->get('material_list', 'MaterialController@materialList');
            $router->get('material_detail/{wechatMaterial}', 'MaterialController@materialDetail');
            $router->get('material_item_detail/{wechatMaterial}/{index}', 'MaterialController@materialItemDetail');
            $router->patch('material_news_update/{wechatMaterial}', 'MaterialController@materialNewsUpdate');
            $router->post('material_news_upload', 'MaterialController@materialNewsUpload');
            $router->post('material_img_upload', 'MaterialController@materialImgUpload');
            $router->post('material_voice_upload', 'MaterialController@materialVoiceUpload');
            $router->post('material_video_upload', 'MaterialController@materialVideoUpload');
            $router->delete('material_delete', 'MaterialController@materialDelete');
            $router->get('material/search/{type}', 'MaterialController@search');

            $router->get('tag_sync', 'TagController@sync');
            $router->get('tag_list', 'TagController@list');
            $router->post('tag_create', 'TagController@create');
            $router->patch('tag_update/{tag}', 'TagController@update');
            $router->delete('tag_delete/{tag}', 'TagController@delete');
            $router->post('tag_users', 'TagController@tagUsers');
            $router->get('tag_users/{tag}', 'TagController@userList');

            $router->get('user_sync', 'UserController@sync');
            $router->get('user_sync_other', 'UserController@syncUser');
            $router->get('user_list', 'UserController@list');
            $router->patch('user_remark/{user}', 'UserController@remark');
            $router->post('user_block', 'UserController@block');
            $router->post('user_unblock', 'UserController@unblock');
            $router->get('user_blacklist', 'UserController@blacklist');
            $router->get('user/search', 'UserController@search');

            $router->get('menu_sync', 'MenuController@sync');
            $router->get('menu_list', 'MenuController@list');
            $router->post('menu_create', 'MenuController@create');
            $router->delete('menu_delete', 'MenuController@delete');

            $router->post('reply_add', 'ReplyController@store');
            $router->get('reply_list', 'ReplyController@list');
            $router->get('reply_content', 'ReplyController@getContentByKeyword');
            $router->patch('reply_update/{reply}', 'ReplyController@update');
            $router->delete('reply_delete/{reply}', 'ReplyController@delete');
            $router->get('reply/search', 'ReplyController@search');

            $router->post('broadcast_send', 'BroadcastRecordController@send');
            $router->get('broadcast_history', 'BroadcastRecordController@history');
            $router->delete('broadcast_delete/{record}', 'BroadcastRecordController@delete');
        });


        Route::group(['prefix' => 'wechat_user'], function () {
            Route::get('list', 'WechatUserController@index');
        });

        //商品
        Route::resource('products', 'ProductsController');


    });
});
