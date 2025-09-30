<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\View;

class RegisterController extends Controller
{
    public function show()
    {
        return View::make('auth.register');
    }
}


