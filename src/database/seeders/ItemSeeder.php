<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Item;
use App\Models\User;
use App\Models\Category;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;

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

        // 商品データ一覧の10個の商品を固定で作成
        $predefinedItemsData = [
            [
                'item_names' => '腕時計',
                'item_prices' => 15000,
                'brand_names' => 'Rolax',
                'item_descriptions' => 'スタイリッシュなデザインのメンズ腕時計',
                'item_image_paths' => 'https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/Armani+Mens+Clock.jpg',
                'conditions' => 1, // 良好
            ],
            [
                'item_names' => 'HDD',
                'item_prices' => 5000,
                'brand_names' => '西芝',
                'item_descriptions' => '高速で信頼性の高いハードディスク',
                'item_image_paths' => 'https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/HDD+Hard+Disk.jpg',
                'conditions' => 2, // 目立った傷や汚れなし
            ],
            [
                'item_names' => '玉ねぎ3束',
                'item_prices' => 300,
                'brand_names' => 'なし',
                'item_descriptions' => '新鮮な玉ねぎ3束のセット',
                'item_image_paths' => 'https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/iLoveIMG+d.jpg',
                'conditions' => 3, // やや傷や汚れあり
            ],
            [
                'item_names' => '革靴',
                'item_prices' => 4000,
                'brand_names' => '',
                'item_descriptions' => 'クラシックなデザインの革靴',
                'item_image_paths' => 'https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/Leather+Shoes+Product+Photo.jpg',
                'conditions' => 4, // 状態が悪い
            ],
            [
                'item_names' => 'ノートPC',
                'item_prices' => 45000,
                'brand_names' => '',
                'item_descriptions' => '高性能なノートパソコン',
                'item_image_paths' => 'https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/Living+Room+Laptop.jpg',
                'conditions' => 1, // 良好
            ],
            [
                'item_names' => 'マイク',
                'item_prices' => 8000,
                'brand_names' => 'なし',
                'item_descriptions' => '高音質のレコーディング用マイク',
                'item_image_paths' => 'https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/Music+Mic+4632231.jpg',
                'conditions' => 2, // 目立った傷や汚れなし
            ],
            [
                'item_names' => 'ショルダーバッグ',
                'item_prices' => 3500,
                'brand_names' => '',
                'item_descriptions' => 'おしゃれなショルダーバッグ',
                'item_image_paths' => 'https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/Purse+fashion+pocket.jpg',
                'conditions' => 3, // やや傷や汚れあり
            ],
            [
                'item_names' => 'タンブラー',
                'item_prices' => 500,
                'brand_names' => 'なし',
                'item_descriptions' => '使いやすいタンブラー',
                'item_image_paths' => 'https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/Tumbler+souvenir.jpg',
                'conditions' => 4, // 状態が悪い
            ],
            [
                'item_names' => 'コーヒーミル',
                'item_prices' => 4000,
                'brand_names' => 'Starbacks',
                'item_descriptions' => '手動のコーヒーミル',
                'item_image_paths' => 'https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/Waitress+with+Coffee+Grinder.jpg',
                'conditions' => 1, // 良好
            ],
            [
                'item_names' => 'メイクセット',
                'item_prices' => 2500,
                'brand_names' => '',
                'item_descriptions' => '便利なメイクアップセット',
                'item_image_paths' => 'https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/%E5%A4%96%E5%87%BA%E3%83%A1%E3%82%A4%E3%82%AF%E3%82%A2%E3%83%83%E3%83%95%E3%82%9A%E3%82%BB%E3%83%83%E3%83%88.jpg',
                'conditions' => 2, // 目立った傷や汚れなし
            ],
        ];

        // 事前定義された商品を作成（10人のユーザーがそれぞれ1つずつ出品）
        foreach ($predefinedItemsData as $index => $itemData) {
            // ユーザーを順番に割り当て（10人以上いる場合）
            $userIndex = $index % $users->count();
            $assignedUser = $users->get($userIndex);
            
            // 画像をローカルにダウンロード
            $localImagePath = $this->downloadImage($itemData['item_image_paths'], $itemData['item_names']);
            
            $item = Item::firstOrCreate(
                ['item_names' => $itemData['item_names']], // 商品名で重複チェック
                [
                    'user_id' => $assignedUser->id,
                    'item_image_paths' => $localImagePath, // ローカルパスを使用
                    'item_names' => $itemData['item_names'],
                    'brand_names' => $itemData['brand_names'] ?? null,
                    'item_prices' => $itemData['item_prices'],
                    'like_counts' => rand(0, 50),
                    'comment_counts' => rand(0, 10),
                    'item_descriptions' => $itemData['item_descriptions'],
                    'conditions' => $itemData['conditions'],
                    'is_sold' => false, // 常に購入可能な状態
                ]
            );
            
            // ランダムに1-3個のカテゴリーを割り当て
            $randomCategories = $categories->random(rand(1, 3));
            $item->categories()->sync($randomCategories->pluck('id'));
            
            $this->command->info("商品「{$itemData['item_names']}」をユーザー「{$assignedUser->name}」が出品しました。");
        }

        $this->command->info('商品ダミーデータを作成しました。');
        $this->command->info('固定商品10個を作成しました（重複なし）。');
    }

    /**
     * 外部画像をダウンロードしてローカルに保存
     */
    private function downloadImage($imageUrl, $itemName)
    {
        try {
            // 画像をダウンロード
            $response = Http::timeout(30)->get($imageUrl);
            
            if (!$response->successful()) {
                $this->command->warn("画像のダウンロードに失敗しました: {$imageUrl}");
                return $imageUrl; // 失敗時は元のURLを返す
            }

            // ファイル名を生成（商品名を英語にマッピング）
            $itemNameMap = [
                '腕時計' => 'watch',
                'HDD' => 'hdd',
                '玉ねぎ3束' => 'onion',
                '革靴' => 'shoes',
                'ノートPC' => 'laptop',
                'マイク' => 'microphone',
                'ショルダーバッグ' => 'bag',
                'タンブラー' => 'tumbler',
                'コーヒーミル' => 'coffee_mill',
                'メイクセット' => 'makeup_set',
            ];
            
            $safeFileName = $itemNameMap[$itemName] ?? 'item';
            $extension = pathinfo(parse_url($imageUrl, PHP_URL_PATH), PATHINFO_EXTENSION);
            $fileName = $safeFileName . '_' . time() . '.' . ($extension ?: 'jpg');
            
            // ローカルに保存
            $localPath = 'items/' . $fileName;
            Storage::disk('public')->put($localPath, $response->body());
            
            $this->command->info("画像をダウンロードしました: {$fileName}");
            return $localPath;
            
        } catch (\Exception $e) {
            $this->command->warn("画像のダウンロード中にエラーが発生しました: {$e->getMessage()}");
            return $imageUrl; // 失敗時は元のURLを返す
        }
    }
}
