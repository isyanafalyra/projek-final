<?php

use App\Http\Controllers\ProfileController;
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
});

use App\Http\Controllers\Api\InternalApiController;

Route::prefix('api')->group(function () {
    Route::get('/countries', [InternalApiController::class, 'countries']);
    Route::get('/risk', [InternalApiController::class, 'risk']);
    Route::get('/ports', [InternalApiController::class, 'ports']);
    Route::get('/news', [InternalApiController::class, 'news']);
    Route::get('/currency', [InternalApiController::class, 'currency']);
});

require __DIR__.'/auth.php';
