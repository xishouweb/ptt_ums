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

Route::any('wechat', 'WechatController@serve');

Route::prefix('app')->group(function () {

    Route::namespace('App')->group(function (Router $router) {
        $router->get('/announcement', 'AnnouncementController@index');
        $router->get('/banner', 'BannerController@index');
        $router->get('/notice', 'NoticeController@index');
        $router->post('/user/fast_login', 'UserController@fastLogin');
        $router->post('/user/login', 'UserController@login');
        $router->post('/user/reset_password', 'UserController@resetPassword');
        $router->get('/captcha', 'ToolController@getCaptcha');
        $router->get('/price', 'ToolController@getPrice');
        $router->get('/search_token', 'ToolController@searchToken');
        $router->get('/version', 'ToolController@latestVersion');
        $router->get('/coinmarketcap', 'ToolController@getCryptoCurrencyPrice');
        $router->get('/wallet/download', 'ToolController@downloadWallet');
        $router->post('/proxy', 'ToolController@proxy');
        Route::group(['middleware' => 'auth:api'], function(Router $router) {
            $router->get('/user', 'UserController@show');
            $router->post('/user', 'UserController@update');
        });
    });
    Route::prefix('v1')->group(function (Router $router) {
        $router->get('captcha', 'CaptchaController@send');
        Route::namespace('App')->group(function (Router $router) {
            $router->post('/user/sign_up', 'UserController@signUp');
            $router->post('/user/login', 'UserController@signInPwd');
            $router->post('/user/fast_login', 'UserController@signInCaptcha');
            $router->post('/user/reset_login_pwd', 'UserController@resetSignInPwd');
            $router->post('/user/reset_trade_pwd', 'UserController@resetTradePwd');
            $router->post('/user/check_trade_pwd', 'UserController@checkTradePwd');
        });
    });
});

Route::prefix('sdk/v1')->group(function () {
    Route::namespace('SDK')->group(function (Router $router) {
        Route::post('/data_records', 'TrackController@upload');
    });
});

Route::prefix('mark_six')->group(function () {
    Route::namespace('MarkSix')->group(function (Router $router) {
        $router->get('/', 'MarkSixController@index');
        $router->post('/', 'MarkSixController@store');
        $router->post('/draw', 'MarkSixController@draw');
        $router->post('/set_award', 'MarkSixController@setAward');
        $router->get('/ranking_list', 'MarkSixController@rankingList');
        $router->get('/winning_info', 'MarkSixController@winningInfo');
    });
});

Route::prefix('proton')->group(function() {
    Route::namespace('Proton')->group(function () {
       Route::resource('news', 'NewController');
    });
});

Route::prefix('v1')->group(function() {
    Route::namespace('V1')->group(function (Router $router) {
       $router->get('symbol/{symbol}/info', 'ToolController@getPrice');
       $router->get('symbol/keyword', 'ToolController@setKeyword');
       $router->post('symbol/message/callback', 'ToolController@wechatMessageCallback');
       $router->post('user/xu/callback', 'UserController@xuUserCallBack');
       $router->get('symbol/statistic', 'ToolController@getStatistic');
       $router->get('symbol/test/{aaa}', 'ToolController@test');

       $router->get('btk_redirect_xu', 'UserController@btkRedirectXu');

       $router->get('price/query/rank/{page}', 'XuRankController@rank');
       $router->get('price/query/{user_id}/join/{campaign_id}', 'XuRankController@join');

       Route::group(['middleware' => ['wechat.oauth:user_base, snsapi_base']], function (Router $router) {
            $router->get('/xu', 'UserController@redirectXu');
        });
    });
});


Route::prefix('campaign')->group(function() {
    Route::get('captcha', 'CaptchaController@send');
    Route::namespace('Campaign')->group(function () {

        Route::get('test/add/token', 'TokenTxController@create');

        Route::post('user/login', 'UserController@login');
        Route::post('user/fast_login', 'UserController@fastLogin');
        Route::post('user/register', 'UserController@register');
        Route::post('user/update_password', 'UserController@updatePassword');
        Route::get('detail/{id}', 'CampaignController@show');
        Route::get('team/ranks', 'TeamController@ranks');
        Route::get('team', 'TeamController@index');
        Route::get('team/{team_id}', 'TeamController@show');
        Route::get('vote/rank', 'TeamController@voteRank');
        Route::get('vote/{id}', 'TeamController@vote');

        Route::group(['middleware' => 'auth:api'], function() {
            Route::post('photo/upload', 'UserController@photoUpload');
            Route::get('account/detail', 'UserController@detail');
            Route::post('user/vote/{team_id}', 'UserController@voteTo');
            Route::post('team', 'TeamController@store');
            Route::post('team/join/{team_id}', 'TeamController@join');
            Route::post('user/update', 'UserController@update');
            Route::get('user/edit', 'UserController@edit');
            Route::get('user/teams', 'UserController@teams');
            Route::get('user/logout', 'UserController@logout');
            Route::get('user/checknickname/{nickname}', 'UserController@checkNickname');
            Route::get('user/votes', 'UserController@getVotes');
            Route::get('user/votes/detail', 'UserController@getVoteDetail');
            Route::get('user/income', 'UserController@myIncome');
            Route::get('user/income/detail', 'UserController@incomeDetail');

            Route::get('user/token/detail', 'UserController@tokenDetail');
            Route::get('user/deposit/address', 'UserController@getDepositAddress');
            Route::get('user/share/{tema_id}/type/{type}', 'UserController@share');

            //to do campaign_id, token_type 放header里

            Route::group(['middleware' => 'checklogin'], function() {
                Route::get('account/detail', 'UserController@detail');
                Route::get('user/rank/campaign/{campaign_id}/token_type/{type}', 'UserController@myRanks');
                Route::get('user/vote/rank/campaign/{campaign_id}/token_type/{token_type}', 'UserController@myVoteRank');
            });
        });

        Route::any('wechat', 'WechatController@serve');
        Route::middleware('wechat.oauth')->group(function(){
            Route::get('wechat/auth', 'UserController@wechatAuth');
        });
    });
});
