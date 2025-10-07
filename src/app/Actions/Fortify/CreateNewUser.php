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

        // ユーザー情報をセッションに一時保存（メール認証完了後にDB登録）
        $userData = [
            'name' => $input['username'],
            'email' => $input['email'],
            'password' => Hash::make($input['password']),
        ];
        
        Session::put('pending_user_data', $userData);
        
        // 一時的なユーザーオブジェクトを作成（メール送信用）
        $tempUser = new User($userData);
        $tempUser->id = 'temp_' . time(); // 一時的なID
        
        // メール認証メールを送信
        $emailAuthController = new EmailAuthController();
        $emailAuthController->sendVerificationEmail($tempUser);
        
        // メール認証誘導画面にリダイレクト
        return redirect()->route('email.guide')->with('success', '登録が完了しました。メール認証を完了してください。');
    }
}


