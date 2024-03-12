<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;

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

Route::get('/', function () {
    return view('welcome');
});

//Clear Cache Commands
Route::get('/cls', function() {
    $exitCode = Artisan::call('config:clear');
    $exitCode = Artisan::call('cache:clear');
    $exitCode = Artisan::call('config:cache');
    return "Cache is cleared"; //Return anything
});

Route::get('/login', function () {
    return response()->json(['error' => 'Unauthenticated.'], 405);
})->name('login');

Route::get('/admin_login', function () {
    return response()->json(['error' => 'Unauthenticated.'], 405);
})->name('admin_login');

Route::get('/vendor_login', function () {
    return response()->json(['error' => 'Unauthenticated.'], 405);
})->name('vendor_login');

Route::get('/response', function () {
    return view('response');
});

Route::get('confirm/{code}','App\Http\Controllers\Controller@confirmAccount');

