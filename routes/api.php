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

Route::get('/ip/{ip}', ['middleware' => ['access'], 'uses' => 'APIController@getIPInfo']);
Route::get('/find/{id}', ['middleware' => ['access'], 'uses' => 'APIController@findIPInfo']);
