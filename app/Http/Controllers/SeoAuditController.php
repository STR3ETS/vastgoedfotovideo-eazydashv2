<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SeoAuditController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        return view('hub.seo.index', compact('user'));
    }

}
