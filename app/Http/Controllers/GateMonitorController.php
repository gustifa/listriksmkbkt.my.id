<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class GateMonitorController extends Controller
{
    public function index()
    {
        return view('gate_monitor');
    }
}
