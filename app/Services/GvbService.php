<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Http;

class GvbService
{
    private string $baseUrl = 'https://www.gvb.nl/api/gvb-shared-services/travelinformation/api/v1';

    public function __construct(private RouteService $routeService) {}

    // ndsm NL:S:30009902
    public function getDepartures(string $stopCode, ?float $lat = null, ?float $lng = null): array
    {
        $response = Http::withHeaders([
            'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            'Referer'    => 'https://www.gvb.nl/',
            'Accept'     => 'application/json',
        ])->get("{$this->baseUrl}/DepartureTimes/GetVisits", [
            'stopCodes'       =>  $stopCode,
            'stopType'        => 'StopPlace',
            'previewInterval' => 60,
            'passageType'     => 'Departure',
        ]);

        $cycling = ($lat && $lng) ? $this->routeService->getCyclingTime(
            fromLat: (float) config('services.ors.lat'),
            fromLng: (float) config('services.ors.lng'),
            toLat: $lat,
            toLng: $lng,
        ) : null;

        return collect($response->json())->map(function ($departure) use ($cycling) {
            $expected = Carbon::parse($departure['departureGroup']['expectedDateTime'])->setTimezone('Europe/Amsterdam');
            $minutesUntilDeparture = now('Europe/Amsterdam')->diffInMinutes($expected, false);

            return [
                'line'             => $departure['publishedLineNumber'],
                'destination'      => $departure['destinationName'],
                'planned'          => Carbon::parse($departure['departureGroup']['aimedDateTime'])->setTimezone('Europe/Amsterdam')->format('H:i'),
                'expected'         => $expected->format('H:i'),
                'delay'            => $departure['departureGroup']['delay'],
                'cancelled'        => $departure['cancelled'],
                'disruptions'      => collect($departure['disruptions'])->pluck('message')->toArray(),
                'cycling'          => $cycling ?? null,
                'minutes_until'    => round($minutesUntilDeparture, 1),
                'can_make_it'      => $cycling ? $minutesUntilDeparture >= $cycling['duration_minutes'] : null,
            ];
        })->toArray();
    }

    public function search(string $query)
    {
        $headers = [
            'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            'Referer'    => 'https://www.gvb.nl',
            'Accept'     => 'application/json',
        ];

        [$queryResult, $linesResult] = Http::pool(fn($pool) => [
            $pool->withHeaders($headers)->get("{$this->baseUrl}/DepartureTimes/GetStopsByQuery?stopType=StopPlace&inService=true&searchString={$query}"),
            $pool->withHeaders($headers)->get("{$this->baseUrl}/Timetable/GetLinesByOwner?dataOwnerCode=GVB"),
        ]);

        $stops = collect($queryResult->json())->map(fn($result) => [
            'type'     => 'stop',
            'category' => $result['stopCategories'][0] ?? null,
            'name'     => $result['stopName'],
            'code'     => $result['stopCode'],
            'place'    => $result['place'],
        ]);

        $lines = collect($linesResult->json())
            ->filter(fn($line) => str_contains(
                strtolower($line['linePublicName']),
                strtolower($query)
            ))
            ->map(fn($line) => [
                'type'       => 'line',
                'category'   => $line['modalities'][0]['transportType'] ?? null,
                'name'       => $line['linePublicName'],
                'code'       => $line['linePlanningNumber'],
                'place'      => collect($line['directionsInformation'])
                    ->map(fn($d) => $d['destination'])
                    ->join(' / '),
            ]);
        return $lines->merge($stops)->values();
    }

    public function getStopsByLine(string $lineCode): array
    {
        $response = Http::withHeaders([
            'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            'Referer'    => 'https://www.gvb.nl',
            'Accept'     => 'application/json',
        ])->get("{$this->baseUrl}/Timetable/GetLineByNumber", [
            'dataOwnerCode'      => 'GVB',
            'linePlanningNumber' => $lineCode,
            'dateTime'           => now()->toIso8601String(),
        ]);

        return collect($response->json()['routes'] ?? [])
            ->flatMap(fn($route) => $route['stops'])
            ->filter(fn($stop) => !empty($stop['stopCode']))
            ->unique('stopCode')
            ->map(fn($stop) => [
                'name'     => $stop['name'],
                'code'     => $stop['stopCode'],
                'category' => $stop['stopCategories'][0] ?? null,
            ])
            ->values()
            ->toArray();
    }
    
}
