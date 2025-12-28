<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\JenisMobilController;
use App\Http\Controllers\AdminDashboardController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MerekMobilController;
use App\Models\Penempatan;

// Registration is restricted to admin only
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/register', function () {
        $penempatans = Penempatan::all();
        return view('auth.register', compact('penempatans'));
    })->name('register.form');

    Route::post('/register', [AuthController::class, 'store'])->name('register.store');
});

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

// Admin dashboard
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
});

// LOGOUT route
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Employee view: daftar mobil untuk pegawai (simple view)
Route::get('/pegawai/mobil', function() {
    $mobils = \App\Models\Mobil::whereNull('is_deleted')->get();
    return view('mobil.pegawai_index', compact('mobils'));
})->middleware('auth')->name('mobil.pegawai.index');




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

use App\Http\Controllers\PenempatanCRUDController;

Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/penempatan', [PenempatanCRUDController::class, 'index'])->name('penempatan.index');
    Route::get('/penempatan/create', [PenempatanCRUDController::class, 'create'])->name('penempatan.create');
    Route::post('/penempatan', [PenempatanCRUDController::class, 'store'])->name('penempatan.store');
    Route::get('/penempatan/{penempatan}/edit', [PenempatanCRUDController::class, 'edit'])->name('penempatan.edit');
    Route::put('/penempatan/{penempatan}', [PenempatanCRUDController::class, 'update'])->name('penempatan.update');
    Route::delete('/penempatan/{penempatan}', [PenempatanCRUDController::class, 'destroy'])->name('penempatan.destroy');
});


use App\Http\Controllers\MobilController;

// Semua route mobil memerlukan autentikasi
Route::middleware(['auth'])->group(function () {
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

    // PERMANENT DELETE MOBIL
    Route::delete('/mobil/{id}/force', [MobilController::class, 'forceDelete'])->name('mobil.forceDelete');
});


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
    
    // Hapus pemakaian (pegawai dapat hapus sendiri saat status pending; admin juga bisa)
    Route::delete('/pemakaian/{id}', [PemakaianMobilController::class, 'destroy'])
        ->name('pemakaian.destroy');


});


use App\Http\Controllers\PemakaianMobilAdminController;

// Route admin pemakaian - hanya untuk admin/penempatan
Route::middleware(['auth', 'role:admin,penempatan'])->group(function () {
    // IMPORTANT: Specific routes MUST come before parameterized routes!
    
    // Endpoint untuk cek data baru (AJAX polling) - HARUS SEBELUM /{id}/detail
    Route::get('/admin/pemakaian/check-new', [PemakaianMobilAdminController::class, 'checkNew'])
        ->name('admin.pemakaian.checkNew');

    // Dedicated AJAX endpoint: return only table partial HTML
    Route::get('/admin/pemakaian/list', [PemakaianMobilAdminController::class, 'list'])
        ->name('admin.pemakaian.list');

    // Export selected (CSV) and bulk delete
    Route::post('/admin/pemakaian/export', [PemakaianMobilAdminController::class, 'export'])
        ->name('admin.pemakaian.export');

    Route::post('/admin/pemakaian/bulk-delete', [PemakaianMobilAdminController::class, 'bulkDelete'])
        ->name('admin.pemakaian.bulkDelete');

    // Daftar semua pemakaian untuk admin
    Route::get('/admin/pemakaian', [PemakaianMobilAdminController::class, 'daftar'])
        ->name('admin.pemakaian.daftar');

    // Detail pemakaian (modal) - HARUS SETELAH specific routes
    Route::get('/admin/pemakaian/{id}/detail', [PemakaianMobilAdminController::class, 'detail'])
        ->name('admin.pemakaian.detail');

    // Ubah status pemakaian
    Route::post('/admin/pemakaian/{id}/ubah-status', [PemakaianMobilAdminController::class, 'ubahStatus'])
        ->name('admin.pemakaian.ubahStatus');
});

use App\Http\Controllers\ExportImportController;

// Admin tools: import/export (XLSX)
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin/tools/import-export', [ExportImportController::class, 'index'])->name('admin.tools.importExport');
    Route::get('/admin/tools/template/{model}', [ExportImportController::class, 'downloadTemplate'])->name('admin.tools.template');
    Route::post('/admin/tools/export', [ExportImportController::class, 'export'])->name('admin.tools.export');
    Route::post('/admin/tools/import', [ExportImportController::class, 'import'])->name('admin.tools.import');
});

// Simpan push subscription dari client - hanya untuk admin
use App\Http\Controllers\PushSubscriptionController;
Route::post('/admin/push/subscribe', [PushSubscriptionController::class, 'store'])
    ->name('admin.push.subscribe')
    ->middleware(['auth', 'role:admin,penempatan']);

// Test push notification removed (cleanup)

use App\Http\Controllers\TesCloudinaryController;

Route::get('/tes-cloudinary', [TesCloudinaryController::class, 'index'])->name('tes.cloudinary.form');
Route::post('/tes-cloudinary', [TesCloudinaryController::class, 'upload'])->name('tes.cloudinary.upload');
