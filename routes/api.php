<?php

use App\Http\Controllers\LiveScoreController;
use App\Http\Controllers\LivescoreUserCreditController;
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

Route::any('/livescore', [LiveScoreController::class, 'get_livescore']);
Route::any('/livescore/pay', [LiveScoreController::class, 'pay']);
Route::any('/livescore/verify', [LiveScoreController::class, 'verify']);
Route::any('/livescore/credit-per-month', [LivescoreUserCreditController::class, 'credit_per_month']);
