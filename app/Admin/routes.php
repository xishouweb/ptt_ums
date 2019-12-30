<?php

use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;
use Encore\Admin\Facades\Admin;

Admin::registerAuthRoutes();

Route::group([
    'prefix'        => config('admin.route.prefix'),
    'namespace'     => config('admin.route.namespace'),
    'middleware'    => config('admin.route.middleware'),
], function (Router $router) {
    $router->get('/', 'HomeController@index');
    //资讯相关路由
    $router->get('/wallet/notice', 'NoticeController@index');
    $router->post('/wallet/notice', 'NoticeController@create');
    $router->get('/wallet/notice/create', 'NoticeController@createForm');
    $router->get('/wallet/notice/{id}/edit', 'NoticeController@edit');
    $router->put('/wallet/notice/{id}', 'NoticeController@update');
    $router->delete('/wallet/notice/{id}', 'NoticeController@delete');
    //Banner相关路由
    $router->get('/wallet/banner', 'BannerController@index');
    $router->post('/wallet/banner', 'BannerController@create');
    $router->get('/wallet/banner/create', 'BannerController@createForm');
    $router->get('/wallet/banner/{id}/edit', 'BannerController@edit');
    $router->put('/wallet/banner/{id}', 'BannerController@update');
    $router->delete('/wallet/banner/{id}', 'BannerController@delete');

    $router->resource('/wallet/user-wallet-balances', UserWalletBalanceController::class);
    $router->resource('/wallet/user-wallet-transactions', UserWalletTransactionController::class);
    $router->resource('/wallet/user-wallet-withdrawals', UserWalletWithdrawalController::class);
    $router->resource('/wallet/savings', SavingController::class);
    $router->get('/wallet/user-wallet-withdrawals/{id}/approve', 'UserWalletWithdrawalController@getApprove');
    $router->get('/wallet/user-wallet-withdrawals/{id}/decline', 'UserWalletWithdrawalController@getDecline');
    $router->get('/wallet/savings/{id}/approve', 'SavingController@getApprove');
    $router->get('/wallet/savings/{id}/decline', 'SavingController@getDecline');

    //Campaign

    $router->resource('user/tokens', 'UserTokenController');
    $router->resource('campaign', 'CampaignController');

    $router->resource('xu_rank', 'PriceQueryStatisticController');

    //公告相关路由
    $router->get('/wallet/announcement', 'AnnouncementController@index');
    $router->post('/wallet/announcement', 'AnnouncementController@create');
    $router->get('/wallet/announcement/create', 'AnnouncementController@createForm');
    $router->get('/wallet/announcement/{id}/edit', 'AnnouncementController@edit');
    $router->put('/wallet/announcement/{id}', 'AnnouncementController@update');
    $router->delete('/wallet/announcement/{id}', 'AnnouncementController@delete');
    $router->resource('news', 'ProtonNewController');

});
