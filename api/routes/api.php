<?php

use App\Http\Controllers\Api\V1\TripUpdateController;
use Illuminate\Support\Facades\Route;

Route::get('/v1/trip-updates', [TripUpdateController::class, 'index']);
Route::get('/v1/trip-updates', [TripUpdateController::class, 'index']);
Route::get('/v1/ndsm/departures', [TripUpdateController::class, 'ndsm']);