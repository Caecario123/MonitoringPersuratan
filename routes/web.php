<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\OutgoingController;

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
    Route::post('/store2', [HomeController::class, 'store2'])->name('letters.store2');

    Route::get('/dashboard', [HomeController::class, 'index'])->name('dashboard');
});

Route::middleware(['auth', 'user-access:admin'])->group(function () {
   
    Route::get('/admin/dashboard', [HomeController::class, 'adminDashboard'])->name('admin.dashboard');

    Route::get('/user',[HomeController::class,'index'])->name('admin.index');
    Route::get('/create',[HomeController::class,'create'])->name('admin.user.create');
    Route::post('/store',[HomeController::class,'store'])->name('admin.user.store');
    Route::get('/admin/disposisi{id}', [HomeController::class, 'disposisi'])->name('admin.letters.disposisi');
    Route::put('/admin/disposisikan{id}', [HomeController::class, 'disposisikan'])->name('admin.letters.disposisikan');

    Route::get('/admin/balasan{id}', [OutgoingController::class, 'balasan'])->name('admin.letters.balasan');
    Route::post('/admin/balasansurat', [OutgoingController::class, 'balasansurat'])->name('admin.balasansurat');

    Route::get('/admin/daftarbalasan{id}', [OutgoingController::class, 'daftarbalasan'])->name('admin.daftarbalasan');
    Route::get('/edit/{id}',[HomeController::class,'edit'])->name('admin.user.edit');
    Route::put('/update/{id}',[HomeController::class,'update'])->name('admin.user.update');
    Route::delete('/delete/{id}',[HomeController::class,'delete'])->name('admin.user.delete');
    Route::get('/admin/streamPDF/{id}', [HomeController::class, 'streamPDF'])->name('admin.file.streamPDF');
    Route::get('/admin/tambahsurat', [HomeController::class, 'tambahsurat'])->name('admin.letters.tambahsurat');
    Route::get('/admin/detailsurat{id}', [HomeController::class, 'detailSurat'])->name('admin.letters.detailSurat');
    Route::post('/admin/store2', [HomeController::class, 'store2'])->name('admin.letters.store2');


});

