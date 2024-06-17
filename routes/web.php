<?php

use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');
Route::get('/chat/{id}', [DashboardController::class, 'chat'])->middleware(['auth', 'verified'])->name('chat');

Route::view('profile', 'profile')->middleware(['auth'])->name('profile');
Route::view('/', 'welcome');

require __DIR__.'/auth.php';
