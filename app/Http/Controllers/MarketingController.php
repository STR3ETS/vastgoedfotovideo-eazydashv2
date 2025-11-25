<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class MarketingController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        return view('hub.marketing.index', compact('user'));
    }
}
