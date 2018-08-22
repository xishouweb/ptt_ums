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
    $router->post('/wallet/notice', 'NoticeController@create');
    $router->get('/wallet/notice/create', 'NoticeController@createForm');
    $router->get('/wallet/notice/{id}/edit', 'NoticeController@edit');
    $router->put('/wallet/notice/{id}', 'NoticeController@update');
    $router->delete('/wallet/notice/{id}', 'NoticeController@delete');
});
