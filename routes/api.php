<?php

use App\Http\Controllers\Api\SurveyResponseController;
use App\Http\Controllers\Api\SurveySessionController;
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

Route::post('/survey-sessions', [SurveySessionController::class, 'store'])->name('api.survey-sessions.store');
Route::post('/survey-responses', [SurveyResponseController::class, 'store'])->name('api.survey-responses.store');

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
