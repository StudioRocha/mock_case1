<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;

class LoginController extends Controller
{
    public function show()
    {
        // 既にログインしている場合はホームにリダイレクト
        if (Auth::check()) {
            $user = Auth::user();
            
            // メール認証が完了していない場合は誘導画面へ
            if (!$user->email_verified_at) {
                return redirect()->route('email.guide');
            }
            
            return redirect()->intended('/');
        }
        
        return View::make('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);
        
        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            
            // メール認証が完了していない場合は必ず誘導画面へ
            if (!$user->email_verified_at) {
                Auth::logout(); // ログイン状態を解除
                return redirect()->route('email.guide')->with('error', 'メール認証を完了してからログインしてください。');
            }
            
            return redirect()->intended('/');
        }
        
        return back()->withErrors([
            'email' => 'メールアドレスまたはパスワードが正しくありません。',
        ]);
    }
}


