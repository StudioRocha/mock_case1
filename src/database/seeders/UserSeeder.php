<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Profile;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        // 環境変数でcountが指定されている場合は、createUsersメソッドを呼び出し
        $count = env('USER_COUNT');
        if ($count) {
            $this->createUsers((int)$count);
            return;
        }

        // 通常のSeeder実行（固定ユーザー1人 + ランダムユーザー10人）
        // テスト用の固定ユーザーを作成
        $fixedUser = User::firstOrCreate(
            ['email' => 'dev@example.com'],
            [
                'name' => 'Developer',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        // 固定ユーザーのプロフィールを作成（存在しない場合のみ）
        Profile::firstOrCreate(
            ['user_id' => $fixedUser->id],
            [
                'user_id' => $fixedUser->id,
                'postal_codes' => '123-4567',
                'addresses' => '東京都渋谷区',
                'building_names' => 'テストビル 101',
                'usernames' => 'Developer',
                'avatar_paths' => null,
            ]
        );

        // ダミーユーザーを10人作成（既存のユーザー数をチェック）
        $existingUserCount = User::count();
        if ($existingUserCount < 11) { // 固定ユーザー1人 + ランダムユーザー10人 = 11人
            $neededUsers = 11 - $existingUserCount;
            
            // ユーザーとプロフィールを同時に作成
            for ($i = 0; $i < $neededUsers; $i++) {
                $user = User::factory()->create();
                Profile::factory()->create(['user_id' => $user->id]);
            }
            
            $this->command->info("追加で{$neededUsers}人のユーザーとプロフィールを作成しました。");
        } else {
            $this->command->info('既に十分なユーザーが存在します。');
        }

        $this->command->info('ユーザーダミーデータを作成しました。');
        $this->command->info('固定ユーザー1人 + ランダムユーザー10人 = 合計11人のユーザーを作成しました。');
    }

    /**
     * 指定した人数のユーザーを作成するメソッド
     * 使用方法: USER_COUNT=5 php artisan db:seed --class=UserSeeder
     */
    public function createUsers($count = 10)
    {
        $this->command->info("=== ユーザー作成開始 ===");
        $this->command->info("作成予定人数: {$count}人");

        // 既存のユーザー数をチェック
        $existingCount = User::count();
        $this->command->info("既存ユーザー数: {$existingCount}人");

        // 指定した人数のユーザーとプロフィールを作成
        for ($i = 0; $i < $count; $i++) {
            $user = User::factory()->create();
            Profile::factory()->create(['user_id' => $user->id]);
        }

        $newCount = User::count();
        $createdCount = $newCount - $existingCount;
        
        $this->command->info("新規作成: {$createdCount}人");
        $this->command->info("現在の総ユーザー数: {$newCount}人");
        $this->command->info("=== ユーザー作成完了 ===");
    }
}
