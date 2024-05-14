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
    Route::get('/show-admin-letters', [LetterController::class, 'show'])
    ->middleware('user-access:admin');

    // Route::get('/letters', [LetterController::class, 'store']);

    Route::get('users', [UserController::class, 'index']);
    Route::get('users/{id}', [UserController::class, 'show']);
    Route::put('users/{id}', [UserController::class, 'update']);
    Route::delete('users/{id}', [UserController::class, 'delete']);
    Route::post('/users', [UserController::class, 'storeUser']);
    Route::put('/changePassword/{id}', [UserController::class, 'changePassword']);

    Route::post('/add-letters', [LetterController::class, 'store']);
    Route::post('/update-letter/{id}', [LetterController::class, 'update']);
    Route::delete('/delete-letter/{id}', [LetterController::class, 'delete']);

    // Route::middleware('auth:sanctum')->get('/showletters', [LetterController::class, 'show']);

    // Route::get('/showletters', [LetterController::class, 'show']);
    Route::get('/show-letterstu', [LetterController::class, 'showtu'])->middleware('user-access:tatausaha');
    Route::get('/show-letters1', [LetterController::class, 'showseksi1'])->middleware('user-access:seksi1');
    Route::get('/show-letters2', [LetterController::class, 'showseksi2'])->middleware('user-access:seksi2');
    Route::get('/show-letters3', [LetterController::class, 'showseksi3'])->middleware('user-access:seksi3');
    Route::get('/show-letters4', [LetterController::class, 'showseksi4'])->middleware('user-access:seksi4');
    Route::get('/show-letters5', [LetterController::class, 'showseksi5'])->middleware('user-access:seksi5');
   
    // menampilkan detail surat
    Route::get('/show-detail-letters/{id}', [LetterController::class, 'showdetailletter']);

    // menampilkan data rekap
    Route::get('/show-letters', [LetterController::class, 'showAllLetters']);//rekap

    // disposisi
    Route::put('/disposition/{id}', [LetterController::class, 'disposisikan']);

    // surat balasan
    Route::delete('/delete-reply/{id}', [OutgoingController::class, 'delete']);
    Route::post('/add-reply/{id}', [OutgoingController::class, 'store']);
    Route::post('/update-reply/{id}', [OutgoingController::class, 'update']);
    Route::get('/show-reply', [OutgoingController::class,  'daftarbalasan']);
    Route::get('/show-reply/{id}', [OutgoingController::class,  'daftarbalasan']);
    Route::get('/show-reply-detail/{id}', [OutgoingController::class,  'detailbalasan']);
    Route::get('/coba', [LetterController::class, 'showletter']);
    //rekap
    Route::delete('/cobadelete', [LetterController::class, 'deleteAllFiles']);
    Route::get('/show-file/{id}', [LetterController::class, 'streamOutgoingPDF']);
    Route::get('/show-file-balas/{id}', [OutgoingController::class, 'streamOutgoingPDF']);

});

