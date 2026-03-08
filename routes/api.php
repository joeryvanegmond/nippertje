<?php

use App\Http\Controllers\Api\V1\TripUpdateController;
use Illuminate\Support\Facades\Route;

Route::get('/v1/departures', [TripUpdateController::class, 'departures']);