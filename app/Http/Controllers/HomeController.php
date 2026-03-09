<?php

namespace App\Http\Controllers;

use App\Services\GvbService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class HomeController extends Controller
{
    public function index(GvbService $service){
        $data = $service->getDepartures('NL:S:30009902');
        return Inertia::render('Home', [
            'line' => $data[0]['line'] ?? "F9",
            'destination'=> $data[0]['destination'] ?? "Centraal Station",
            'timer' => $data[0]['expected'] ?? "21:30"
        ]);
    }
}
