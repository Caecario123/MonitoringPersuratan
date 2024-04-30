<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LetterController;

use App\Http\Controllers\API\UserController;
use App\Http\Controllers\OutgoingController;
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

Route::post('login', [UserController::class, 'login']);

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();

});

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/showletters', [LetterController::class, 'show'])
    ->middleware('user-access:admin');

    // Route::get('/letters', [LetterController::class, 'store']);

    Route::get('users', [UserController::class, 'index']);
    Route::get('users/{id}', [UserController::class, 'show']);
    Route::put('users/{id}', [UserController::class, 'update']);
    Route::delete('users/{id}', [UserController::class, 'delete']);
    Route::post('/users', [UserController::class, 'storeUser']);

    Route::post('/addletters', [LetterController::class, 'store']);
    Route::post('/updateletter/{id}', [LetterController::class, 'update']);
    Route::delete('/deleteletter/{id}', [LetterController::class, 'delete']);

    // Route::middleware('auth:sanctum')->get('/showletters', [LetterController::class, 'show']);

    // Route::get('/showletters', [LetterController::class, 'show']);
    Route::get('/showletterstu', [LetterController::class, 'showtu']);
    Route::get('/showletters1', [LetterController::class, 'showseksi1']);
    Route::get('/showletters2', [LetterController::class, 'showseksi2']);
    Route::get('/showletters3', [LetterController::class, 'showseksi3']);
    Route::get('/showletters4', [LetterController::class, 'showseksi4']);
    Route::get('/showletters5', [LetterController::class, 'showseksi5']);

    Route::put('/dispositionletters/{id}', [LetterController::class, 'disposisikan']);

    Route::delete('/deleteOutgoingLetters/{id}', [OutgoingController::class, 'delete']);
    Route::post('/addOutgoingLetters/{id}', [OutgoingController::class, 'store']);
    Route::post('/updateOutgoingLetters/{id}', [OutgoingController::class, 'update']);
    Route::get('/showOutgoingLetters', [LetterController::class, 'show']);
});

