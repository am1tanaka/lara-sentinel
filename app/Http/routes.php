<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('activate/{email}/{code}', 'Sentinel\SentinelController@activate');

/*
Route::get('activate/{email}/{code}', function($email, $code) {
    return view('auth.login', ['info' => $email."/".$code]);
});

Route::get('activate', function() {
    return view('auth.login');
});
*/

Route::get('login', function() {return view('auth.login');});
Route::post('login', 'Sentinel\SentinelController@login');

Route::get('register/{email}', 'Sentinel\SentinelController@ResendActivationCode');
Route::get('register', function() {return view('auth.register');});
Route::post('register', 'Sentinel\SentinelController@register');

Route::get('home', 'HomeController@index');
