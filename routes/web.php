<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LandingPageController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DocumentShareController;
use App\Http\Controllers\FriendsController;
use App\Http\Controllers\MessageController;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\DocumenShareController;

/*
|--------------------------------------------------------------------------
| Public Routes (Halaman Publik)
|--------------------------------------------------------------------------
*/

// Rute untuk halaman utama (Landing Page)
Route::get('/', [LandingPageController::class, 'index'])->name('landing');

// Rute untuk halaman welcome (HTML murni)
Route::get('/welcome', function () {
    return view('welcome');  // Ini akan merender file welcome.blade.php
})->name('welcome');

/*
|--------------------------------------------------------------------------
| Authentication Routes (Rute Autentikasi: Login, Register, Logout)
|--------------------------------------------------------------------------
*/

Auth::routes(); // Menggunakan Auth::routes() sudah otomatis menangani rute login, register, dan logout

/*
|--------------------------------------------------------------------------
| Authenticated Routes (Rute yang Memerlukan Autentikasi)
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {
    // Dashboard
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Rute untuk halaman profil pengguna
    Route::get('/profile/index', [ProfileController::class, 'show'])->name('profile.index');

    // Rute untuk mengupdate profil pengguna
    Route::post('/profile/update', [ProfileController::class, 'update'])->name('profile.update1');

    // Route::get('/profile', [ProfileController::class, 'profile'])->name('profile');
    Route::get('/profile/update', [ProfileController::class, 'editProfile'])->name('profile.update');
    Route::put('/profile/update', [ProfileController::class, 'update'])->name('profile.update');

    /*
    |--------------------------------------------------------------------------
    | Document Routes (Rute Dokumen)
    |--------------------------------------------------------------------------
    */

    // Rute untuk menampilkan daftar dokumen
    Route::get('documents', [DocumentController::class, 'index'])->name('documents.index');

    // Rute untuk menampilkan form upload dokumen
    Route::get('documents/upload', [DocumentController::class, 'showUploadForm'])->name('documents.upload');

    // Rute untuk menyimpan dokumen yang di-upload
    Route::post('documents/store', [DocumentController::class, 'store'])->name('documents.store');

    // Rute untuk mengedit nama dokumen
    Route::get('documents/edit/{document_id}', [DocumentController::class, 'edit'])->name('documents.edit');
    Route::post('documents/update/{document_id}', [DocumentController::class, 'update'])->name('documents.update');

    // Rute untuk menghapus dokumen
    Route::delete('documents/delete/{document_id}', [DocumentController::class, 'delete'])->name('documents.delete');
    Route::get('documents/{id}/view', [DocumentController::class, 'view'])->name('documents.view');
    Route::get('documents/{id}/download', [DocumentController::class, 'download'])->name('documents.download');

    // rute untuk melihat file
    Route::get('/documents/view/{document_id}', [DocumentController::class, 'view'])->name('documents.view');
    Route::get('/documents/download-pdf/{document_id}', [DocumentController::class, 'downloadAsPdf'])->name('documents.download.pdf');

    Route::post('/documents/share/{id}', [DocumentController::class, 'share'])->name('documents.share');

    Route::get('documents/{id}/view', [DocumentController::class, 'view'])->name('documentShare.view');
    Route::delete('/documents/unshare/{document_id}/{share_id}', [DocumentController::class, 'unshare'])->name('documents.unshare');

    Route::get('/documentShare', [DocumentShareController::class, 'index'])->name('documentShare.index');
    Route::post('/documentShare/share/{id}', [DocumentShareController::class, 'share'])->name('documentShare.share');
});

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
