<?php

namespace App\Http\Controllers;


use App\Http\Requests\EmailVerificationRequest;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;


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
     * 自動認証機能（開発環境のみ）
     */
    public function autoVerify()
    {
        // 環境変数で制御
        if (!config('app.auto_verify_enabled', false)) {
            abort(404);
        }
        
        // セッションから最新のユーザーIDを取得
        $userId = Session::get('user_id');
        
        if ($userId) {
            $user = User::find($userId);
            
            if ($user && !$user->email_verified_at && $user->verification_token) {
                // ワンクリック認証を自動実行
                return $this->verifyToken($user->verification_token);
            }
        }
        
        return redirect()->route('email.guide')->with('error', '認証対象のユーザーが見つかりません');
    }

    /**
     * メール認証画面を表示
     */
    public function show()
    {
        return view('email-auth-verify');
    }
    
    /**
     * ワンクリック認証（トークンベース）
     */
    public function verifyToken($token)
    {
        $user = User::where('verification_token', $token)->first();
        
        if (!$user) {
            return redirect()->route('email.guide')->with('error', '無効な認証リンクです。');
        }
        
        // トークンの有効期限チェック
        if ($user->verification_token_expires_at && $user->verification_token_expires_at < now()) {
            return redirect()->route('email.guide')->with('error', '認証リンクの有効期限が切れています。');
        }
        
        // 既に認証済みかチェック
        if ($user->email_verified_at) {
            Auth::login($user);
            return redirect()->route('profile.edit')->with('success', '既にメール認証が完了しています。');
        }
        
        // ワンクリック認証完了
        $user->update([
            'email_verified_at' => now(),
            'verification_token' => null, // トークンを無効化
            'verification_token_expires_at' => null,
        ]);
        
        // 自動ログイン
        Auth::login($user);
        
        return redirect()->route('profile.edit')->with('success', 'メール認証が完了しました！');
    }

    /**
     * メール認証を実行（従来の認証コード方式）
     */
    public function verify(EmailVerificationRequest $request)
    {
        // セッションから認証コードを取得（一時ユーザーIDを使用）
        $tempUserId = Session::get('temp_user_id');
        $storedCode = Session::get("email_verification_code_{$tempUserId}");
        
        if (!$storedCode || $storedCode !== $request->verification_code) {
            return redirect()->back()->withErrors(['verification_code' => '認証コードが正しくありません。']);
        }
        
        // セッションからユーザーIDを取得してDBのユーザーを更新
        $userId = Session::get('user_id');
        
        if (!$userId) {
            return redirect()->route('register')->with('error', 'セッションが期限切れです。再度登録してください。');
        }
        
        // ユーザーのメール認証を完了
        $user = User::find($userId);
        if ($user) {
            $user->update(['email_verified_at' => now()]);
            
            // ログイン状態にする
            Auth::login($user);
            
            // セッションをクリア
            Session::forget('user_id');
            Session::forget('temp_user_id');
            Session::forget("email_verification_code_{$tempUserId}");
            
            return redirect()->route('profile.edit')->with('success', 'メール認証が完了しました。');
        }
        
        return redirect()->route('register')->with('error', 'ユーザーが見つかりません。再度登録してください。');
    }
    
    /**
     * 認証メールを再送（ワンクリック認証対応）
     */
    public function resend()
    {
        // セッションからユーザーIDを取得
        $userId = Session::get('user_id');
        
        if (!$userId) {
            return redirect()->route('register')->with('error', 'セッションが期限切れです。再度登録してください。');
        }
        
        // ユーザーを取得
        $user = User::find($userId);
        
        if (!$user) {
            return redirect()->route('register')->with('error', 'ユーザーが見つかりません。再度登録してください。');
        }
        
        // 既に認証済みの場合はプロフィール画面にリダイレクト
        if ($user->email_verified_at) {
            Auth::login($user);
            return redirect()->route('profile.edit')->with('success', '既にメール認証が完了しています。');
        }
        
        // 新しい認証トークンを生成
        $verificationToken = \Illuminate\Support\Str::random(60);
        
        // ユーザーの認証トークンを更新
        $user->update([
            'verification_token' => $verificationToken,
            'verification_token_expires_at' => now()->addHours(24),
        ]);
        
        // ワンクリック認証用のURLを生成
        $verificationUrl = route('email.verify.token', ['token' => $verificationToken]);
        
        // メール送信
        Mail::send('emails.verification', [
            'user' => $user,
            'verificationUrl' => $verificationUrl
        ], function ($message) use ($user) {
            $message->to($user->email)
                   ->subject('メール認証を完了してください');
        });
        
        return redirect()->route('email.guide')->with('success', '認証メールを再送しました。');
    }
    
    /**
     * 新規登録時に認証メールを送信（ワンクリック認証）
     */
    public function sendVerificationEmail(User $user)
    {
        // ワンクリック認証用のURLを生成
        $verificationUrl = route('email.verify.token', ['token' => $user->verification_token]);
        
        // メール送信
        Mail::send('emails.verification', [
            'user' => $user,
            'verificationUrl' => $verificationUrl
        ], function ($message) use ($user) {
            $message->to($user->email)
                   ->subject('メール認証を完了してください');
        });
    }
}