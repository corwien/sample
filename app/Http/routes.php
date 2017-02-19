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

/*
Route::get('/', function () {
    return view('welcome');
});
*/

Route::get('/', 'StaticPagesController@home')->name('home');
Route::get('/help', 'StaticPagesController@help')->name('help');
Route::get('/about', 'StaticPagesController@about')->name('about');

// 用户注册
Route::get('/signup', 'UsersController@create')->name('signup');

resource('users', 'UsersController');


// 会话控制，登录，退出
get('login', 'SessionsController@create')->name('login');
post('login', 'SessionsController@store')->name('login');
delete('logout', 'SessionsController@destroy')->name('logout');


// 账号激活
get('signup/confirm/{token}', 'UsersController@confirmEmail')->name('confirm_email');

// 密码重置
get('password/email', 'Auth\PasswordController@getEmail')->name('password.reset');
post('password/email', 'Auth\PasswordController@postEmail')->name('password.reset');
get('password/reset/{token}', 'Auth\PasswordController@getReset')->name('password.edit');
post('password/reset', 'Auth\PasswordController@postReset')->name('password.update');


// 微博路由，只实现创建删除操作
resource('statuses', 'StatusesController', ['only' => ['store', 'destroy']]);

// 关注
get('/users/{id}/followings', 'UsersController@followings')->name('users.followings');
get('/users/{id}/followers', 'UsersController@followers')->name('users.followers');

post('/users/followers/{id}', 'FollowersController@store')->name('followers.store');
delete('/users/followers/{id}', 'FollowersController@destroy')->name('followers.destroy');
