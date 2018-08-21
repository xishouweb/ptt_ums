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
    $router->get('/wallet/notice', 'NoticeController@index');
});
