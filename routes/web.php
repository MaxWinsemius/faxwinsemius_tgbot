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

Route::middleware(['IpMiddleware'])->group(function() {
    Route::get('/', function () {
        return view('welcome');
    });

    Route::get('/botman/tinker', 'BotManController@tinker');
});


Route::get('/503', function() {
    return "503 access denied";
})->name('503');

Route::match(['get', 'post'], '/botman', 'BotManController@handle');
