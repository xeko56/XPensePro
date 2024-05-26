<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/transaction', [TransactionController::class, 'show']);
    Route::get('/transactions/detail', [TransactionController::class, 'dailyDetail'])->name('transaction.detail');

    // API endpoints
    Route::get('/api/transactions/summary', [TransactionController::class, 'monthlySummaryApi']);
    Route::get('/api/transactions/detail', [TransactionController::class, 'dailyDetailApi']);
});




require __DIR__.'/auth.php';
