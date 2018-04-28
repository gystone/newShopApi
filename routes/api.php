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
    Route::post('login', 'AuthController@login');

    // JWT-Auth
    Route::group([
        'middleware' => 'jwt-auth'
    ], function ($router) {
        $router->delete('logout', 'AuthController@logout');
        // 认证后才能访问的路由
    });
});


Route::group([
    'prefix' => 'admin',
    'namespace' => 'Admin'
], function () {
    // 认证
    Route::post('login', 'AuthController@login')->middleware('admin-change');

    // JWT-Auth
    Route::group([
        'middleware' => 'jwt-auth'
    ], function ($router) {
        $router->delete('logout', 'AuthController@logout');
        // 认证后才能访问的路由
    });
});
