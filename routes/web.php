<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AdminController;
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
    Route::post('/watchlist/toggle', [InternalApiController::class, 'toggleWatchlist'])->middleware('auth');
});

// Admin Dashboard & CRUD Routes
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'index'])->name('dashboard');
    
    // User Management
    Route::post('/users/toggle-role/{user}', [AdminController::class, 'toggleUserRole'])->name('users.toggle-role');
    Route::delete('/users/{user}', [AdminController::class, 'deleteUser'])->name('users.delete');
    
    // Port Management
    Route::post('/ports', [AdminController::class, 'storePort'])->name('ports.store');
    Route::put('/ports/{port}', [AdminController::class, 'updatePort'])->name('ports.update');
    Route::delete('/ports/{port}', [AdminController::class, 'deletePort'])->name('ports.delete');
    
    // Article / CMS Management
    Route::post('/articles', [AdminController::class, 'storeArticle'])->name('articles.store');
    Route::put('/articles/{article}', [AdminController::class, 'updateArticle'])->name('articles.update');
    Route::delete('/articles/{article}', [AdminController::class, 'deleteArticle'])->name('articles.delete');
});

require __DIR__.'/auth.php';
