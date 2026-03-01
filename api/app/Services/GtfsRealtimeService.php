<?php

namespace App\Services;

use Transit_realtime\FeedMessage;

class GtfsRealtimeService
{
    public function getTripUpdates(): array
    {
        $data = file_get_contents('https://gtfs.ovapi.nl/nl/tripUpdates.pb');

        $feed = new FeedMessage();
        $feed->mergeFromString($data);

        $updates = [];
        foreach ($feed->getEntity() as $entity) {
            if ($entity->hasTripUpdate()) {
                $trip = $entity->getTripUpdate()->getTrip();

                $stopTimes = [];
                foreach ($entity->getTripUpdate()->getStopTimeUpdate() as $stopTime) {
                    $stopTimes[] = [
                        'stop_id'        => $stopTime->getStopId(),
                        'arrival'        => $stopTime->hasArrival() ? $stopTime->getArrival()->getTime() : null,
                        'departure'      => $stopTime->hasDeparture() ? $stopTime->getDeparture()->getTime() : null,
                    ];
                }

                $updates[] = [
                    'trip_id'    => $trip->getTripId(),
                    'route_id'   => $trip->getRouteId(),
                    'direction'  => $trip->getDirectionId(),
                    'stop_times' => $stopTimes,
                ];
            }
        }

        return $updates;
    }


    public function getStopUpdates(string $stopId): array
    {
        $data = file_get_contents('https://gtfs.ovapi.nl/nl/tripUpdates.pb');
        $feed = new FeedMessage();
        $feed->mergeFromString($data);

        $allStopIds = [];
        foreach ($feed->getEntity() as $entity) {
            if ($entity->hasTripUpdate()) {
                foreach ($entity->getTripUpdate()->getStopTimeUpdate() as $stopTime) {
                    $id = $stopTime->getStopId();
                    if (str_contains($id, '3152')) {
                        $allStopIds[] = $id;
                    }
                }
            }
        }

        return array_unique($allStopIds);
    }
}
