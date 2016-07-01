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

// TODO: 動作確認が終わったら削除する
Route::get('test/{role}/{routename}', function($role, $routename){
    return view('sentinel.users',
    [
        'role' => $role,
        'method' => $routename,
        'roles' => Sentinel::getRoleRepository()->all(),
        'users' => Sentinel::getUserRepository()->all()
    ]);
});

Route::get('activate/{email}/{code}', 'Sentinel\SentinelController@activate');

Route::get('login', function() {return view('auth.login');});
Route::post('login', 'Sentinel\SentinelController@login');

Route::get('register/{email}', 'Sentinel\SentinelController@resendActivationCode');
Route::get('register', function() {return view('auth.register');});
Route::post('register', 'Sentinel\SentinelController@register');

Route::get('password/reset/{email}/{code}/{password}', 'Sentinel\SentinelController@resetPassword');
Route::get('password/reset', function() {return view('auth.passwords.reset', ['token'=>'']);});
Route::post('password/reset', 'Sentinel\SentinelController@sendResetPassword');

Route::get('logout', 'Sentinel\SentinelController@logout');

Route::resource('users', 'Sentinel\UserController');

Route::get('home', 'HomeController@index');
