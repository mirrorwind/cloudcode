<?php

use Illuminate\Support\Facades\Route;

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

Route::get('/', function () {
    return view('welcome');
});

Route::group(['prefix' => 'yys'], function () {

    Route::group(['prefix' => 'cron'], function () {
        Route::get('/fetch-list', ['uses' => 'Yys\YysController@fetchList']);
        Route::get('/update-single', ['uses' => 'Yys\YysController@updateSingle']);
    });

    Route::get('/', ['uses' => 'Yys\YysController@ranking']);
    Route::get('/submit', ['uses' => 'Yys\YysController@submit']);
    Route::get('/test', ['uses' => 'Yys\YysController@test']);
    Route::get('/list', ['uses' => 'Yys\YysController@list']);
    Route::get('/ranking', ['uses' => 'Yys\YysController@ranking']);
    Route::post('/detail/json', ['uses' => 'Yys\YysController@detail']);
    Route::get('/detail/{sn}', ['uses' => 'Yys\YysController@detail']);
    Route::get('/model/{sn}', ['uses' => 'Yys\YysController@model']);
});
