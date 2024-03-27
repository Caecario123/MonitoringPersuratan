<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LoginController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Route::get('/', function () {
//     return view('welcome');
// });

Route::get('/login',[LoginController::class,'index'])->name('login');
Route::post('/login-proses',[LoginController::class,'login_proses'])->name('login-proses');
Route::get('/logout',[LoginController::class,'logout'])->name('logout');

// Route::group(['prefix' => 'admin','middleware' =>['auth'],'as' => 'admin.'],function(){
//     Route::get('/dashboard',[HomeController::class,'dashboard'])->name('dashboard');

//     Route::get('/user',[HomeController::class,'index'])->name('index');
//     Route::get('/create',[HomeController::class,'create'])->name('user.create');
//     Route::post('/store',[HomeController::class,'store'])->name('user.store');

//     Route::get('/edit/{id}',[HomeController::class,'edit'])->name('user.edit');
//     Route::put('/update/{id}',[HomeController::class,'update'])->name('user.update');
//     Route::delete('/delete/{id}',[HomeController::class,'delete'])->name('user.delete');
//     });

Route::middleware(['auth', 'user-access:user'])->group(function () {
   
    Route::get('/dashboard', [HomeController::class, 'index'])->name('dashboard');
});

Route::middleware(['auth', 'user-access:admin'])->group(function () {
   
    Route::get('/admin/dashboard', [HomeController::class, 'adminDashboard'])->name('admin.dashboard');

    Route::get('/user',[HomeController::class,'index'])->name('admin.index');
    Route::get('/create',[HomeController::class,'create'])->name('admin.user.create');
    Route::post('/store',[HomeController::class,'store'])->name('admin.user.store');

    Route::get('/edit/{id}',[HomeController::class,'edit'])->name('admin.user.edit');
    Route::put('/update/{id}',[HomeController::class,'update'])->name('admin.user.update');
    Route::delete('/delete/{id}',[HomeController::class,'delete'])->name('admin.user.delete');
});

Route::middleware(['auth', 'user-access:kakan'])->group(function () {
   
    Route::get('/kakan/dashboard', [HomeController::class, 'kakanDashboard'])->name('kakan.dashboard');
});
Route::middleware(['auth', 'user-access:tatausaha'])->group(function () {
   
    Route::get('/tatausaha/dashboard', [HomeController::class, 'tatausahaDashboard'])->name('tatausaha.dashboard');
});
Route::middleware(['auth', 'user-access:seksi1'])->group(function () {
   
    Route::get('/seksi1/dashboard', [HomeController::class, 'seksi1Dashboard'])->name('seksi1.dashboard');
    Route::get('/tambahsurat', [HomeController::class, 'tambahsurat'])->name('letters.tambahsurat');
    Route::post('/store2', [HomeController::class, 'store2'])->name('letters.store2');
    Route::get('/letters', [HomeController::class, 'index2']);

    Route::get('/disposisi{id}', [HomeController::class, 'disposisi'])->name('letters.disposisi');
    Route::put('/disposisikan{id}', [HomeController::class, 'disposisikan'])->name('letters.disposisikan');
    Route::get('/balasan{id}', [OutgoingController::class, 'balasan'])->name('letters.balasan');
    Route::post('/balasansurat', [OutgoingController::class, 'balasansurat'])->name('balasansurat');
    Route::get('/daftarbalasan{id}', [OutgoingController::class, 'daftarbalasan'])->name('daftarbalasan');

    Route::get('/streamOutgoingPDF{id}', [OutgoingController::class, 'streamOutgoingPDF'])->name('filebalasan.streamOutgoingPDF');

    Route::get('/streamPDF/{id}', [HomeController::class, 'streamPDF'])->name('file.streamPDF');


    Route::get('/detailsurat{id}', [HomeController::class, 'detailSurat'])->name('letters.detailSurat');

    Route::get('/newletterlist', [HomeController::class, 'newletterlist'])->name('newletterlist');
});
Route::middleware(['auth', 'user-access:seksi2'])->group(function () {
   
    Route::get('/seksi2/dashboard', [HomeController::class, 'seksi2Dashboard'])->name('seksi2.dashboard');
});
Route::middleware(['auth', 'user-access:seksi3'])->group(function () {
   
    Route::get('/seksi3/dashboard', [HomeController::class, 'seksi3Dashboard'])->name('seksi3.dashboard');
});
Route::middleware(['auth', 'user-access:seksi4'])->group(function () {
   
    Route::get('/seksi4/dashboard', [HomeController::class, 'seksi4Dashboard'])->name('seksi4.dashboard');
});
Route::middleware(['auth', 'user-access:seksi5'])->group(function () {
   
    Route::get('/seksi5/dashboard', [HomeController::class, 'seksi5Dashboard'])->name('seksi5.dashboard');
});