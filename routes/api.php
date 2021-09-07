<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::post('login', 'API\UserController@login');
Route::post('register', 'API\UserController@register');

Route::get('courses','API\CourcesController@index');

Route::group(['middleware' => 'auth:api', 'namespace' => 'API'],function () {
    
    Route::get('user', 'UserController@details');
    Route::get('logout', 'UserController@logout');
    Route::post('courses','CourcesController@store');
    Route::resource('carts','CartController')->only([
        'index', 'store', 'destroy'
    ]);
    
    // Route::post('pay','PaymentController@payment');
    Route::post('pay','PaymentController@payStripe');
    Route::get('my-courses','PaymentController@myCourses');

    Route::post('my-courses-download','PaymentController@getDownload');
});
