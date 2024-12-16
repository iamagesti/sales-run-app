<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
use App\Http\Controllers\SalesController;

// Route::get('/', function () {
//     return view('welcome');
// });
Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/', function () {
        return redirect('/admin');
    });
});

Route::get('/login', function () {
    return redirect(route('filament.admin.auth.login'));
})->name('login');

Route::get('preview-invoice/{id}', [SalesController::class, 'preview'])->name('preview-invoice');
Route::get('download-invoice/{id}', [SalesController::class, 'download'])->name('download-invoice');
