<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PlanningController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        return view('hub.planning.index', compact('user'));
    }


}
