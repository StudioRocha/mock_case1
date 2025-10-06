<?php

namespace Database\Factories;

use App\Models\Item;
use App\Models\User;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

class ItemFactory extends Factory
{
    protected $model = Item::class;

    // 商品データ一覧のデータ
    private $itemData = [
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

    public function definition()
    {
        // 商品データ一覧からランダムに選択
        $itemData = $this->faker->randomElement($this->itemData);
        
        return [
            'user_id' => User::factory(),
            'item_image_paths' => $itemData['item_image_paths'],
            'item_names' => $itemData['item_names'],
            'brand_names' => $itemData['brand_names'],
            'item_prices' => $itemData['item_prices'],
            'like_counts' => $this->faker->numberBetween(0, 50),
            'comment_counts' => $this->faker->numberBetween(0, 10),
            'item_descriptions' => $itemData['item_descriptions'],
            'conditions' => $itemData['conditions'],
            'is_sold' => $this->faker->boolean(15), // 15%の確率で売却済み
        ];
    }

    public function sold()
    {
        return $this->state(function (array $attributes) {
            return [
                'is_sold' => true,
            ];
        ];
    }

    public function available()
    {
        return $this->state(function (array $attributes) {
            return [
                'is_sold' => false,
            ];
        };
    }
}
