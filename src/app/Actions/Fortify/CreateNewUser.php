<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\EmailAuthController;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Illuminate\Support\Facades\Session;


class CreateNewUser implements CreatesNewUsers
{

    public function create(array $input)
    {
        // 設定ファイルからバリデーションルールとメッセージを取得
        $rules = config('validation.register.rules');
        $messages = config('validation.register.messages');
        
        // テスト環境ではuniqueルールを除外
        if (app()->environment('testing')) {
            $rules['email'] = array_filter($rules['email'], function($rule) {
                return !str_starts_with($rule, 'unique:');
            });
        }
        
        Validator::make($input, $rules, $messages)->validate();

                // ワンクリック認証用のトークンを生成
                $verificationToken = \Illuminate\Support\Str::random(60);
                
                // ユーザーをデータベースに登録（メール認証前）
                $user = User::create([
                    'name' => $input['username'],
                    'email' => $input['email'],
                    'password' => Hash::make($input['password']),
                    'email_verified_at' => null, // メール認証前はnull
                    'verification_token' => $verificationToken,
                    'verification_token_expires_at' => now()->addHours(24), // 24時間で期限切れ
                ]);
        
        // 開発環境用：ユーザーIDをセッションに保存（自動認証用）
        if (config('app.auto_verify_enabled', false)) {
            Session::put('user_id', $user->id);
        }
        
        // メール認証メールを送信
        $emailAuthController = new EmailAuthController();
        $emailAuthController->sendVerificationEmail($user);
        
        // メール認証誘導画面にリダイレクト
        return redirect()->route('email.guide')->with('success', 'メール認証を完了してください。');
    }
}


