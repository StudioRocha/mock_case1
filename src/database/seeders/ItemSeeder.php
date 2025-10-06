<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Item;
use App\Models\User;
use App\Models\Category;

class ItemSeeder extends Seeder
{
    public function run()
    {
        // 既存のユーザーを取得（なければ作成）
        $users = User::all();
        if ($users->isEmpty()) {
            // テスト用ユーザーを作成
            $users = User::factory(5)->create();
        }

        // カテゴリーを取得
        $categories = Category::all();
        
        if ($categories->isEmpty()) {
            $this->command->error('カテゴリーが存在しません。先にCategorySeederを実行してください。');
            return;
        }

        // 商品データ一覧の10個の商品を作成
        for ($i = 0; $i < 10; $i++) {
            $item = Item::factory()->create([
                'user_id' => $users->random()->id,
            ]);

            // ランダムに1-3個のカテゴリーを割り当て
            $randomCategories = $categories->random(rand(1, 3));
            $item->categories()->attach($randomCategories->pluck('id'));
        }

        // 追加でランダムな商品を20個作成（より多くのテストデータ用）
        for ($i = 0; $i < 20; $i++) {
            $item = Item::factory()->create([
                'user_id' => $users->random()->id,
            ]);

            // ランダムに1-3個のカテゴリーを割り当て
            $randomCategories = $categories->random(rand(1, 3));
            $item->categories()->attach($randomCategories->pluck('id'));
        }

        $this->command->info('商品ダミーデータを作成しました。');
        $this->command->info('商品データ一覧の10個 + ランダム商品20個 = 合計30個の商品を作成しました。');
    }
}
