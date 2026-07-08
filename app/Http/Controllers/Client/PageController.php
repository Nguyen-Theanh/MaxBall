<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;

class PageController extends Controller
{
    public function about()
    {
        return view('client.pages.about');
    }
}
