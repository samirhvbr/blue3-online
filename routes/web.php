<?php

use App\Http\Controllers\Admin\ComponentController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\IncidentController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\StatusController;
use Illuminate\Support\Facades\Route;

// Página pública de status.
Route::get('/', [StatusController::class, 'index'])->name('status.index');

// Autenticação do painel.
Route::get('/admin/login', [LoginController::class, 'show'])->name('login');
Route::post('/admin/login', [LoginController::class, 'login'])->name('login.attempt');
Route::post('/admin/logout', [LoginController::class, 'logout'])->middleware('auth')->name('logout');

// Painel administrativo (controle do que a página mostra).
Route::middleware('auth')->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::resource('components', ComponentController::class)->except('show');
    Route::resource('incidents', IncidentController::class)->except('show');
});
