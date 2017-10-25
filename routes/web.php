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

Route::get('/', ['middleware'=> ['access'], 'uses' => 'PageController@index']);

Route::get('/ip/{ip}', ['middleware'=> ['access'], 'uses' => 'PageController@getIP']);

Route::get('/find', ['middleware'=> ['access'], 'uses' => 'PageController@findIP']);
Route::post('/post', ['middleware'=> ['access'], 'uses' => 'PageController@postFindIP']);
