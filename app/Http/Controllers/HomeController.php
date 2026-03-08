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
            'line' => $data[0]['line'],
            'destination'=> $data[0]['destination'],
            'timer' => $data[0]['expected']
        ]);
    }
}
