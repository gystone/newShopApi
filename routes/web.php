<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('test', 'TestController@index');

Route::group(['prefix' => 'wechat'], function () {
    Route::any('init', 'WechatController@serve');

    //自定义菜单
    Route::get('/del_menu', 'WechatController@deleteMenu');
    Route::get('/publish_menu', 'WechatController@publishMenu');

    //同步粉丝
    Route::get('/get_user', 'WechatController@getUser');

    //微信支付
    Route::post('/pay/unifiedorder', 'WechatPayController@unifiedOrder');
    Route::any('/pay/notify', 'WechatPayController@notify');

    //客服
    Route::post('add_kf', 'WechatController@addKF');
    Route::delete('del_kf', 'WechatController@delKF');
    Route::get('get_kf_list', 'WechatController@getKFList');
});