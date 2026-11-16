<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\FlightController;
Route::middleware(['auth:sanctum','throttle:api'])->prefix('v1')->group(function () {
    Route::post('/sessions', [FlightController::class, 'session']);
    Route::post('/strategies', [FlightController::class, 'strategy']);
    Route::post('/simulations/batch', [FlightController::class, 'simulations']);
    Route::post('/jumps', [FlightController::class, 'jump']);
    Route::get('/strategies/best', [FlightController::class, 'best']);
    Route::get('/stats', [FlightController::class, 'stats']);
});
