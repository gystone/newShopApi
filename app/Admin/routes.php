<?php

use Illuminate\Routing\Router;

Admin::registerAuthRoutes();

Route::group([
    'prefix'        => config('admin.route.prefix'),
    'namespace'     => config('admin.route.namespace'),
    'middleware'    => config('admin.route.middleware'),
], function (Router $router) {

    $router->get('/', 'HomeController@index');

    //微信相关
    $router->group(['namespace' => 'Wechat'], function () use ($router) {
        // 微信配置
        $router->get('wechat', 'WechatController@index');
        $router->post('wechat', 'WechatController@store');

        //微信菜单
        $router->resource('wechat_menu', 'WechatMenuController');

        //微信用户
        $router->resource('wechat_user', 'WechatUserController');

        //文本消息
        $router->resource('wechat_text', 'WechatTextController');

        //关键字
        $router->resource('wechat_keyword', 'WechatKeywordController');

        //图文
        $router->resource('wechat_news', 'WechatNewsController');

        //文章
        $router->resource('wechat_article', 'WechatArticleController');

        //图片
        $router->resource('wechat_image', 'WechatImageController');

        //客服
        $router->resource('wechat_kf', 'KFController');
    });
});
