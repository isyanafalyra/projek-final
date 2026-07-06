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

use App\Services\RiskIntelligenceService;

Route::get('/test-api', function (RiskIntelligenceService $service) {
    return response()->json([
        'weather' => $service->getWeatherData(51.8850, 4.2867), // Rotterdam
        'macro' => $service->getMacroData('NL'),
        'country' => $service->getCountryDetails('NL'),
        'rates' => $service->getExchangeRates('USD'),
        'news' => $service->getNewsData('logistik')
    ]);
});

require __DIR__.'/auth.php';
