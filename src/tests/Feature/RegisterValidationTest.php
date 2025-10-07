<?php

namespace Tests\Feature;

use Tests\TestCase;
use Tests\Support\TestValidationRules;

class RegisterValidationTest extends TestCase
{
    /**
     * 名前未入力時にバリデーションメッセージが表示されることを検証する。
     * 手順:
     *  1) /register を開く
     *  2) 名前を空にして他の項目を送信
     *  3) 登録ボタン押下時にエラーメッセージが表示される
     */
    public function test_register_requires_username_shows_message()
    {
        // 1. 会員登録ページを開く
        $getResponse = $this->get('/register');
        $getResponse->assertStatus(200);

        // 2. 名前を空にして他の必要項目を送信
        // email を空にすることで unique ルール評価を回避し、DB 依存をなくす
        $postData = [
            'username' => '',
            'email' => '',
            'password' => TestValidationRules::getTestData()['valid_password'],
            'password_confirmation' => TestValidationRules::getTestData()['valid_password'],
        ];

        // 3. 登録ボタンを押す → バリデーションエラーで /register に戻る想定
        $response = $this->from('/register')->post('/register', $postData);

        $response->assertStatus(302);
        $response->assertRedirect('/register');

        // 期待挙動: 「お名前を入力してください」というメッセージ
        $response->assertSessionHasErrors([
            'username' => TestValidationRules::getRegisterMessages()['username.required'],
        ]);
    }
    /**
     * メールアドレス未入力時にバリデーションメッセージが表示されることを検証する
     * 手順:
     *  1) 会員登録ページを開く
     *  2) メールアドレスを入力せずに他の必要項目を入力する
     *  3) 登録ボタンを押す
     * 
     * 期待挙動: 「メールアドレスを入力してください」というバリデーションメッセージが表示される
     */
    public function test_register_requires_email_shows_message()
    {
        // 1. 会員登録ページを開く
        $getResponse = $this->get('/register');
        $getResponse->assertStatus(200);

        // 2. メールアドレスを空にして他の必要項目を送信
        $postData = [
            'username' => TestValidationRules::getTestData()['valid_username'],
            'email' => '', // メールアドレスを空にする
            'password' => TestValidationRules::getTestData()['valid_password'],
            'password_confirmation' => TestValidationRules::getTestData()['valid_password'],
        ];

        // 3. 登録ボタンを押す → バリデーションエラーで /register に戻る想定
        $response = $this->from('/register')->post('/register', $postData);

        $response->assertStatus(302);
        $response->assertRedirect('/register');

        // 期待挙動: 「メールアドレスを入力してください」というメッセージ
        $response->assertSessionHasErrors([
            'email' => TestValidationRules::getRegisterMessages()['email.required'],
        ]);
    }

}
