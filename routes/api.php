<?php

use Illuminate\Http\Request;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;

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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('app')->group(function () {
    Route::namespace('App')->group(function (Router $router) {
        $router->get('/announcement', 'AnnouncementController@index');
        $router->get('/banner', 'BannerController@index');
        $router->get('/notice', 'NoticeController@index');
        $router->post('/user/fast_login', 'UserController@fastLogin');
        $router->post('/user/login', 'UserController@login');
        $router->get('/captcha', 'ToolController@getCaptcha');
        $router->get('/price', 'ToolController@getPrice');
        $router->get('/search_token', 'ToolController@searchToken');
        $router->get('/version', 'ToolController@latestVersion');
        Route::group(['middleware' => 'auth:api'], function(Router $router) {
            $router->get('/user', 'UserController@show');
            $router->put('/user', 'UserController@update');
            $router->post('/user/reset_password', 'UserController@resetPassword');
        });
    });
});

Route::prefix('proton')->group(function() {
    Route::namespace('Proton')->group(function () {
       Route::resource('news', 'NewController');
    });
});
