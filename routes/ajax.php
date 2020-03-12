<?php

/*
|--------------------------------------------------------------------------
| Ajax Routes
|--------------------------------------------------------------------------
|
| Here is where you can register Ajax routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "ajax" middleware group. Returning json responses
|
*/

Route::group(['middleware' => 'auth'], fn () => Route::resource('/project', 'ProjectController'));
