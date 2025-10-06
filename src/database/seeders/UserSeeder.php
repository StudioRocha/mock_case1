<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        // テスト用の固定ユーザーを作成
        User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'テストユーザー',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        // 出品者用のテストユーザー
        User::firstOrCreate(
            ['email' => 'seller@example.com'],
            [
                'name' => '出品者ユーザー',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        // 購入者用のテストユーザー
        User::firstOrCreate(
            ['email' => 'buyer@example.com'],
            [
                'name' => '購入者ユーザー',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        // ダミーユーザーを10人作成
        User::factory(10)->create();

        $this->command->info('ユーザーダミーデータを作成しました。');
        $this->command->info('固定ユーザー3人 + ランダムユーザー10人 = 合計13人のユーザーを作成しました。');
    }
}
