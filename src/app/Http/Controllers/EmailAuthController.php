<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\EmailVerificationRequest;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

class EmailAuthController extends Controller
{
    /**
     * メール認証誘導画面を表示
     */
    public function guide()
    {
        return view('email-auth-guide');
    }
    
    /**
     * メール認証画面を表示
     */
    public function show()
    {
        return view('email-auth-verify');
    }
    
    /**
     * メール認証を実行
     */
    public function verify(EmailVerificationRequest $request)
    {
        // セッションから認証コードを取得（一時ユーザーIDを使用）
        $tempUserId = Session::get('temp_user_id');
        $storedCode = Session::get("email_verification_code_{$tempUserId}");
        
        if (!$storedCode || $storedCode !== $request->verification_code) {
            return redirect()->back()->withErrors(['verification_code' => '認証コードが正しくありません。']);
        }
        
        // セッションからユーザー情報を取得してDBに登録
        $userData = Session::get('pending_user_data');
        
        if (!$userData) {
            return redirect()->route('register')->with('error', 'セッションが期限切れです。再度登録してください。');
        }
        
        // ユーザーをDBに登録
        $user = User::create([
            'name' => $userData['name'],
            'email' => $userData['email'],
            'password' => $userData['password'],
            'email_verified_at' => now(), // メール認証完了
        ]);
        
        // セッションをクリア
        Session::forget('pending_user_data');
        Session::forget('temp_user_id');
        Session::forget("email_verification_code_{$tempUserId}");
        
        // ログイン状態にする
        Auth::login($user);
        
        return redirect()->route('profile.edit')->with('success', 'メール認証が完了しました。');
    }
    
    /**
     * 認証メールを再送
     */
    public function resend()
    {
        // 一時ユーザーIDを取得
        $tempUserId = Session::get('temp_user_id');
        
        if (!$tempUserId) {
            return redirect()->route('register')->with('error', 'セッションが期限切れです。再度登録してください。');
        }
        
        // 6桁の認証コードを生成
        $verificationCode = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        
        // セッションに保存
        Session::put("email_verification_code_{$tempUserId}", $verificationCode);
        
        // ユーザー情報を取得（メール送信用）
        $userData = Session::get('pending_user_data');
        $tempUser = new User($userData);
        $tempUser->id = $tempUserId;
        
        // メール送信
        Mail::send('emails.verification', [
            'user' => $tempUser,
            'verificationCode' => $verificationCode
        ], function ($message) use ($userData) {
            $message->to($userData['email'])
                   ->subject('メール認証コード');
        });
        
        return redirect()->route('email.guide')->with('success', '認証メールを再送しました。');
    }
    
    /**
     * 新規登録時に認証メールを送信
     */
    public function sendVerificationEmail(User $user)
    {
        // 一時ユーザーIDをセッションに保存
        Session::put('temp_user_id', $user->id);
        
        // 6桁の認証コードを生成
        $verificationCode = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        
        // セッションに保存
        Session::put("email_verification_code_{$user->id}", $verificationCode);
        
        // メール送信
        Mail::send('emails.verification', [
            'user' => $user,
            'verificationCode' => $verificationCode
        ], function ($message) use ($user) {
            $message->to($user->email)
                   ->subject('メール認証コード');
        });
    }
}