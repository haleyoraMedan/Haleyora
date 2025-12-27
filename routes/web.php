<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\JenisMobilController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MerekMobilController;

Route::get('/register', function () {
    return view('auth.register');
});

Route::post('/register', [AuthController::class, 'store']);

Route::get('/login', function () {
    return view('auth.login');
})->name('login');

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth')->group(function () {
    Route::get('/jenis-mobil', [JenisMobilController::class, 'index'])
        ->name('jenis-mobil.index');

    Route::post('/jenis-mobil', [JenisMobilController::class, 'store'])
        ->name('jenis-mobil.store');

    Route::put('/jenis-mobil/{id}', [JenisMobilController::class, 'update'])
        ->name('jenis-mobil.update');

    Route::delete('/jenis-mobil/{id}', [JenisMobilController::class, 'destroy'])
        ->name('jenis-mobil.destroy');
});




Route::middleware('auth')->group(function () {
    Route::get('/merek-mobil', [MerekMobilController::class, 'index'])
        ->name('merek-mobil.index');

    Route::post('/merek-mobil', [MerekMobilController::class, 'store'])
        ->name('merek-mobil.store');

    Route::put('/merek-mobil/{id}', [MerekMobilController::class, 'update'])
        ->name('merek-mobil.update');

    Route::delete('/merek-mobil/{id}', [MerekMobilController::class, 'destroy'])
        ->name('merek-mobil.destroy');
});


use App\Http\Controllers\UserController;

Route::middleware('auth')->group(function () {
    Route::get('/user', [UserController::class, 'index'])->name('user.index');
    Route::get('/user/{id}/edit', [UserController::class, 'edit'])->name('user.edit');
    Route::put('/user/{id}', [UserController::class, 'update'])->name('user.update');
    Route::delete('/user/{id}', [UserController::class, 'destroy'])->name('user.destroy');
    Route::post('/user/{id}/restore', [UserController::class, 'restore'])->name('user.restore');
});

use App\Http\Controllers\PenempatanController;

Route::middleware('auth')->group(function () {
    Route::get('/penempatan', [PenempatanController::class, 'index'])->name('penempatan.index');
    Route::get('/penempatan/create', [PenempatanController::class, 'create'])->name('penempatan.create');
    Route::post('/penempatan', [PenempatanController::class, 'store'])->name('penempatan.store');
    Route::get('/penempatan/{id}/edit', [PenempatanController::class, 'edit'])->name('penempatan.edit');
    Route::put('/penempatan/{id}', [PenempatanController::class, 'update'])->name('penempatan.update');
    Route::delete('/penempatan/{id}', [PenempatanController::class, 'destroy'])->name('penempatan.destroy');
    Route::get('/penempatan/{id}', [PenempatanController::class, 'show'])->name('penempatan.show'); 

});


use App\Http\Controllers\MobilController;

// INDEX & SEARCH
Route::get('/mobil', [MobilController::class, 'index'])->name('mobil.index');

// CREATE FORM
Route::get('/mobil/create', [MobilController::class, 'create'])->name('mobil.create');

// STORE NEW MOBIL
Route::post('/mobil', [MobilController::class, 'store'])->name('mobil.store');

// EDIT FORM
Route::get('/mobil/{id}/edit', [MobilController::class, 'edit'])->name('mobil.edit');

// UPDATE MOBIL
Route::put('/mobil/{id}', [MobilController::class, 'update'])->name('mobil.update');

// SOFT DELETE MOBIL
Route::delete('/mobil/{id}', [MobilController::class, 'destroy'])->name('mobil.destroy');

// RESTORE MOBIL
Route::post('/mobil/{id}/restore', [MobilController::class, 'restore'])->name('mobil.restore');


use App\Http\Controllers\PemakaianMobilController;

Route::middleware(['auth'])->group(function () {

    Route::get('/pemakaian/pilih-mobil', [PemakaianMobilController::class, 'pilihMobil'])
        ->name('pemakaian.pilihMobil');

    Route::post('/pemakaian/pilih-mobil', [PemakaianMobilController::class, 'simpanPilihanMobil'])
        ->name('pemakaian.simpanPilihan');

    Route::get('/pemakaian/input-detail', [PemakaianMobilController::class, 'inputDetail'])
        ->name('pemakaian.inputDetail');

    Route::post('/pemakaian/input-detail/{id?}', [PemakaianMobilController::class, 'simpanDetail'])
        ->name('pemakaian.simpanDetail');

    Route::get('/pemakaian/daftar', [PemakaianMobilController::class, 'daftar'])
        ->name('pemakaian.daftar');
    
    Route::get('/pemakaian/detail/{id}', [PemakaianMobilController::class, 'detail']);


});


use App\Http\Controllers\PemakaianMobilAdminController;

// Daftar semua pemakaian untuk admin
Route::get('/admin/pemakaian', [PemakaianMobilAdminController::class, 'daftar'])
    ->name('admin.pemakaian.daftar')
    ->middleware('auth');

// Detail pemakaian (modal)
Route::get('/admin/pemakaian/{id}/detail', [PemakaianMobilAdminController::class, 'detail'])
    ->name('admin.pemakaian.detail')
    ->middleware('auth');

// Ubah status pemakaian
Route::post('/admin/pemakaian/{id}/ubah-status', [PemakaianMobilAdminController::class, 'ubahStatus'])
    ->name('admin.pemakaian.ubahStatus')
    ->middleware('auth');



use App\Http\Controllers\TesCloudinaryController;

Route::get('/tes-cloudinary', [TesCloudinaryController::class, 'index'])->name('tes.cloudinary.form');
Route::post('/tes-cloudinary', [TesCloudinaryController::class, 'upload'])->name('tes.cloudinary.upload');
