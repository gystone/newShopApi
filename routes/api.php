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

Route::group([
    'namespace' => 'Api'
], function () {

    Route::get('test', 'TestController@index');
    // 认证
    Route::post('login', 'LoginController@login');;

    // JWT-Auth
    Route::group([
        'middleware' => 'jwt-auth'
    ], function ($router) {
        $router->delete('logout', 'LoginController@logout');
        // 认证后才能访问的路由
    });
});


Route::group([
    'prefix' => 'admin',
    'namespace' => 'Admin'
], function () {
    // 认证
    Route::post('login', 'LoginController@login');

    // JWT-Auth
    Route::group([
        'middleware' => 'jwt-admin-auth'
    ], function ($router) {
        $router->delete('logout', 'LoginController@logout');

        $router->get('info', 'LoginController@info');
        // 认证后才能访问的路由
        $router->get('user', 'UserController@index');
        $router->post('user', 'UserController@store');
        $router->patch('user/{user}', 'UserController@update');
        $router->delete('user/{user}', 'UserController@destroy');
        $router->post('upload_avatar', 'UserController@uploadAvatar');

        $router->get('role', 'RoleController@index');
        $router->post('role', 'RoleController@store');
        $router->patch('role/{role}', 'RoleController@update');
        $router->delete('role/{role}', 'RoleController@destroy');

        $router->get('permission', 'PermissionController@index');
        $router->post('permission', 'PermissionController@store');
        $router->patch('permission/{permission}', 'PermissionController@update');
        $router->delete('permission/{permission}', 'PermissionController@destroy');

        $router->group([
            'prefix' => 'wechat',
            'namespace' => 'Wechat'
        ], function ($router) {
            $router->get('material_sync', 'MaterialController@materialSync');
            $router->get('material_list', 'MaterialController@materialList');
            $router->get('material_detail/{wechatMaterial}', 'MaterialController@materialDetail');
            $router->get('material_item_detail/{wechatMaterial}/{index}', 'MaterialController@materialItemDetail');
        });
    });
});
