<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\View;

class LoginController extends Controller
{
    public function show()
    {
        return View::make('auth.login');
    }
}


