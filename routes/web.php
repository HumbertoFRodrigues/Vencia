<?php

use App\Http\Controllers\Auth\LoginController;
use App\Livewire\Clientes\ClienteDetail;
use App\Livewire\Clientes\ClientesIndex;
use App\Livewire\Dashboard;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->name('login.store');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

    Route::get('/', Dashboard::class)->name('dashboard');

    Route::get('/clientes', ClientesIndex::class)->name('clientes.index');
    Route::get('/clientes/{cliente}', ClienteDetail::class)->name('clientes.show');
});
