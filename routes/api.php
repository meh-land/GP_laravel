<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PassportAuthController;
use App\Http\Controllers\TestingController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::post('register', [PassportAuthController::class, 'register']);
Route::post('login', [PassportAuthController::class, 'login']);
Route::get('test',  [TestingController::class, 'test']);

Route::middleware(['auth:api'])->group(function () {
    Route::get('show', [PassportAuthController::class, 'show']);
    Route::put('update', [PassportAuthController::class, 'update']);
    Route::delete('delete', [PassportAuthController::class, 'delete']);
});