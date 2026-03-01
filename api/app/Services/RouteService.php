<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class RouteService
{
    public function getCyclingTime(float $fromLat, float $fromLng, float $toLat, float $toLng): array
    {
        $response = Http::withHeaders([
            'Authorization' => config('services.ors.key'),
            'Accept'        => 'application/json',
        ])->post('https://api.openrouteservice.org/v2/directions/cycling-regular', [
            'coordinates' => [
                [$fromLng, $fromLat],
                [$toLng, $toLat],
            ],
        ]);

        $data = $response->json();
        $summary = $data['routes'][0]['summary'];

        return [
            'duration_minutes' => round($summary['duration'] / 60),
            'distance_km'      => round($summary['distance'] / 1000, 1),
        ];
    }
}