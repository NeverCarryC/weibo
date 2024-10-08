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

Route::get('/', 'StaticPagesController@home')->name('home');
Route::get('/help', 'StaticPagesController@help')->name('help');
Route::get('/about', 'StaticPagesController@about')->name('about');
Route::get('signup', 'UsersController@create')->name('signup');

// 写了resource这一行相当于创建了以下路由：
// GET	/users	UsersController@index	显示所有用户列表的页面
// GET	/users/{user}	UsersController@show	显示用户个人信息的页面
// GET	/users/create	UsersController@create	创建用户的表单页面
// POST	/users	UsersController@store	接受表单数据，在数据库中创建用户
// GET	/users/{user}/edit	UsersController@edit	编辑用户个人资料的页面
// PATCH	/users/{user}	UsersController@update	更新用户
// DELETE	/users/{user}	UsersController@destroy	删除用户
Route::resource('users', 'UsersController');


// 登录表单
Route::get('login', 'SessionsController@create')->name('login');
// 登录验证
Route::post('login', 'SessionsController@store')->name('login');
Route::delete('logout', 'SessionsController@destroy')->name('logout');
// 注册后发送验证邮件
Route::get('signup/confirm/{token}', 'UsersController@confirmEmail')->name('confirm_email');

// 填写 Email 的表单
Route::get('password/reset',  'PasswordController@showLinkRequestForm')->name('password.request');
// 处理表单提交，成功的话就发送邮件，附带 Token 的链接
Route::post('password/email',  'PasswordController@sendResetLinkEmail')->name('password.email');
// 显示更新密码的表单，包含 token
Route::get('password/reset/{token}',  'PasswordController@showResetForm')->name('password.reset');
// 对提交过来的 token 和 email 数据进行配对，正确的话更新密码
Route::post('password/reset',  'PasswordController@reset')->name('password.update');

Route::resource('statuses', 'StatusesController', ['only' => ['store', 'destroy']]);
