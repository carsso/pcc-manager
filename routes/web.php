<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PccController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\OvhApiController;
use App\Http\Controllers\DarkmodeController;

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

Route::get('/', [HomeController::class, 'index'])
    ->middleware('guest')
    ->name('home');

Route::get('/legal', [HomeController::class, 'legal'])
    ->name('legal');

Route::get('/logout', [OvhApiController::class, 'logout'])
    ->name('logout');

Route::get('/login/{endpoint}', [OvhApiController::class, 'login'])
    ->middleware('guest')
    ->name('login');

Route::get('/login/{endpoint}/read-only', [OvhApiController::class, 'loginReadOnly'])
    ->middleware('guest')
    ->name('login.read-only');

Route::get('/login/{endpoint}/token/{token}', [OvhApiController::class, 'token'])
    ->middleware('guest')
    ->name('login.token');

Route::get('/login/{endpoint}/redirect', [OvhApiController::class, 'redirect'])
    ->middleware('guest')
    ->name('login.redirect');

Route::get('/darkmode/{enable}', [DarkmodeController::class, 'json'])
    ->name('darkmode');

Route::group(['middleware' => ['auth']], function() {

    Route::get('/pcc', [PccController::class, 'index'])
        ->name('pcc');

    Route::get('/pcc/{pccName}', [PccController::class, 'pcc'])
        ->name('pcc.pcc');

    Route::get('/pcc/{pccName}/datacenter/{datacenterId}', [PccController::class, 'datacenter'])
        ->name('pcc.datacenter');

    Route::get('/pcc/{pccName}/datacenter/{datacenterId}/{entityType}/{entityId}/graphs', [PccController::class, 'graphs'])
        ->where('entityType', 'host|filer|vm')
        ->name('pcc.graphs');

    Route::any('/ovhapi{uri?}', [OvhApiController::class, 'request'])
        ->where('uri', '.*')
        ->name('ovhapi');

});