Route::middleware(['auth', 'user-access:kakan'])->group(function () {
   
    Route::get('/kakan/dashboard', [HomeController::class, 'kakanDashboard'])->name('kakan.dashboard');

    Route::get('/admin/daftarbalasan{id}', [OutgoingController::class, 'daftarbalasan'])->name('admin.daftarbalasan');
    Route::get('/admin/streamPDF/{id}', [HomeController::class, 'streamPDF'])->name('admin.file.streamPDF');
    Route::get('/admin/tambahsurat', [HomeController::class, 'tambahsurat'])->name('admin.letters.tambahsurat');
    Route::get('/admin/detailsurat{id}', [HomeController::class, 'detailSurat'])->name('admin.letters.detailSurat');
    Route::post('/admin/store2', [HomeController::class, 'store2'])->name('admin.letters.store2');
});
Route::middleware(['auth', 'user-access:tatausaha'])->group(function () {
   
    Route::get('/tatausaha/dashboard', [HomeController::class, 'tatausahaDashboard'])->name('tatausaha.dashboard');

    Route::get('/tatausaha/disposisi{id}', [HomeController::class, 'disposisi'])->name('tatausaha.letters.disposisi');
    Route::put('/tatausaha/disposisikan{id}', [HomeController::class, 'disposisikan'])->name('tatausaha.letters.disposisikan');

    Route::get('/tatausaha/balasan{id}', [OutgoingController::class, 'balasan'])->name('tatausaha.letters.balasan');
    Route::post('/tatausaha/balasansurat', [OutgoingController::class, 'balasansurat'])->name('tatausaha.balasansurat');

    Route::get('/tatausaha/daftarbalasan{id}', [OutgoingController::class, 'daftarbalasan'])->name('tatausaha.daftarbalasan');
    Route::get('/tatausaha/streamPDF/{id}', [HomeController::class, 'streamPDF'])->name('tatausaha.file.streamPDF');
    Route::get('/tatausaha/detailsurat{id}', [HomeController::class, 'detailSurat'])->name('tatausaha.letters.detailSurat');
});
Route::middleware(['auth', 'user-access:seksi1'])->group(function () {
   
    Route::get('/seksi1/dashboard', [HomeController::class, 'seksi1Dashboard'])->name('seksi1.dashboard');
    Route::get('/letters', [HomeController::class, 'index2']);

    Route::get('/seksi1/balasan{id}', [OutgoingController::class, 'balasan'])->name('seksi1.letters.balasan');
    Route::post('/seksi1/balasansurat', [OutgoingController::class, 'balasansurat'])->name('seksi1.balasansurat');
    Route::get('/seksi1/daftarbalasan{id}', [OutgoingController::class, 'daftarbalasan'])->name('seksi1.daftarbalasan');

    Route::get('/seksi1/streamOutgoingPDF{id}', [OutgoingController::class, 'streamOutgoingPDF'])->name('seksi1.filebalasan.streamOutgoingPDF');
    Route::get('/seksi1/streamPDF/{id}', [HomeController::class, 'streamPDF'])->name('seksi1.file.streamPDF');
    Route::get('/seksi1/detailsurat{id}', [HomeController::class, 'detailSurat'])->name('seksi1.letters.detailSurat');
    Route::get('/seksi1/newletterlist', [HomeController::class, 'newletterlist'])->name('seksi1.newletterlist');
});
Route::middleware(['auth', 'user-access:seksi2'])->group(function () {
   
    Route::get('/seksi2/dashboard', [HomeController::class, 'seksi2Dashboard'])->name('seksi2.dashboard');
    Route::get('/seksi2/letters', [HomeController::class, 'index2']);

    Route::get('/seksi2/balasan{id}', [OutgoingController::class, 'balasan'])->name('seksi2.letters.balasan');
    Route::post('/seksi2/balasansurat', [OutgoingController::class, 'balasansurat'])->name('seksi2.balasansurat');
    Route::get('/seksi2/daftarbalasan{id}', [OutgoingController::class, 'daftarbalasan'])->name('seksi2.daftarbalasan');

    Route::get('/seksi2/streamOutgoingPDF{id}', [OutgoingController::class, 'streamOutgoingPDF'])->name('seksi2.filebalasan.streamOutgoingPDF');
    Route::get('/seksi2/streamPDF/{id}', [HomeController::class, 'streamPDF'])->name('seksi2.file.streamPDF');
    Route::get('/seksi2/detailsurat{id}', [HomeController::class, 'detailSurat'])->name('seksi2.letters.detailSurat');
    Route::get('/seksi2/newletterlist', [HomeController::class, 'newletterlist'])->name('seksi2.newletterlist');
});
Route::middleware(['auth', 'user-access:seksi3'])->group(function () {
   
    Route::get('/seksi3/dashboard', [HomeController::class, 'seksi3Dashboard'])->name('seksi3.dashboard');
    Route::get('/seksi3/letters', [HomeController::class, 'index2']);

    Route::get('/seksi3/balasan{id}', [OutgoingController::class, 'balasan'])->name('seksi3.letters.balasan');
    Route::post('/seksi3/balasansurat', [OutgoingController::class, 'balasansurat'])->name('seksi3.balasansurat');
    Route::get('/seksi3/daftarbalasan{id}', [OutgoingController::class, 'daftarbalasan'])->name('seksi3.daftarbalasan');

    Route::get('/seksi3/streamOutgoingPDF{id}', [OutgoingController::class, 'streamOutgoingPDF'])->name('seksi3.filebalasan.streamOutgoingPDF');
    Route::get('/seksi3/streamPDF/{id}', [HomeController::class, 'streamPDF'])->name('seksi3.file.streamPDF');
    Route::get('/seksi3/detailsurat{id}', [HomeController::class, 'detailSurat'])->name('seksi3.letters.detailSurat');
    Route::get('/seksi3/newletterlist', [HomeController::class, 'newletterlist'])->name('seksi3.newletterlist');
});
Route::middleware(['auth', 'user-access:seksi4'])->group(function () {
   
    Route::get('/seksi4/dashboard', [HomeController::class, 'seksi4Dashboard'])->name('seksi4.dashboard');
    Route::get('/letters', [HomeController::class, 'index2']);

    Route::get('/seksi4/balasan{id}', [OutgoingController::class, 'balasan'])->name('seksi4.letters.balasan');
    Route::post('/seksi4/balasansurat', [OutgoingController::class, 'balasansurat'])->name('seksi4.balasansurat');
    Route::get('/seksi4/daftarbalasan{id}', [OutgoingController::class, 'daftarbalasan'])->name('seksi4.daftarbalasan');

    Route::get('/seksi4/streamOutgoingPDF{id}', [OutgoingController::class, 'streamOutgoingPDF'])->name('seksi4.filebalasan.streamOutgoingPDF');
    Route::get('/seksi4/streamPDF/{id}', [HomeController::class, 'streamPDF'])->name('seksi4.file.streamPDF');
    Route::get('/seksi4/detailsurat{id}', [HomeController::class, 'detailSurat'])->name('seksi4.letters.detailSurat');
    Route::get('/seksi4/newletterlist', [HomeController::class, 'newletterlist'])->name('seksi4.newletterlist');
});
Route::middleware(['auth', 'user-access:seksi5'])->group(function () {
   
    Route::get('/seksi5/dashboard', [HomeController::class, 'seksi5Dashboard'])->name('seksi5.dashboard');
    Route::get('/seksi5/letters', [HomeController::class, 'index2']);

    Route::get('/seksi5/balasan{id}', [OutgoingController::class, 'balasan'])->name('seksi5.letters.balasan');
    Route::post('/seksi5/balasansurat', [OutgoingController::class, 'balasansurat'])->name('seksi5.balasansurat');
    Route::get('/seksi5/daftarbalasan{id}', [OutgoingController::class, 'daftarbalasan'])->name('seksi5.daftarbalasan');

    Route::get('/seksi5/streamOutgoingPDF{id}', [OutgoingController::class, 'streamOutgoingPDF'])->name('seksi5.filebalasan.streamOutgoingPDF');
    Route::get('/seksi5/streamPDF/{id}', [HomeController::class, 'streamPDF'])->name('seksi5.file.streamPDF');
    Route::get('/seksi5/detailsurat{id}', [HomeController::class, 'detailSurat'])->name('seksi5.letters.detailSurat');
    Route::get('/seksi5/newletterlist', [HomeController::class, 'newletterlist'])->name('seksi5.newletterlist');
});