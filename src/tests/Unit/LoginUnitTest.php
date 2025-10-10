<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Support\Facades\Validator;
use Tests\Support\TestValidationRules;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * ログイン機能バリデーションのunitテスト
 * 
 * テストID: 2
 */
class LoginUnitTest extends TestCase
{
    use RefreshDatabase;
    /**
     * メールアドレス未入力時にバリデーションメッセージが表示されることを検証する
     */
    public function test_email_validation_required_message()
    {
        // バリデーションルール（設定ファイルから動的に取得）
        $rules = TestValidationRules::getLoginRules();

        // カスタムメッセージ（設定ファイルから参照）
        $messages = TestValidationRules::getLoginMessages();

        // テストデータ（設定ファイルから取得）
        $testData = [
            'email' => '', // メールアドレスを空にする
            'password' => TestValidationRules::getLoginTestData()['valid_password'],
        ];

        // バリデーション実行
        $validator = Validator::make($testData, $rules, $messages);

        // バリデーションが失敗することを確認
        $this->assertTrue($validator->fails(), 'バリデーションが失敗することを確認');

        // エラーメッセージが正しく設定されていることを確認
        $errors = $validator->errors();
        $this->assertTrue($errors->has('email'), 'emailフィールドにエラーがあることを確認');
        $this->assertEquals(TestValidationRules::getLoginMessages()['email.required'], $errors->first('email'), '正しいエラーメッセージが表示されることを確認');

        // バリデーションルールが正しく設定されていることを確認
        $this->assertArrayHasKey('email', $rules);
        $this->assertContains('required', $rules['email']);
        $this->assertContains('email', $rules['email']);

        // カスタムメッセージが正しく設定されていることを確認
        $this->assertArrayHasKey('email.required', $messages);
        $this->assertEquals(TestValidationRules::getLoginMessages()['email.required'], $messages['email.required']);
    }

    /**
     * パスワード未入力時にバリデーションメッセージが表示されることを検証する
     */
    public function test_password_validation_required_message()
    {
        $rules = TestValidationRules::getLoginRules();
        $messages = TestValidationRules::getLoginMessages();

        $testData = [
            'email' => TestValidationRules::getLoginTestData()['valid_email'],
            'password' => '', // パスワードを空にする
        ];

        $validator = Validator::make($testData, $rules, $messages);

        $this->assertTrue($validator->fails());
        $errors = $validator->errors();
        $this->assertTrue($errors->has('password'));
        $this->assertEquals(TestValidationRules::getLoginMessages()['password.required'], $errors->first('password'));
    }

    /**
     * メールアドレス形式が不正な場合、バリデーションメッセージが表示されることを検証する
     */
    public function test_email_format_validation_message()
    {
        $rules = TestValidationRules::getLoginRules();
        $messages = TestValidationRules::getLoginMessages();

        $testData = [
            'email' => TestValidationRules::getLoginTestData()['invalid_format_email'], // 不正なメール形式
            'password' => TestValidationRules::getLoginTestData()['valid_password'],
        ];

        $validator = Validator::make($testData, $rules, $messages);

        $this->assertTrue($validator->fails());
        $errors = $validator->errors();
        $this->assertTrue($errors->has('email'));
        $this->assertEquals(TestValidationRules::getLoginMessages()['email.email'], $errors->first('email'));
    }

    /**
     * 全ての項目が正しく入力されている場合、バリデーションが成功することを検証する
     */
    public function test_valid_login_data_passes_validation()
    {
        $rules = TestValidationRules::getLoginRules();
        $messages = TestValidationRules::getLoginMessages();

        $testData = [
            'email' => TestValidationRules::getLoginTestData()['valid_email'],
            'password' => TestValidationRules::getLoginTestData()['valid_password'],
        ];

        $validator = Validator::make($testData, $rules, $messages);

        // バリデーションが成功することを確認
        $this->assertFalse($validator->fails(), '正常なデータでバリデーションが成功することを確認');
    }

    /**
     * 存在しないメールアドレスでのログイン試行時のバリデーションテスト
     * 注意: このテストは認証ロジックのテストであり、実際の認証処理はFeatureテストで行う
     */
    public function test_invalid_email_validation_message()
    {
        $rules = TestValidationRules::getLoginRules();
        $messages = TestValidationRules::getLoginMessages();

        $testData = [
            'email' => TestValidationRules::getLoginTestData()['invalid_email'], // 存在しないメールアドレス
            'password' => TestValidationRules::getLoginTestData()['valid_password'],
        ];

        $validator = Validator::make($testData, $rules, $messages);

        // バリデーション自体は成功する（メール形式は正しいため）
        $this->assertFalse($validator->fails(), 'メール形式が正しければバリデーションは成功する');

        // 実際の認証失敗はLoginControllerで処理される
        // この部分はFeatureテストで検証する
    }

    /**
     * 間違ったパスワードでのログイン試行時のバリデーションテスト
     * 注意: このテストは認証ロジックのテストであり、実際の認証処理はFeatureテストで行う
     */
    public function test_invalid_password_validation_message()
    {
        $rules = TestValidationRules::getLoginRules();
        $messages = TestValidationRules::getLoginMessages();

        $testData = [
            'email' => TestValidationRules::getLoginTestData()['valid_email'],
            'password' => TestValidationRules::getLoginTestData()['invalid_password'], // 間違ったパスワード
        ];

        $validator = Validator::make($testData, $rules, $messages);

        // バリデーション自体は成功する（パスワードは入力されているため）
        $this->assertFalse($validator->fails(), 'パスワードが入力されていればバリデーションは成功する');

        // 実際の認証失敗はLoginControllerで処理される
        // この部分はFeatureテストで検証する
    }
}
