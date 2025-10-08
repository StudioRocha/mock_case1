<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Support\Facades\Validator;
use Tests\Support\TestValidationRules;

//  * 会員登録バリデーションのunitテスト
//  * 
//  * テストID: 1

class RegisterValidationUnitTest extends TestCase
{
    /**
     * 名前未入力時にバリデーションメッセージが表示されることを検証する。
     */
    public function test_username_validation_required_message()
    {
        // バリデーションルール（設定ファイルから動的に取得）
        $rules = TestValidationRules::getRegisterRules();

        // カスタムメッセージ（設定ファイルから参照）
        $messages = TestValidationRules::getRegisterMessages();

        // テストデータ（設定ファイルから取得）
        $testData = [
            'username' => '', // 名前を空にする
            'email' => TestValidationRules::getTestData()['valid_email'],
            'password' => TestValidationRules::getTestData()['valid_password'],
            'password_confirmation' => TestValidationRules::getTestData()['valid_password'],
        ];

        // バリデーション実行
        $validator = Validator::make($testData, $rules, $messages);

        // バリデーションが失敗することを確認
        $this->assertTrue($validator->fails(), 'バリデーションが失敗することを確認');

        // エラーメッセージが正しく設定されていることを確認
        $errors = $validator->errors();
        $this->assertTrue($errors->has('username'), 'usernameフィールドにエラーがあることを確認');
        $this->assertEquals(TestValidationRules::getRegisterMessages()['username.required'], $errors->first('username'), '正しいエラーメッセージが表示されることを確認');

        // バリデーションルールが正しく設定されていることを確認
        $this->assertArrayHasKey('username', $rules);
        $this->assertContains('required', $rules['username']);
        $this->assertContains('string', $rules['username']);
        $this->assertContains('max:20', $rules['username']);

        // カスタムメッセージが正しく設定されていることを確認
        $this->assertArrayHasKey('username.required', $messages);
        $this->assertEquals(TestValidationRules::getRegisterMessages()['username.required'], $messages['username.required']);
    }

    /**
     * メールアドレス未入力時のバリデーションテスト
     */
    public function test_email_validation_required_message()
    {
        $rules = TestValidationRules::getRegisterRules();
        $messages = TestValidationRules::getRegisterMessages();

        $testData = [
            'username' => TestValidationRules::getTestData()['valid_username'],
            'email' => '', // メールアドレスを空にする
            'password' => TestValidationRules::getTestData()['valid_password'],
            'password_confirmation' => TestValidationRules::getTestData()['valid_password'],
        ];

        $validator = Validator::make($testData, $rules, $messages);

        $this->assertTrue($validator->fails());
        $errors = $validator->errors();
        $this->assertTrue($errors->has('email'));
        $this->assertEquals(TestValidationRules::getRegisterMessages()['email.required'], $errors->first('email'));
    }

    /**
     * パスワード未入力時のバリデーションテスト
     */
    public function test_password_validation_required_message()
    {
        $rules = TestValidationRules::getRegisterRules();
        $messages = TestValidationRules::getRegisterMessages();

        $testData = [
            'username' => TestValidationRules::getTestData()['valid_username'],
            'email' => TestValidationRules::getTestData()['valid_email'],
            'password' => '', // パスワードを空にする
            'password_confirmation' => TestValidationRules::getTestData()['valid_password'],
        ];

        $validator = Validator::make($testData, $rules, $messages);

        $this->assertTrue($validator->fails());
        $errors = $validator->errors();
        $this->assertTrue($errors->has('password'));
        $this->assertEquals(TestValidationRules::getRegisterMessages()['password.required'], $errors->first('password'));
    }



    /**
     * パスワードが7文字以下の場合、バリデーションメッセージが表示されることを検証する
     */
    public function test_password_min_length_validation_message()
    {
        $rules = TestValidationRules::getRegisterRules();
        $messages = TestValidationRules::getRegisterMessages();

        $testData = [
            'username' => TestValidationRules::getTestData()['valid_username'],
            'email' => TestValidationRules::getTestData()['valid_email'],
            'password' => TestValidationRules::getTestData()['short_password'], // 7文字（8文字未満）
            'password_confirmation' => TestValidationRules::getTestData()['short_password'],
        ];

        $validator = Validator::make($testData, $rules, $messages);

        $this->assertTrue($validator->fails());
        $errors = $validator->errors();
        $this->assertTrue($errors->has('password'));
        $this->assertEquals(TestValidationRules::getRegisterMessages()['password.min'], $errors->first('password'));
    }


     /**
     * パスワード確認不一致時のバリデーションテスト
     */
    public function test_password_confirmation_validation_message()
    {
        $rules = TestValidationRules::getRegisterRules();
        $messages = TestValidationRules::getRegisterMessages();

        $testData = [
            'username' => TestValidationRules::getTestData()['valid_username'],
            'email' => TestValidationRules::getTestData()['valid_email'],
            'password' => TestValidationRules::getTestData()['valid_password'],
            'password_confirmation' => TestValidationRules::getTestData()['different_password'], // 異なるパスワード
        ];

        $validator = Validator::make($testData, $rules, $messages);

        $this->assertTrue($validator->fails());
        $errors = $validator->errors();
        $this->assertTrue($errors->has('password'));
        $this->assertEquals(TestValidationRules::getRegisterMessages()['password.confirmed'], $errors->first('password'));
    }

    /**
     * 全ての項目が正しく入力されている場合、バリデーションが成功することを検証する
     */
    public function test_valid_registration_data_passes_validation()
    {
        $rules = TestValidationRules::getRegisterRules();
        $messages = TestValidationRules::getRegisterMessages();

        $testData = [
            'username' => TestValidationRules::getTestData()['valid_username'],
            'email' => TestValidationRules::getTestData()['valid_email'],
            'password' => TestValidationRules::getTestData()['valid_password'],
            'password_confirmation' => TestValidationRules::getTestData()['valid_password'],
        ];

        $validator = Validator::make($testData, $rules, $messages);

        // バリデーションが成功することを確認（uniqueルールを除く）
        $this->assertFalse($validator->fails(), '正常なデータでバリデーションが成功することを確認');
    }
}
