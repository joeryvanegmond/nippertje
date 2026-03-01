<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\GtfsRealtimeService;
use App\Services\GvbService;
use Illuminate\Http\Request;

class TripUpdateController extends Controller
{
    public function index(GtfsRealtimeService $service)
    {
        return response()->json($service->getTripUpdates());
    }

    public function stop(GtfsRealtimeService $service, string $stopId)
    {
        return response()->json($service->getStopUpdates($stopId));
    }

    public function ndsm(GvbService $service)
    {
        return response()->json($service->getNdsmRealtime());
    }
}
