<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function store(LoginRequest $request)
    {
        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials, false)) {
            $request->session()->regenerate();
            $user = Auth::user();
            if ($user && !$user->profile) {
                return redirect('/mypage/profile');
            }
            return redirect()->intended('/');
        }

        return back()->withErrors(['email' => '認証に失敗しました'])->withInput();
    }
}


