<?php

use App\Http\Controllers\UserController;
use App\Http\Controllers\WelcomeVideoController;
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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
Route::post('register', [UserController::class, 'createUser']);

Route::group(['middleware' => ['auth:api']], function () {
    Route::get('verify', [UserController::class, 'verify']);
    Route::post('update-user', [UserController::class, 'updateUser']);


    /////////////Routes For Admin
    ////// User
    Route::get('users', [UserController::class, 'users']);
    Route::post('block-user', [UserController::class, 'deActive']);
    Route::post('user-status', [UserController::class, 'status']);

    //////// Welcome Video
    Route::get('all-welcome-videos', [WelcomeVideoController::class, 'allWelcomeVideos']);
    Route::post('add-welcome-video', [WelcomeVideoController::class, 'uploadWelcomeVideo']);
    Route::delete('delete-welcome-video/{id}', [WelcomeVideoController::class, 'deleteWelcomeVideo']);
});

Route::get('user-detail/{id}', [UserController::class, 'userDetail']);
