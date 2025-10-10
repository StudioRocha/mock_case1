<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * ログアウト機能のunitテスト
 * 
 * テストID: 3
 */
class LogoutUnitTest extends TestCase
{
    use RefreshDatabase;
    /**
     * ログアウトができることを検証する
     */
    public function test_logout_can_be_executed()
    {
        // テスト用ユーザーを作成
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => \Illuminate\Support\Facades\Hash::make('password123'),
            'email_verified_at' => now(),
        ]);

        // ユーザーをログイン状態にする
        Auth::login($user);

        // ログイン状態であることを確認
        $this->assertTrue(Auth::check(), 'ユーザーがログイン状態であることを確認');
        $this->assertEquals($user->id, Auth::id(), 'ログインしているユーザーのIDが正しいことを確認');

        // ログアウトを実行
        Auth::logout();

        // ログアウト状態であることを確認
        $this->assertFalse(Auth::check(), 'ユーザーがログアウト状態であることを確認');
        $this->assertNull(Auth::id(), 'ログインしているユーザーのIDがnullであることを確認');
        $this->assertNull(Auth::user(), 'ログインしているユーザーがnullであることを確認');
    }

    /**
     * 未ログイン状態でログアウトを実行してもエラーが発生しないことを検証する
     */
    public function test_logout_without_login_does_not_cause_error()
    {
        // 未ログイン状態であることを確認
        $this->assertFalse(Auth::check(), 'ユーザーが未ログイン状態であることを確認');

        // ログアウトを実行（エラーが発生しないことを確認）
        Auth::logout();

        // 依然として未ログイン状態であることを確認
        $this->assertFalse(Auth::check(), 'ユーザーが未ログイン状態であることを確認');
        $this->assertNull(Auth::id(), 'ログインしているユーザーのIDがnullであることを確認');
        $this->assertNull(Auth::user(), 'ログインしているユーザーがnullであることを確認');
    }

    /**
     * ログアウト後に認証情報がクリアされることを検証する
     */
    public function test_logout_clears_authentication()
    {
        // テスト用ユーザーを作成
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => \Illuminate\Support\Facades\Hash::make('password123'),
            'email_verified_at' => now(),
        ]);

        // ユーザーをログイン状態にする
        Auth::login($user);

        // 認証情報が設定されていることを確認
        $this->assertTrue(Auth::check(), 'ユーザーがログイン状態であることを確認');
        $this->assertEquals($user->id, Auth::id(), 'ログインしているユーザーのIDが正しいことを確認');

        // ログアウトを実行
        Auth::logout();

        // 認証情報がクリアされていることを確認
        $this->assertFalse(Auth::check(), 'ユーザーがログアウト状態であることを確認');
        $this->assertNull(Auth::id(), 'ログインしているユーザーのIDがnullであることを確認');
        $this->assertNull(Auth::user(), 'ログインしているユーザーがnullであることを確認');
    }
}
