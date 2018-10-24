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

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/api/v1/track', 'Vendor\TrackController@upload');
Route::post('/api/v1/track_node_call', 'Vendor\TrackController@record');
Route::get('/track', 'Vendor\TrackController@index');

Route::prefix('api/business')->group(function () {

	Route::namespace('Business')->group(function () {

		Route::resource('/match_items', 'MatchItemController')->only([
			'show'
		]);

		Route::resource('/user_applications', 'UserApplicationController')->only([
		    'show'
		]);

		Route::post('/users/login', 'BusinessUserController@login');
        Route::post('/users/register', 'BusinessUserController@register');
        Route::post('/users/captcha', 'CaptchaController@send');
        Route::post('/oauth/token', 'BusinessUserController@getAuthToken');
        Route::get('/download/data_upload_example', function() {
            return response()->download(public_path('downloads/data_upload_example.csv'), 'example.csv');
        });

        Route::group(['middleware' => 'auth:api'], function() {
            Route::get('/users/detail', 'BusinessUserController@detail');
            Route::post('/users/update', 'BusinessUserController@update');
            Route::post('/users/generate_public_key', 'BusinessUserController@generate_public_key');
            Route::get('/user_applications', 'UserApplicationController@index');
            Route::post('/user_applications', 'UserApplicationController@store');
            Route::get('/data_records', 'DataRecordController@index');
            Route::post('/data_records', 'DataRecordController@store');
            Route::post('/data_records/upload_file', 'DataRecordController@uploadFile');
            Route::post('/data_records/multiple', 'DataRecordController@multipleDataUpload');
            Route::get('/match_items', 'MatchItemController@index');
            Route::post('/match_items', 'MatchItemController@store');
            Route::get('/dashboard/chart_data', 'DashboardController@chartData');
            Route::get('/dashboard/summary', 'DashboardController@summary');
        });
	});


});

Route::prefix('api/vendor')->group(function () {

	Route::namespace('Vendor')->group(function () {

		Route::get('/data/record', 'DataController@index');
		Route::post('/data/record', 'DataController@record');
		Route::get('/data/create', 'DataController@create');
		Route::post('/data', 'DataController@store');
		Route::get('/data/examples/address', 'DataController@examples_address');

	});


});