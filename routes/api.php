<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\UserPreferenceController;
use App\Http\Controllers\Api\V1\NewsApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::prefix('v1')->group(function() {
    Route::controller(AuthController::class)->group(function(){
        Route::post('register', 'createUser');
        Route::post('login', 'loginUser');
    });

    Route::middleware('auth:sanctum')->group(function () {
        Route::resource('preferences', UserPreferenceController::class);

        // Store buck user preferences
        Route::post('preferences-bulk', [UserPreferenceController::class ,'bulkStore']);

        Route::get('articles', [NewsApiController::class ,'articles']);

        Route::get('newsfeed', [NewsApiController::class ,'newsfeed']);

        Route::get('get-sources', [NewsApiController::class ,'getSources']);
    });
});

