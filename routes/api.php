<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LetterController;

use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\OutgoingController;
use App\Http\Controllers\API\HomeController;

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


Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/letters', [LetterController::class, 'store']);

Route::get('users', [UserController::class, 'index']);
Route::get('users/{id}', [UserController::class, 'show']);
Route::put('users/{id}', [UserController::class, 'update']);
Route::delete('users/{id}', [UserController::class, 'delete']);
Route::post('/storeusers', [UserController::class, 'storeUser']);

Route::post('/addletters', [LetterController::class, 'store']);
Route::post('/updateletters/{id}', [LetterController::class, 'update']);
Route::delete('/deleteletters/{id}', [LetterController::class, 'delete']);
Route::put('/dispositionletters/{id}', [LetterController::class, 'disposisikan']);

Route::delete('/deleteOutgoingLetters/{id}', [OutgoingController::class, 'delete']);
Route::post('/addOutgoingLetters/{id}', [OutgoingController::class, 'store']);
Route::post('/updateOutgoingLetters/{id}', [OutgoingController::class, 'update']);

Route::post('/addOutgoingLetters/{id}', [OutgoingController::class, 'store']);
