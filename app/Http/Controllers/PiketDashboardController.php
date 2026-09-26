<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PiketDashboardController extends Controller
{
    public function dashboard(){
    return view('piket.dashboard');
    }
}
