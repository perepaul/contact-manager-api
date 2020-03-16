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

Route::group([
    'prefix' => 'user',
    'namespace'=>'User'
],
function () {

Route::post('register','AuthController@register');
Route::post('login','AuthController@login');
Route::post('contact/add','ContactController@addContacts');
Route::get('contact/get-all/{token}/{pagination?}','ContactController@getPaginatedContacts');
Route::post('contact/update/{id}','ContactController@updateContact');
Route::post('contact/delete/{id}/{token}','ContactController@deleteContact');
Route::get('contact/get-single/{id}','ContactController@getContact');
Route::get('contact/search/search/{search}/{token}/{pagination?}');

}
);
