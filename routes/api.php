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
        $router->get('/banner', 'BannerController@index');
        $router->get('/notice', 'NoticeController@index');
        $router->post('/user/login', 'UserController@login');
        $router->get('/price', 'ToolController@getPrice');
        $router->get('/search_token', 'ToolController@searchToken');
        $router->get('/announcement', 'AnnouncementController@index');
        Route::group(['middleware' => 'auth:api'], function(Router $router) {
            $router->get('/user/detail', 'UserController@detail');
        });
    });
});

Route::prefix('proton')->group(function() {
    Route::namespace('Proton')->group(function () {
       Route::resource('news', 'NewController');
    });
});


Route::prefix('campaign')->group(function() {
    Route::get('captcha', 'CaptchaController@send');

    Route::namespace('Campaign')->group(function () {
        Route::post('user/login', 'UserController@login');
        Route::post('user/register', 'UserController@register');

        Route::get('detail/{id}', 'CampaignController@show');

        Route::get('team/ranks', 'TeamController@ranks');
        Route::get('team', 'TeamController@index');
        Route::get('user/teams', 'UserController@teams');

        Route::get('vote/rank', 'TeamController@voteRank');

        Route::group(['middleware' => 'auth:api'], function() {
            Route::get('account/detail', 'UserController@detail');
            Route::post('user/vote/{team_id}', 'UserController@voteTo');
            Route::post('team', 'TeamController@store');
            Route::post('team/join/{team_id}', 'TeamController@join');

        });

    });
});