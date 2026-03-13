<?php

namespace App\Http\Controllers;

use App\Services\GvbService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class HomeController extends Controller
{
    public function index(Request $request, GvbService $service)
    {
        $stopCode = $request->input('stopCode');
        if($stopCode){
            $lineName = $request->input('lineName');
    
            $data = $service->getDepartures($stopCode);
    
            if ($lineName) {
                $destination = $request->input('destination');
    
                $departure = collect($data)->first(function ($d) use ($lineName, $destination) {
                    if ($destination) {
                        return $d['line'] === $lineName && $d['destination'] === $destination;
                    }
                    return $d['line'] === $lineName;  // ← zonder destination gewoon eerste van die lijn
                });
            }
        }

        $departure = $departure ?? $data[0] ?? null;

        return Inertia::render('Home', [
            'line'        => $departure['line']        ?? '',
            'destination' => $departure['destination'] ?? '',
            'timer'       => $departure['expected']    ?? '',
            'stopCode'    => $stopCode,
        ]);
    }

    public function search(Request $request, GvbService $service)
    {
        $base = [
            'line'           => '',
            'destination'    => '',
            'timer'          => '',
            'results'        => [],
            'lineStops'      => [],
            'stopDepartures' => [],
        ];

        if ($request->has('lineCode')) {
            return Inertia::render('Home', array_merge($base, [
                'lineStops' => $service->getStopsByLine($request->input('lineCode')),
            ]));
        }

        if ($request->has('stopCode')) {
            $departures = collect($service->getDepartures($request->input('stopCode')))
                ->unique(fn($d) => $d['line'] . '|' . $d['destination'])
                ->values();

            return Inertia::render('Home', array_merge($base, [
                'stopDepartures' => $departures,
            ]));
        }

        $q = $request->input('q', '');

        return Inertia::render('Home', array_merge($base, [
            'results' => strlen($q) >= 1 ? $service->search($q) : [],
        ]));
    }
}
